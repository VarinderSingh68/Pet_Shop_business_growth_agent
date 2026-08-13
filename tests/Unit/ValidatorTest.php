<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredFailsOnMissingOrEmptyValue(): void
    {
        $v = Validator::make(['name' => ''], ['name' => 'required']);

        $this->assertTrue($v->fails());
        $this->assertSame('Name is required.', $v->firstError());
    }

    public function testRequiredPassesWithValue(): void
    {
        $v = Validator::make(['name' => 'Fido'], ['name' => 'required']);

        $this->assertTrue($v->passes());
    }

    public function testEmailRuleRejectsInvalidAddress(): void
    {
        $v = Validator::make(['email' => 'not-an-email'], ['email' => 'email']);

        $this->assertTrue($v->fails());
    }

    public function testEmailRuleIsSkippedWhenValueAbsent(): void
    {
        $v = Validator::make([], ['email' => 'email']);

        $this->assertTrue($v->passes());
    }

    public function testMinAndMaxApplyStringLengthNotNumericValue(): void
    {
        $v = Validator::make(['code' => 'AB'], ['code' => 'min:3']);
        $this->assertTrue($v->fails());

        $v = Validator::make(['code' => 'ABCD'], ['code' => 'max:3']);
        $this->assertTrue($v->fails());

        $v = Validator::make(['code' => 'ABC'], ['code' => 'min:3|max:3']);
        $this->assertTrue($v->passes());
    }

    public function testConfirmedRuleComparesConfirmationField(): void
    {
        $v = Validator::make(
            ['password' => 'secret', 'password_confirmation' => 'different'],
            ['password' => 'confirmed'],
        );

        $this->assertTrue($v->fails());
    }

    public function testInRuleRestrictsToAllowedValues(): void
    {
        $v = Validator::make(['status' => 'archived'], ['status' => 'in:draft,published']);

        $this->assertTrue($v->fails());
    }

    public function testValidatedReturnsOnlyRuledFields(): void
    {
        $v = Validator::make(
            ['name' => 'Fido', 'unexpected' => 'ignored'],
            ['name' => 'required'],
        );

        $this->assertSame(['name' => 'Fido'], $v->validated());
    }

    public function testMultipleRulesAccumulateSeparateFieldErrors(): void
    {
        $v = Validator::make(
            ['name' => '', 'email' => 'bad'],
            ['name' => 'required', 'email' => 'email'],
        );

        $errors = $v->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
    }
}
