<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pnl_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from');
            $table->decimal('delivery_percent', 5, 2);
            $table->decimal('margin', 10, 2);
            $table->decimal('rto_charge', 10, 2);
            $table->decimal('delivered_charge', 10, 2);
            $table->decimal('gst_percent', 5, 2);
            $table->timestamps();

            $table->unique(['store_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pnl_rates');
    }
};