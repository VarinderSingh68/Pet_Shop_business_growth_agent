<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Totp;
use PHPUnit\Framework\TestCase;

final class TotpTest extends TestCase
{
    public function testSecretRoundTripsThroughGenerateCodeAndVerify(): void
    {
        $secret = Totp::generateSecret();

        $this->assertTrue(Totp::verify($secret, $this->currentCodeFor($secret)));
    }

    public function testVerifyRejectsWrongCode(): void
    {
        $secret = Totp::generateSecret();
        $correct = $this->currentCodeFor($secret);
        $wrong = $correct === '000000' ? '111111' : '000000';

        $this->assertFalse(Totp::verify($secret, $wrong));
    }

    public function testVerifyRejectsMalformedInput(): void
    {
        $secret = Totp::generateSecret();

        $this->assertFalse(Totp::verify($secret, ''));
        $this->assertFalse(Totp::verify($secret, 'abcdef'));
        $this->assertFalse(Totp::verify($secret, '12345'));
    }

    public function testRfc6238RawAlgorithmMatchesPublishedTestVector(): void
    {
        // RFC 6238 Appendix B: 20-byte ASCII key "12345678901234567890",
        // SHA-1, at T = 59s (counter 1) the published 8-digit code is
        // "94287082" — the low 6 digits (truncated the same way real
        // authenticator apps display 6-digit codes) are "287082".
        $key = '12345678901234567890';
        $counter = intdiv(59, 30);

        $binCounter = pack('N2', 0, $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $truncated = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);
        $code = str_pad((string) ($truncated % 1_000_000), 6, '0', STR_PAD_LEFT);

        $this->assertSame('287082', $code);
    }

    public function testProvisioningUriIsWellFormedOtpauthUri(): void
    {
        $uri = Totp::provisioningUri('JBSWY3DPEHPK3PXP', 'admin@gmail.com', 'Happy Tails Admin');

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret=JBSWY3DPEHPK3PXP', $uri);
        $this->assertStringContainsString('admin%40gmail.com', $uri);
    }

    /**
     * Independently computes the current HOTP/TOTP code for a base32
     * secret, duplicating Totp's private algorithm on purpose — this lets
     * the round-trip tests assert against a value computed without calling
     * into the class under test.
     */
    private function currentCodeFor(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binaryString = '';
        foreach (str_split(strtoupper($secret)) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $key = '';
        foreach (str_split($binaryString, 8) as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $key .= chr(bindec($byte));
        }

        $counter = intdiv(time(), 30);
        $binCounter = pack('N2', 0, $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $truncated = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($truncated % 1_000_000), 6, '0', STR_PAD_LEFT);
    }
}
