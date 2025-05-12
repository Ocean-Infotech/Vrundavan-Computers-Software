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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('media_type')->nullable();
            $table->string('slider_type');
            $table->text('redirect_url')->nullable();
            $table->string('status')->default('active')->comment('active, inactive');
			$table->string('created_by')->nullable();
			$table->string('updated_by')->nullable();
            $table->timestamps();
			$table->string('deleted_by')->nullable();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
