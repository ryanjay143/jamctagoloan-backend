<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use App\Models\ListOfMemberModel;
use Carbon\Carbon;

class ResetAttendanceOnSunday
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $today = Carbon::now('Asia/Manila')->dayOfWeek;

        // Check if today is Sunday and if the reset hasn't been done today
        if ($today === Carbon::SUNDAY && !Cache::has('attendance_reset')) {
            ListOfMemberModel::query()->update(['attendance_status' => 0]);

            // Set a cache key to prevent multiple resets on the same day
            Cache::put('attendance_reset', true, Carbon::now('Asia/Manila')->endOfDay());
        }

        return $next($request);
    }
}