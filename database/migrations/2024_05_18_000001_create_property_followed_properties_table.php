<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('property_followed_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained();
            $table->foreignId('followed_property_id')->constrained();
            $table->timestamps();
        });

        Schema::dropIfExists('user_followed_properties');
    }

    public function down(): void
    {
        Schema::dropIfExists('property_followed_properties');
    }
};
