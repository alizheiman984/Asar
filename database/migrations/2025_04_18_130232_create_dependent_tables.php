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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name');
            $table->integer('number_of_volunteer');
            $table->decimal('cost', 10, 2);
            $table->text('address');
            $table->dateTime('from');
            $table->dateTime('to');
            $table->integer('points');
            $table->enum('status', ['pending', 'done', 'rejected'])->default('pending');
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->onDelete('cascade');
            $table->foreignId('campaign_type_id')->constrained('campaign_types')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('campaign_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->integer('points');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('magnitude_change');
            $table->enum('reason_of_change', ['عذر', 'حضور', 'عدم حضور']);
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['complaints', 'suggestion']);
            $table->text('content');            
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('volunteer_teams')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_attendance')->default(false);
            $table->integer('points_earned')->default(0);
            $table->string('image')->nullable();
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('benefactors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->timestamps();
        });

        Schema::create('donor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefactor_id')->constrained('benefactors')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('transfer_number')->nullable();
            $table->enum('type', ['حوالة', 'كاش']);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->date('payment_date');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('financials', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('payment', 10, 2);
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('company_name');
            $table->date('contract_date');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('financials');
        Schema::dropIfExists('donor_payments');
        Schema::dropIfExists('benefactors');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('points');
        Schema::dropIfExists('campaign_volunteers');
        Schema::dropIfExists('campaigns');
    }
};
