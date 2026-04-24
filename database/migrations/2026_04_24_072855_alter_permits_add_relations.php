<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {

            $table->foreignId('permit_holder_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->after('permit_holder_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('permit_status_id')
                ->nullable()
                ->after('status')
                ->constrained()
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
