<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RouletteCreateTables extends Migration
{
    public function up()
    {
        Schema::create('roulette_rounds', function (Blueprint $table) {
            $table->id();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('result')->nullable();
            $table->boolean('is_rigged')->default(false);
            $table->integer('rigged_value')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('roulette_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('round_id')->constrained('roulette_rounds');
            $table->string('type');
            $table->string('value');
            $table->decimal('amount', 12, 2);
            $table->decimal('payout', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('roulette_bets');
        Schema::dropIfExists('roulette_rounds');
    }
} 