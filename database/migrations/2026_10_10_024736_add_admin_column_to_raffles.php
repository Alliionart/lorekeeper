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
        Schema::table('raffles', function (Blueprint $table) {
            $table->boolean('is_admin_only')->default(false)->after('is_active');
            $table->longtext('excluded_winners')->nullable()->after('is_admin_only');
            $table->enum('rewards_type', ['Tiered', 'Random', 'Static'])->default('Static')->after('winner_count');
        });

        Schema::table('raffle_tickets', function (Blueprint $table) {
            $table->text('opted_out')->nullable()->after('raffle_id');
            $table->text('winner_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('is_admin_only');
            $table->dropColumn('excluded_winners');
            $table->dropColumn('reward_type');
        });

        Schema::table('raffle_tickets', function (Blueprint $table) {
            $table->dropColumn('opted_out');
            $table->dropColumn('winner_data');
        });
    }
};
