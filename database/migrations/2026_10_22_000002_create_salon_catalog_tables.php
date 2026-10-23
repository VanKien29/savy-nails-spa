<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->json('data');
            $table->timestamps();
        });
        Schema::create('salon_images', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('alt');
            $table->string('caption');
            $table->string('category')->default('rituals')->index();
            $table->json('variants');
            $table->boolean('is_inspiration')->default(false);
            $table->boolean('show_in_gallery')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('salon_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->foreignId('image_id')->nullable()->constrained('salon_images')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('salon_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('salon_categories')->cascadeOnDelete();
            $table->foreignId('image_id')->nullable()->constrained('salon_images')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('group')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('gel_price', 8, 2)->nullable();
            $table->boolean('price_from')->default(false);
            $table->string('price_note')->nullable();
            $table->string('duration_label')->nullable();
            $table->unsignedTinyInteger('massage_minutes')->nullable();
            $table->json('includes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['salon_services', 'salon_categories', 'salon_images', 'salon_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
