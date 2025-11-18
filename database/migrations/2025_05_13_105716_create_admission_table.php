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
        Schema::create('admission', function (Blueprint $table) {
            $table->id();
            $table->string('biometric_id')->nullable();
            $table->string('aadhar_card_no');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->text('temporary_address');
            $table->text('permanent_address');
            $table->string('mobile_no');
            $table->string('parent_mobile_no')->nullable();
            $table->string('other_mobile_no')->nullable();
            $table->string('whatsapp_no')->nullable();
            $table->string('cast')->nullable();
            $table->string('occupation')->nullable();
            $table->date('date_of_birth');
            $table->string('email_address')->nullable();
            $table->enum('gender', ["Male", "Female", "Other"])->comment('Male, Female, Other');
            $table->enum('category',["SC", "ST", "OBC", "EWS", "General"])->comment('SC, ST, OBC, EWS, General');
            $table->string('enrolment_no')->nullable();
            $table->string('spid')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('apaar_id_abc_id')->nullable();
            $table->string('status')->default('created')->comment('created, rejected, completed, cancelled');
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
        Schema::dropIfExists('admission');
    }
};
