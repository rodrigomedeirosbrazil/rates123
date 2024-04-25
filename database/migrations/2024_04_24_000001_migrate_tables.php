<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('units');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('demands');
        Schema::rename('monitored_datas', 'rates');
        Schema::rename('monitored_platforms', 'scraped_platforms');
        Schema::rename('date_events', 'schedule_events');
        Schema::rename('user_property', 'user_followed_properties');
        Schema::rename('monitored_properties', 'properties');
        Schema::rename('monitored_syncs', 'syncs');

        Schema::table('rates', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->after('id')->nullable();
        });

        DB::table('rates')->update(['property_id' => DB::raw('monitored_property_id')]);

        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn('monitored_property_id');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->renameColumn('monitored_platform_id', 'scraped_platform_id');
        });

        Schema::table('syncs', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->after('id')->nullable();
        });

        DB::table('syncs')->update(['property_id' => DB::raw('monitored_property_id')]);

        Schema::table('syncs', function (Blueprint $table) {
            $table->dropColumn('monitored_property_id');
        });

        Schema::table('user_followed_properties', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->after('id')->nullable();
        });

        DB::table('user_followed_properties')->update(['property_id' => DB::raw('monitored_property_id')]);

        Schema::table('user_followed_properties', function (Blueprint $table) {
            $table->dropColumn('monitored_property_id');
        });

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->after('id')->nullable();
        });

        DB::table('price_notifications')->update(['property_id' => DB::raw('monitored_property_id')]);

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('monitored_property_id');
        });

        Schema::table('occupancies', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->after('id')->nullable();
        });

        DB::table('occupancies')->update(['property_id' => DB::raw('monitored_property_id')]);

        Schema::table('occupancies', function (Blueprint $table) {
            $table->dropColumn('monitored_property_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
    }
};
