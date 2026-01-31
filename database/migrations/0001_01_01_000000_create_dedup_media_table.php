<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dedup_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hash', 64)->unique()->index();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('reference_count')->default(0);
            $table->timestamps();
        });

        // Pivot table for polymorphic many-to-many relationship
        Schema::create('dedup_mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('dedup_media_id')
                ->constrained('dedup_media')
                ->cascadeOnDelete();
            $table->uuidMorphs('mediable');
            $table->string('collection')->default('default');
            $table->timestamps();

            $table->unique(['dedup_media_id', 'mediable_type', 'mediable_id', 'collection'], 'dedup_mediables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dedup_mediables');
        Schema::dropIfExists('dedup_media');
    }
};
