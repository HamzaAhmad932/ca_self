<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('admin.audits.audit');
    }

    public function getModelsName(Request $request)
    {
        try {
            $dir = '../app';
            $files = scandir($dir);
            $models = array();

            foreach($files as $file) {
                //skip current and parent folder entries and non-php file
                if ($file == '.' || $file == '..' || !preg_match('/.php/', $file)) continue;
                $models[] = preg_replace('/.php/', '', $file);

            }

            return $this->apiSuccessResponse(200, $models, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function getColumnsName(Request $request)
    {
        try {
            $model_name = $request->model_name;
            $table = 'App\\' . $model_name ;
            $item = new $table();
            $table = $item->getTable();

            $columns = DB::select('show columns from ' . $table);

            return $this->apiSuccessResponse(200, $columns, 'success');

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

    public function getAuditData(Request $request)
    {
        try {
            $this->validate($request, ['filters' => 'required|array']);
            $filters = $request->filters;

            $from = '';
            $to = '';
            if (!empty($filters['fromDate']) && !empty($filters['toDate'])) {
                $from = Carbon::parse($filters['fromDate'])->format('Y-m-d');
                $to = Carbon::parse($filters['toDate'])->format('Y-m-d');
            } else {
                $from = Carbon::now()->subDays(10)->format('Y-m-d');
                $to = Carbon::now()->format('Y-m-d');
            }

            $model_name = $filters['modelName'];
            $model = 'App\\' . $model_name ;
            $item = new $model();
            $table = $item->getTable();
            $columns = DB::select('show columns from ' . $table);
            $where = [];
            $select = [];
            $audit_data = '';
            $counter = 0;

            foreach ($filters['columns'] as $column) {
                if (!is_array($column['name']) && !empty($column['value'])) {
                    foreach ($columns as $column_name) {
                        $where[$counter] = [$column_name->Field, 'like', '%'.$column['value'].'%'];
                        $counter++;
                    }
                } elseif (is_array($column['name']) && !empty($column['value'])) {
                    $select[$counter] = $column['name']['columnName'];
                    $where[$counter] = [$column['name']['columnName'], 'like', '%'.$column['value'].'%'];
                } elseif (is_array($column['name']) && empty($column['value'])) {
                    $select[$counter] = $column['name']['columnName'];
                }
                $counter++;
            }

            $audit_data = $model::whereBetween('created_at', [$from, $to]);

            if (count($select) > 0) {
                $audit_data = $audit_data->select($select);
            }
            if (count($where) > 0) {
                $audit_data = $audit_data->where($where);
            }

            $audit_data = $audit_data->get();

            if (count($audit_data) > 0) {
                return $this->apiSuccessResponse(200, ['audits' => $audit_data, 'fromDate' => $from, 'toDate' => $to], 'success');
            } else {
                return $this->apiErrorResponse('Record not founc', 404);
            }

        } catch (Exceptions $e) {
            return $this->apiErrorResponse($e->getMessage(), 404);
        }
    }

}
