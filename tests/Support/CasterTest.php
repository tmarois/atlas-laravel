<?php

declare(strict_types=1);

namespace Atlas\Laravel\Tests\Support;

use Atlas\Laravel\Support\Caster;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class CasterTest extends TestCase
{
    public function test_casts_values_to_defined_types(): void
    {
        $data = [
            'age' => '30',
            'price' => '9.99',
            'name' => 123,
            'active' => '1',
            'options' => '{"foo":"bar"}',
            'birthday' => '2024-01-01',
            'meta' => ['foo' => 'bar'],
            'metaObject' => (object) ['foo' => 'bar'],
            'meeting' => '2024-01-01 15:00:00 Europe/London',
            'invalidDate' => 'not-a-date',
            'invalidJson' => '{bad json',
        ];

        $casts = [
            'age' => 'int',
            'price' => 'float',
            'name' => 'string',
            'active' => 'bool',
            'options' => 'json',
            'birthday' => 'datetime',
            'meta' => 'json',
            'metaObject' => 'json',
            'meeting' => 'datetime',
            'invalidDate' => 'datetime',
            'invalidJson' => 'json',
        ];

        $result = Caster::cast($data, $casts);

        $this->assertSame(30, $result['age']);
        $this->assertSame(9.99, $result['price']);
        $this->assertSame('123', $result['name']);
        $this->assertTrue($result['active']);
        $this->assertSame(['foo' => 'bar'], $result['options']);
        $this->assertInstanceOf(Carbon::class, $result['birthday']);
        $this->assertInstanceOf(Carbon::class, $result['meeting']);
        $this->assertSame('Europe/London', $result['meeting']->getTimezone()->getName());
        $this->assertSame(['foo' => 'bar'], $result['meta']);
        $this->assertSame(['foo' => 'bar'], $result['metaObject']);
        $this->assertNull($result['invalidDate']);
        $this->assertNull($result['invalidJson']);
    }

    public function test_casts_boolean_strings(): void
    {
        $data = [
            'true' => 'true',
            'false' => 'false',
            'one' => '1',
            'zero' => '0',
            'yes' => 'yes',
            'no' => 'no',
        ];

        $casts = [
            'true' => 'bool',
            'false' => 'bool',
            'one' => 'bool',
            'zero' => 'bool',
            'yes' => 'bool',
            'no' => 'bool',
        ];

        $result = Caster::cast($data, $casts);

        $this->assertTrue($result['true']);
        $this->assertFalse($result['false']);
        $this->assertTrue($result['one']);
        $this->assertFalse($result['zero']);
        $this->assertTrue($result['yes']);
        $this->assertFalse($result['no']);
    }
}
