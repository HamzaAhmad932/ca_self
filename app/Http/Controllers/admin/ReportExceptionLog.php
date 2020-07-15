<?php

namespace App\Http\Controllers\admin;

use App\ExceptionLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportExceptionLog extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        return view('admin.pages.report_exception_logs');
    }

    public function getExceptions(Request $request) {

        $user_id = (int) $request->post('user_id');
        $user_account_id = (int) $request->post('user_account_id');
        $booking_info_id = (int) $request->post('booking_info_id');
        $pms_booking_id = (int) $request->post('pms_booking_id');
        $limit = (int) $request->post('limit');
        $start = (int) $request->post('start');

        $exceptions = ExceptionLog::where('id', '>', 0)->select('created_at', 'message', 'file', 'line', 'id');

        if($user_id > 0)
            $exceptions = $exceptions->where('user_id', $user_id);

        if($user_account_id > 0)
            $exceptions = $exceptions->where('user_account_id', $user_account_id);

        if($booking_info_id > 0)
            $exceptions = $exceptions->where('booking_info_id', $booking_info_id);

        if($pms_booking_id > 0)
            $exceptions = $exceptions->where('booking_pms_id', $pms_booking_id);

        if($limit > 1)
            $exceptions = $exceptions->limit($limit);

        if($start > 0)
            $exceptions = $exceptions->offset($start);

        $exceptions = $exceptions->orderBy('id', 'desc')->get();

        for($i = 0; $i < count($exceptions); $i++) {
            $exceptions[$i]->date = date_format($exceptions[$i]->created_at,'D M d h:i A');
        }

        return response()->json(['exceptions'=>$exceptions]);
    }

    public function getException(Request $request) {
        $id = (int) $request->post('id');
        $exception = ExceptionLog::where('id', $id)->first();
        $exception->date = date_format($exception->created_at,'D M d h:i A');
        return response()->json(['exception'=>$exception]);
    }

}
