<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('offer_id')->constrained()->restrictOnDelete();
            $table->string('client_reference', 190)->unique();
            $table->string('customer_name', 255);
            $table->string('customer_email', 255);
            $table->unsignedBigInteger('price');
            $table->char('currency', 3);
            $table->date('check_in');
            $table->date('check_out');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['offer_id']);
        });

        Schema::dropIfExists('reservations');
    }
};
