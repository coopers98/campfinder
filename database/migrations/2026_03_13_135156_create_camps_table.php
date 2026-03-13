<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('camps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->text('description');
            $table->unsignedTinyInteger('age_min');
            $table->unsignedTinyInteger('age_max');
            $table->date('week_start');
            $table->date('week_end');
            $table->string('schedule_type');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('enrolled')->default(0);
            $table->unsignedInteger('waitlist_count')->default(0);
            $table->boolean('lunch_provided')->default(false);
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index(['week_start', 'category']);
            $table->index(['age_min', 'age_max']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camps');
    }
};
