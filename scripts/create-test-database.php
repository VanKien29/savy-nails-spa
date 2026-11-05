<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    if (! $app->environment(['local', 'testing']) || config('database.default') !== 'mysql') {
        throw new RuntimeException('Create the test database only in a local/testing MySQL environment.');
    }

    $database = 'savy_nails_spa_testing';
    DB::connection('mysql')->statement('CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo 'Ready MySQL test database: '.$database.PHP_EOL;
} catch (Throwable) {
    fwrite(STDERR, "Cannot prepare the isolated MySQL test database. Check the local/testing environment, connection and CREATE DATABASE permission.\n");
    exit(1);
}
