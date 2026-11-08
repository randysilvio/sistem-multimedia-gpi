<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\Song;
use App\Services\Branding;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $next = Schedule::with('liturgy')->whereDate('worship_date', '>=', $today)->orderBy('worship_date')->first();

        return view('dashboard', [
            'nextSchedule' => $next,
            'readiness' => $next ? \App\Services\ReadinessCheck::for($next) : [],
            'recentSchedules' => Schedule::with('liturgy')->orderByDesc('worship_date')->limit(6)->get(),
            'stats' => [
                'schedules' => Schedule::count(),
                'templates' => Liturgy::templates()->count(),
                'songs' => Song::count(),
                'announcements' => Announcement::where('is_active', true)->count(),
            ],
            'logoUrl' => Branding::logoUrl(),
            'hasUploadedLogo' => Branding::hasUploadedLogo(),
        ]);
    }

    /** GET /up: untuk pemantauan uptime. 200 bila aplikasi & database sehat, 503 bila tidak. */
    public function health(): JsonResponse
    {
        try {
            DB::select('select 1');
            $db = true;
        } catch (\Throwable) {
            $db = false;
        }

        return response()->json(['status' => $db ? 'ok' : 'degraded', 'database' => $db], $db ? 200 : 503)
            ->header('Cache-Control', 'no-store');
    }
}
