<?php

namespace App\Http\Controllers;

use App\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function  index() {
        $logs = Log::with('user')->get();

        return  view('backend.accounting.bitacora.index', ['logs' => $logs]);
    }
}
