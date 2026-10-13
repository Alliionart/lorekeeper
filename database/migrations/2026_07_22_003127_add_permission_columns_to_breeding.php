<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('breeding', function (Blueprint $table) {
            $table->integer('parent_1')->references('id')->on('characters')->after('status');
            $table->integer('parent_2')->references('id')->on('characters')->after('parent_1');
            $table->integer('permission_1')->references('id')->on('breeding_permissions')->nullable()->after('parent_1');
            $table->integer('permission_2')->references('id')->on('breeding_permissions')->nullable()->after('parent_2');

            $table->string('breeding_data')->nullable()->change();
            $table->string('status')->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('breeding', function (Blueprint $table) {
            $table->dropColumn('parent_1');
            $table->dropColumn('parent_2');
            $table->dropColumn('permission_1');
            $table->dropColumn('permission_2');
        });
    }
};
