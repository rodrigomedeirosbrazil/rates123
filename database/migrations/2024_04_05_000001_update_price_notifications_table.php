<?php

use App\Enums\PriceNotificationTypeEnum;
use App\Managers\PriceManager;
use App\Models\PriceNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->decimal('average_variation', 10, 2)->after('change_percent')->nullable();
        });

        Schema::table('price_notifications', function (Blueprint $table) {
            $table->renameColumn('change_percent', 'variation');
        });

        $priceManager = new PriceManager();

        PriceNotification::query()
            ->orderBy('created_at', 'asc')
            ->cursor()
            ->each(function ($price) use ($priceManager) {
                $price->average_variation = $price->type === PriceNotificationTypeEnum::PriceUp
                    || $price->type === PriceNotificationTypeEnum::PriceDown
                    ? number_format($priceManager->getVariationPercentageByModePrice(
                        $price->monitored_property_id,
                        $price->after
                    ), 2)
                    : 0;

                $price->save();
            });
    }

    public function down(): void
    {
        Schema::table('price_notifications', function (Blueprint $table) {
            $table->dropColumn('average_variation');
        });
    }
};
