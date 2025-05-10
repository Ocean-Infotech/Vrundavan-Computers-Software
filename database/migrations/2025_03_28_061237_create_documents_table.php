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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type_id');
            $table->string('name');
            $table->string('folder_path');
            $table->string('file_mime_type');
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
        Schema::dropIfExists('documents');
    }
};
