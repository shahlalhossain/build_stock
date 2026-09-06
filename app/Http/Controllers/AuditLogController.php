<?php

namespace App\Http\Controllers;

use App\DataTables\AuditLogsDataTable;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditLogController extends Controller
{
//    public function auditLogs(AuditLogsDataTable $auditLogsDataTable)
//    {
//        return $auditLogsDataTable->render('audit_log.index');
//    }

//    public function auditLogs($type = 'all')
//    {
//        $query = AuditLog::latest();
//
//        if ($type !== 'all') {
//            $query->where('event', $type);
//        }
//
//        $data['auditLogs']  = $query->paginate(10);
//        $data['type']       = $type;
//
//        return view('audit_log.list2', $data);
//    }

    public function auditLogs($type = 'all')
    {
        $query = AuditLog::latest();

        // Event filter
        if ($type !== 'all') {
            $query->where('event', $type);
        }

        // Date filters
        $from = request('from_date');
        $to   = request('to_date');

        if ($from && $to) {
            $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
        } elseif ($from) {
            $query->whereDate('created_at', Carbon::parse($from));
        } elseif ($to) {
            $query->whereDate('created_at', Carbon::parse($to));
        }

        $data['auditLogs'] = $query->paginate(10)->withQueryString();
        $data['type']      = $type;

        return view('audit_log.list2', $data);
    }

    public function deleteAuditLog($id)
    {
        try {
            $auditLog = AuditLog::findOrFail($id);
            $auditLog->forceDelete();
            return response()->json(['success' => true, 'message' => 'Audit Log Deleted Successfully.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete Audit Log.', 'error' => $exception->getMessage()], 500);
        }
    }
}
