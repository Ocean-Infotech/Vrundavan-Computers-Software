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
        Schema::create('master_states', function (Blueprint $table) {
            $table->id();
            $table->string('country_id');
			$table->string('name');
			$table->string('short_name')->nullable();
			$table->string('gst_code')->nullable();
			$table->string('latitude')->nullable();
			$table->string('longitude')->nullable();
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
        Schema::dropIfExists('master_states');
    }
};
