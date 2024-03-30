<?php

use App\Enums\PriceNotificationTypeEnum;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
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

        // MIGRATE OLD Price notifications
        PriceNotification::query()
            ->orderBy('created_at', 'asc')
            ->cursor()
            ->each(function ($price) {
                $priceDateAfter = MonitoredData::query()
                    ->whereDate('created_at', $price->created_at)
                    ->whereDate('checkin', $price->checkin)
                    ->where('monitored_property_id', $price->monitored_property_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $priceDateBefore = MonitoredData::query()
                    ->whereDate('created_at', '<', $price->created_at)
                    ->whereDate('checkin', $price->checkin)
                    ->where('monitored_property_id', $price->monitored_property_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $price->before = $priceDateBefore->price;
                $price->after = $priceDateAfter->price;

                $price->change_percent = $price->type === PriceNotificationTypeEnum::PriceUnavailable
                    || $price->type === PriceNotificationTypeEnum::PriceAvailable
                    ? 0
                    : number_format(
                        (($priceDateAfter->price - $priceDateBefore->price) / $priceDateBefore->price) * 100,
                        2
                    );

                $price->save();
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
