<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    public function isEmailEnabled(): bool
    {
        return Cache::remember('email_feature_status', 60, function () {
            return (int) DB::table('setting_tbl')
                ->where('featureid',1)
                ->value('feature_status') === 1;
        });
    }

    public function isSmsEnabled(): bool
    {
        return Cache::remember('sms_feature_status', 60, function () {
            return (int) DB::table('setting_tbl')
                ->where('featureid',2)
                ->value('feature_status') === 1;
        });
    }

}
?>