<?php

namespace App\Http\Controllers;

use App\DataTables\LoginHistoryDataTable;
use App\Models\LoginActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class ActivitiesController extends Controller
{
    public function loginHistory(LoginHistoryDataTable $loginHistoryDataTable, $type = 'all')
    {
        $loginHistoryDataTable = new LoginHistoryDataTable($type);
        return $loginHistoryDataTable->render('activity.login-history', compact('type'));
    }

//    public function loginHistory($type = 'all') : View
//    {
//        $data = [];
//
//        $loginHistoryQuery = LoginActivity::query();
//
//        if ($type == 'all') {
//            $data['loginHistories'] = $loginHistoryQuery->paginate(20);
//        } elseif ($type == 'active') {
//            $data['loginHistories'] = $loginHistoryQuery->where('status', 'active')->paginate(20);
//        } elseif ($type == 'expired') {
//            $data['loginHistories'] = $loginHistoryQuery->where('status', 'expired')->paginate(20);
//        } elseif ($type == 'my') {
//            $data['loginHistories'] = $loginHistoryQuery->where('user_id', auth()->id())->paginate(20);
//        } else {
//            $data['loginHistories'] = $loginHistoryQuery->paginate(20);
//        }
//
//        return view('activity.login-history-bk', $data);
//    }

    public function deleteLoginHistory($id) : JsonResponse
    {
        try {
            $loginLog = LoginActivity::findOrFail($id);
            $loginLog->forceDelete();
            return response()->json(['success' => true, 'message' => 'Login Log Deleted Successfully.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete Login Log.', 'error' => $exception->getMessage()], 500);
        }
    }
}
