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
        Schema::create('library_issued_books', function (Blueprint $table) {
            $table->id();
            $table->string('book_id');
            $table->string('student_id');
            $table->date('issued_date');
            $table->string('issued_by')->nullable();
            $table->date('return_date')->nullable();
            $table->date('return_by')->nullable();
            $table->string('status')->default('active')->comment('active, deactive');
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
        Schema::dropIfExists('library_issued_books');
    }
};
