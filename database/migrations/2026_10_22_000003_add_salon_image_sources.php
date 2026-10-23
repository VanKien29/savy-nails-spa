<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_images', function (Blueprint $table) {
            $table->string('source_url', 1000)->nullable();
            $table->string('source_credit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('salon_images', fn (Blueprint $table) => $table->dropColumn(['source_url', 'source_credit']));
    }
};
