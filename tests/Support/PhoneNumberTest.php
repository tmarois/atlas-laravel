<?php

namespace Atlas\Laravel\Tests\Support;

use Atlas\Laravel\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_format_returns_formatted_number(): void
    {
        $this->assertSame('(123) 456-7890', PhoneNumber::format('1234567890'));
    }

    public function test_format_normalizes_input_before_formatting(): void
    {
        $this->assertSame('(123) 456-7890', PhoneNumber::format('(123) 456-7890'));
    }

    public function test_format_accepts_integer_input(): void
    {
        $this->assertSame('(123) 456-7890', PhoneNumber::format(1234567890));
    }

    public function test_format_returns_null_for_empty_input(): void
    {
        $this->assertNull(PhoneNumber::format(null));
    }

    public function test_format_returns_null_when_normalization_fails(): void
    {
        $this->assertNull(PhoneNumber::format('123'));
    }

    public function test_normalize_strips_country_code_and_symbols(): void
    {
        $this->assertSame('1234567890', PhoneNumber::normalize('+1 (123) 456-7890'));
    }

    public function test_normalize_returns_null_for_invalid_number(): void
    {
        $this->assertNull(PhoneNumber::normalize('123'));
    }
}
