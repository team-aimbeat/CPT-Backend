<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPolicyAcceptancesTable extends Migration
{
    public function up()
    {
        Schema::create('user_policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('setting_id')->nullable();
            $table->string('policy_type');
            $table->string('policy_title')->nullable();
            $table->string('policy_content_hash', 64)->nullable();
            $table->mediumText('policy_content')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'policy_type', 'policy_content_hash'], 'user_policy_acceptance_unique');
            $table->index(['user_id', 'policy_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_policy_acceptances');
    }
}
