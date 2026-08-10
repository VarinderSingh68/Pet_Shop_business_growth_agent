<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Minimal Google OAuth 2.0 "Sign in with Google" flow via plain cURL — no
 * SDK dependency, consistent with the project's minimal-Composer-footprint
 * rule. Implements the authorization-code flow: redirect to Google, receive
 * a code, exchange it server-side for the user's verified email and name.
 */
final class GoogleAuthService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function isConfigured(): bool
    {
        return config('google.client_id') !== '' && config('google.client_secret') !== '';
    }

    public function redirectUrl(string $state): string
    {
        $params = [
            'client_id' => config('google.client_id'),
            'redirect_uri' => $this->callbackUrl(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchanges an authorization code for the signed-in Google account's
     * verified email, name, and stable subject ID.
     *
     * @return array{google_id: string, email: string, name: string}
     * @throws \RuntimeException on any failure — never trust an unverified email
     */
    public function fetchProfile(string $code): array
    {
        $tokenResponse = $this->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => config('google.client_id'),
            'client_secret' => config('google.client_secret'),
            'redirect_uri' => $this->callbackUrl(),
            'grant_type' => 'authorization_code',
        ]);

        if (!isset($tokenResponse['access_token'])) {
            throw new \RuntimeException('Google did not return an access token.');
        }

        $profile = $this->get(self::USERINFO_URL, (string) $tokenResponse['access_token']);

        if (empty($profile['sub']) || empty($profile['email'])) {
            throw new \RuntimeException('Google did not return a usable profile.');
        }

        if (empty($profile['email_verified'])) {
            throw new \RuntimeException('Google account email is not verified.');
        }

        return [
            'google_id' => (string) $profile['sub'],
            'email' => strtolower((string) $profile['email']),
            'name' => (string) ($profile['name'] ?? $profile['email']),
        ];
    }

    private function callbackUrl(): string
    {
        return url('/account/login/google/callback');
    }

    /** @param array<string, string> $fields */
    private function post(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);

        if ($body === false) {
            throw new \RuntimeException('Could not reach Google: ' . $error);
        }

        $decoded = json_decode((string) $body, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function get(string $url, string $bearerToken): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $bearerToken],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);

        if ($body === false) {
            throw new \RuntimeException('Could not reach Google: ' . $error);
        }

        $decoded = json_decode((string) $body, true);
        return is_array($decoded) ? $decoded : [];
    }
}
