<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use Illuminate\Http\Request;

class SmsLogController extends Controller
{
    /**
     * Admin view of every SMS send attempt.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        $query = SmsLog::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('context')) {
            $query->where('context', $request->input('context'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('provider_message_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Stats for the cards
        $today = now()->startOfDay();
        $stats = [
            'today_total'     => SmsLog::where('created_at', '>=', $today)->count(),
            'today_delivered' => SmsLog::where('created_at', '>=', $today)->where('status', SmsLog::STATUS_DELIVERED)->count(),
            'today_failed'    => SmsLog::where('created_at', '>=', $today)->whereIn('status', [SmsLog::STATUS_FAILED, SmsLog::STATUS_REJECTED])->count(),
            'today_skipped'   => SmsLog::where('created_at', '>=', $today)->where('status', SmsLog::STATUS_SKIPPED)->count(),
            'today_cost'      => SmsLog::where('created_at', '>=', $today)->sum('cost'),
        ];

        $contexts = SmsLog::whereNotNull('context')->distinct()->pluck('context')->toArray();
        $statuses = [
            SmsLog::STATUS_QUEUED,
            SmsLog::STATUS_SENT,
            SmsLog::STATUS_DELIVERED,
            SmsLog::STATUS_FAILED,
            SmsLog::STATUS_REJECTED,
            SmsLog::STATUS_SKIPPED,
        ];

        return view('admin.sms_logs.index', compact('logs', 'stats', 'contexts', 'statuses'));
    }
}