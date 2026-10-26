<?php

namespace App\Support;

final class PhpRuntime
{
    public const REQUIREMENT = 'PHP stable 8.4.27+ (8.4.x) or 8.5.12+ (8.5.x)';

    public static function isSupported(string $version = PHP_VERSION): bool
    {
        if (! preg_match('/^\d+\.\d+\.\d+$/D', $version)) {
            return false;
        }

        return (version_compare($version, '8.4.27', '>=') && version_compare($version, '8.5.0', '<'))
            || (version_compare($version, '8.5.12', '>=') && version_compare($version, '8.6.0', '<'));
    }
}
