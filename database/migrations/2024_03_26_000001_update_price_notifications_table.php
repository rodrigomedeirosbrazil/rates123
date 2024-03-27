<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->decimal('change_percent', 10, 2)->nullable();
            $table->decimal('before', 10, 2)->nullable();
            $table->decimal('after', 10, 2)->nullable();
        });

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }

    public function down(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('change_percent');
            $table->dropColumn('before');
            $table->dropColumn('after');
        });

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->text('message');
        });
    }
};
