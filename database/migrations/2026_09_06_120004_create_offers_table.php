<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id', 190);
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedTinyInteger('max_guests');
            $table->unsignedBigInteger('price');
            $table->char('currency', 3);
            $table->unsignedInteger('available_units');
            $table->dateTime('expires_at');
            $table->dateTime('imported_at');
            $table->timestamps();

            $table->unique(['supplier_id', 'external_id']);
            $table->index(['property_id', 'check_in', 'check_out', 'price']);
        });

        // Schema-level guarantee that check_out > check_in, independent of
        // application code.
        DB::statement('ALTER TABLE offers ADD CONSTRAINT chk_offers_dates CHECK (check_out > check_in)');
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['property_id']);
            $table->dropForeign(['import_id']);
        });

        Schema::dropIfExists('offers');
    }
};
