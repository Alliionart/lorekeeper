<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('prompts', function (Blueprint $table) {
            $table->boolean('multiple_users')->default(0)->after('is_active');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('other_user_ids', 255)->nullable()->after('user_id');
            $table->string('rules', 255)->nullable()->after('other_user_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('multiple_users');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('other_user_ids');
            $table->dropColumn('rules');
        });
    }
};
