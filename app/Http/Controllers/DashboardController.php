<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function dashboard() : View
    {
//        $logMessage = 'Log Message';
//        Log::emergency($logMessage);
//        Log::alert($logMessage);
//        Log::critical($logMessage);
//        Log::error($logMessage);
//        Log::warning($logMessage);
//        Log::notice($logMessage);
//        Log::info($logMessage);
//        Log::debug($logMessage);

        return view('dashboard');
    }
}
