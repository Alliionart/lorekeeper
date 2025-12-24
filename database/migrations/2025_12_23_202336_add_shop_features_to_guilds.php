<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('guild_shop_stock', function (Blueprint $table) {
            $table->float('guild_cost')->after('cost')->nullable();
            $table->boolean('is_limited_stock')->default(false);
            $table->integer('purchase_limit')->nullable();
            $table->boolean('guild_only')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('guild_shop_stock', function (Blueprint $table) {
            $table->dropColumn('guild_cost');
            $table->dropColumn('is_limited_stock');
            $table->dropColumn('purchase_limit');
            $table->dropColumn('guild_only');
        });
    }
};
