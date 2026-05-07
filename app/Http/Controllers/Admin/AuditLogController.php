<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->user_id,    fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->action,     fn ($q) => $q->where('action', $request->action))
            ->when($request->model_type, fn ($q) => $q->where('model_type', $request->model_type))
            ->when($request->date,       fn ($q) => $q->whereDate('created_at', $request->date))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        // Stats for today
        $loginsToday   = AuditLog::where('action', 'login')->whereDate('created_at', today())->count();
        $invoicesToday = AuditLog::where('action', 'invoice_generated')->whereDate('created_at', today())->count();
        $paymentsToday = AuditLog::where('action', 'payment_recorded')->whereDate('created_at', today())->count();
        $totalLogs     = AuditLog::count();

        // Filter options
        $availableActions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $availableModels  = AuditLog::select('model_type')->distinct()->orderBy('model_type')->pluck('model_type');
        $users            = User::orderBy('name')->get();

        return view('admin.audit.index', compact(
            'logs',
            'loginsToday', 'invoicesToday', 'paymentsToday', 'totalLogs',
            'availableActions', 'availableModels', 'users'
        ));
    }
}
