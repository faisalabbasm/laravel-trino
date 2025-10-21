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
        Schema::create('schema_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_id')->unique();
            $table->string('database');
            $table->string('schema');
            $table->json('snapshot_data');
            $table->integer('columns_captured')->default(0);
            $table->timestamp('captured_at');
            $table->timestamps();
            
            $table->index(['database', 'schema']);
            $table->index('captured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schema_snapshots');
    }
};
