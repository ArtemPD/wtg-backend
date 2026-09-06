<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('external_import_id', 190);
            $table->dateTime('sent_at');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('total_offers')->default(0);
            $table->unsignedInteger('processed_offers')->default(0);
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'external_import_id']);
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table): void {
            $table->dropForeign(['supplier_id']);
        });

        Schema::dropIfExists('imports');
    }
};
