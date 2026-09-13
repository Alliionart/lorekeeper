<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // Didn't create this table, this migration was created with the command `php artisan plugin:make-migration LoginAsUser create_impersonator_logs_table` to for testing/demonstration purposes.

        // Schema::create('impersonator_logs', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Schema::dropIfExists('impersonator_logs');
    }
};
