<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal rule-based validator. Rules are pipe-separated strings, e.g.
 * 'email|required|max:191' or 'required|unique:users,email'.
 */
final class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    private function __construct(private readonly array $data, private readonly array $rules)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->run();
        return $validator;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;

            foreach (explode('|', $ruleString) as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        match ($rule) {
            'required' => $this->assert(
                $value !== null && $value !== '' && $value !== [],
                $field,
                "{$label} is required.",
            ),
            'email' => $this->assertIfPresent(
                $value,
                fn () => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                $field,
                "{$label} must be a valid email address.",
            ),
            'numeric' => $this->assertIfPresent(
                $value,
                fn () => is_numeric($value),
                $field,
                "{$label} must be a number.",
            ),
            'integer' => $this->assertIfPresent(
                $value,
                fn () => filter_var($value, FILTER_VALIDATE_INT) !== false,
                $field,
                "{$label} must be a whole number.",
            ),
            'min' => $this->assertIfPresent(
                $value,
                fn () => is_string($value) ? mb_strlen($value) >= (int) $params[0] : (float) $value >= (float) $params[0],
                $field,
                "{$label} must be at least {$params[0]}.",
            ),
            'max' => $this->assertIfPresent(
                $value,
                fn () => is_string($value) ? mb_strlen($value) <= (int) $params[0] : (float) $value <= (float) $params[0],
                $field,
                "{$label} must not exceed {$params[0]}.",
            ),
            'confirmed' => $this->assert(
                $value === ($this->data[$field . '_confirmation'] ?? null),
                $field,
                "{$label} confirmation does not match.",
            ),
            'in' => $this->assertIfPresent(
                $value,
                fn () => in_array((string) $value, $params, true),
                $field,
                "{$label} is not valid.",
            ),
            'unique' => $this->assertIfPresent(
                $value,
                function () use ($value, $params, $field) {
                    [$table, $column] = [$params[0], $params[1] ?? $field];
                    $ignoreId = $params[2] ?? null;

                    $sql = "SELECT COUNT(*) AS c FROM `{$table}` WHERE `{$column}` = :value";
                    $bindings = ['value' => $value];
                    if ($ignoreId !== null) {
                        $sql .= ' AND id != :ignore_id';
                        $bindings['ignore_id'] = $ignoreId;
                    }

                    $row = Database::instance()->selectOne($sql, $bindings);
                    return (int) ($row['c'] ?? 0) === 0;
                },
                $field,
                "This {$label} is already in use.",
            ),
            'boolean' => true,
            'string' => $this->assertIfPresent(
                $value,
                fn () => is_string($value),
                $field,
                "{$label} must be text.",
            ),
            default => null,
        };
    }

    private function assert(bool $passed, string $field, string $message): void
    {
        if (!$passed) {
            $this->errors[$field][] = $message;
        }
    }

    private function assertIfPresent(mixed $value, callable $check, string $field, string $message): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->assert((bool) $check(), $field, $message);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    /** @return array<string, array<int, string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            return $messages[0];
        }
        return null;
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }
}
