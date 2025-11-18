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
        Schema::create('library_book_master', function (Blueprint $table) {
            $table->id();
            $table->string('book_name');
            $table->string('library_book_no');
            $table->string('author_name');
            $table->string('publisher_name');
            $table->string('total_number_of_page')->nullable();
            $table->date('purchase_date');
            $table->integer('price');
            $table->string('status')->default('active')->comment('active, destroy, not available');
            $table->string('created_by');
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
        Schema::dropIfExists('library_book_master');
    }
};
