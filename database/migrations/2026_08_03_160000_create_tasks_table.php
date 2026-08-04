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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->nullable();
            $table->string('title');
            $table->dateTime('due_date')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = Pending, 1 = In Progress, 2 = Completed');
            $table->text('remarks')->nullable();
            $table->text('response')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
