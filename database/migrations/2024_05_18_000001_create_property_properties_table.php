<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('property_properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('followed_property_id');
        });

        Schema::dropIfExists('user_followed_properties');
    }

    public function down(): void
    {
        Schema::dropIfExists('property_properties');
    }
};
