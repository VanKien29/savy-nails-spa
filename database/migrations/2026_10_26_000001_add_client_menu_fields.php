<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_services', function (Blueprint $table): void {
            $table->decimal('fill_price', 8, 2)->nullable()->after('gel_price');
            $table->string('includes_label', 160)->nullable()->after('includes');
        });
    }

    public function down(): void
    {
        Schema::table('salon_services', fn (Blueprint $table) => $table->dropColumn(['fill_price', 'includes_label']));
    }
};
