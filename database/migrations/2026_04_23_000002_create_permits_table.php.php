<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->string('plate');
            $table->string('holder');     // hotel / azienda
            $table->string('type');       // NCC / navetta
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('status')->default('active'); // active / revoked
            $table->uuid('qr_token')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};