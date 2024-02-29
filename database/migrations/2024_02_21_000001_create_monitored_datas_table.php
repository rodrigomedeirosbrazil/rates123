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
        Schema::create('monitored_datas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_property_id')->constrained();
            $table->decimal('price', 10, 2);
            $table->date('checkin');
            $table->boolean('available');
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_datas');
    }
};
