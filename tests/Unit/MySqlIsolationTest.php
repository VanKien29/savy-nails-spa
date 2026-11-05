<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MySqlIsolationTest extends TestCase
{
    public function test_uploads_use_isolated_test_storage(): void
    {
        foreach (['local', 'public'] as $disk) {
            $path = str_replace('\\', '/', Storage::disk($disk)->path('probe.png'));
            $this->assertStringContainsString('/storage/framework/testing/disks/'.$disk.'/', $path);
            $this->assertStringNotContainsString('/storage/app/', $path);
        }
    }

    public function test_guard_rejects_application_database_before_any_schema_reset(): void
    {
        config(['database.connections.mysql.database' => 'savy_nails_spa']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('isolated MySQL database');
        $this->guardTestDatabase($this->app);
    }
}
