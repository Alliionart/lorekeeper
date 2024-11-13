<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('genotype', 300)->nullable()->after('name');
            $table->string('phenotype', 300)->nullable()->after('genotype');
            $table->string('sex', 191)->nullable()->after('phenotype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('genotype');
            $table->dropColumn('phenotype');
            $table->dropColumn('sex');
        });
    }
};
