<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitored_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_property_id')->constrained();
            $table->boolean('successful');
            $table->integer('prices_count');
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_syncs');
    }
};
