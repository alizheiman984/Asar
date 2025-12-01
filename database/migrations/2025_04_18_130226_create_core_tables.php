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
        Schema::create('campaign_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('volunteer_teams', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('national_number')->unique();
            $table->string('phone')->unique();
            $table->enum('gender', ['ذكر', 'أنثى']);
            $table->string('nationality');
            $table->date('birth_date');
            $table->string('image');
            $table->string('email')->unique();
            $table->string('password');
             $table->enum('type',['charities','volunteer teams']);
            $table->enum('status',['pending','accepted','rejected'])->default('pending');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('business_informations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->string('team_name');
            $table->string('license_number');

            $table->text('long')->nullable();
            $table->text('lat')->nullable();

            $table->string('address')->nullable();
            $table->string('bank_account_number')->unique();
            $table->string('log_image');
            $table->string('logo');
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('national_number')->unique()->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->date('birth_date')->nullable();
            $table->string('image')->nullable();
            $table->integer('total_points')->default(0);
            $table->string('qr_code')->nullable();
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('national_number')->unique()->nullable();
            $table->enum('position',['مدير مستودع','مشرف','موظف مالي']);
            $table->string('phone');
            $table->string('address')->nullable();
            $table->date('date_accession');
            $table->string('image')->nullable();
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('business_informations');
        Schema::dropIfExists('volunteer_teams');
        Schema::dropIfExists('specializations');
        Schema::dropIfExists('campaign_types');
    }
};
