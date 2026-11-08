<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        return view('activity.index', [
            'logs' => ActivityLog::with('user')->latest('id')->paginate(30),
        ]);
    }
}
