<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('audit_logs.index', compact('logs'));
    }
}
