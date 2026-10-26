<?php

namespace Tests\Unit;

use App\Support\PhpRuntime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhpRuntimeTest extends TestCase
{
    #[DataProvider('versions')]
    public function test_only_patched_stable_supported_branches_are_accepted(string $version, bool $supported): void
    {
        $this->assertSame($supported, PhpRuntime::isSupported($version));
    }

    public static function versions(): array
    {
        return [
            ['8.3.36', false],
            ['8.4.0', false],
            ['8.4.26', false],
            ['8.4.27', true],
            ['8.4.28', true],
            ['8.5.0', false],
            ['8.5.11', false],
            ['8.5.12', true],
            ['8.5.13', true],
            ['8.6.0', false],
            ['8.4.27RC1', false],
            ['8.5.12-dev', false],
        ];
    }
}
