<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::rename('marking_carriers', 'carrier_markings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::rename('carrier_markings', 'marking_carriers');
    }
};
