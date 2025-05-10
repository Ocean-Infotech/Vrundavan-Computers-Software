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
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('request_points')->nullable();
            $table->string('request_amount')->nullable();
            $table->date('withdraw_request_date')->nullable();
            $table->string('image')->nullable();
            $table->mediumText('reason')->nullable();
            $table->string('withdraw_request_status')->default('Pending')->comment('Pending , Approve , Reject');
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
        Schema::dropIfExists('withdraw_requests');
    }
};
