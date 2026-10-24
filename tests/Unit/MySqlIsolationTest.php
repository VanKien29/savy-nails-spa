<?php

namespace Tests\Unit;

use Tests\TestCase;

class MySqlIsolationTest extends TestCase
{
    public function test_guard_rejects_application_database_before_any_schema_reset(): void
    {
        config(['database.connections.mysql.database' => 'the_k_luxury_nail_bar']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('isolated MySQL database');
        $this->guardTestDatabase($this->app);
    }
}
