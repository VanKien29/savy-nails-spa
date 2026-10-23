<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Keep compiled views separate from concurrently running local preview/export requests.
        $path = storage_path('framework/testing/views');
        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }
        config(['view.compiled' => $path]);
    }
}
