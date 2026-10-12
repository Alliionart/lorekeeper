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
        Schema::table('breeding', function (Blueprint $table) {
            $table->integer('split_user_id')->nullable();
            $table->text('split_status')->nullable()->after('split_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('breeding', function (Blueprint $table) {
            $table->dropColumn('split_user_id');
            $table->dropColumn('split_status');
        });
    }
};
