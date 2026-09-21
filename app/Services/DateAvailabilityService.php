<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
class DateAvailabilityService
{
    public function isDateAvailable($date): array
    {
        if(!$date)
		{
            return [true, null];
        }

        $carbon = Carbon::parse($date);
        $formattedDate = $carbon->toDateString();

        if ($this->isWeekend($carbon)) {
            return [false, 'Selected date falls on weekend (Saturday/Sunday).'];
        }

        if ($this->isFixedHoliday($formattedDate)) {
            return [false, 'Selected date falls on a holiday.'];
        }

        if ($this->isRecurringHoliday($carbon)) {
            return [false, 'Selected date falls on a recurring holiday.'];
        }

        return [true, null];
    }

    private function isWeekend(Carbon $date): bool
    {
        return $date->isSaturday() || $date->isSunday();
    }

    private function isFixedHoliday(string $date): bool
    {
        return DB::table('holiday_tbl')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    private function isRecurringHoliday(Carbon $date): bool
    {
        $rules = DB::table('holiday_rules')->get();

        foreach ($rules as $rule) {

            if ($rule->type === 'weekly') {
                if (strtolower($date->format('l')) === strtolower($rule->value)) {
                    return true;
                }
            }

            if ($rule->type === 'monthly') {
                if ($date->day == (int)$rule->value) {
                    return true;
                }
            }

            if ($rule->type === 'yearly') {
                if ($date->format('m-d') === $rule->value) {
                    return true;
                }
            }
        }
        return false;
    }
}
?>