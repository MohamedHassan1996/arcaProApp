<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceReport;
use App\Models\MaintenanceStockItem;
use App\Models\Stock;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorMaintenanceReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function store(Request $request) {



        try {
            $data = $request->all();

            $path = null;

            DB::beginTransaction();

            if(isset($data['path'])) {
                $path = Storage::disk('public')->putAs('maintenance_reports', $data['path'], Str::random(10));
            }

            $maintenanceReport = MaintenanceReport::create([
                'maintenance_guid' => $data['maintenanceGuid'],
                'leave_at' => $data['leaveAt'],
                'arrive_at' => $data['arriveAt'],
                'is_one_work_period' => $data['isOneWorkPeriod'],
                'work_times' => $data['workTimes'],
                'numbe_of_meals' => $data['numbeOfMeals'],
                'product_codices' => $data['productCodices'],
                'note' => $data['note'],
                'path' => $path,
            ]);

            $stockItems = $data['stockItems'];

            foreach ($stockItems as $stockItemData) {
                $stockItem = MaintenanceStockItem::create([
                    'maintenance_report_guid' => $maintenanceReport->guid,
                    'stock_item_guid' => $stockItemData['stockItemGuid'],
                    'quantity' => $stockItemData['quantity'],
                ]);

                Stock::where('guid', $stockItemData['stockItemGuid'])->decrement('quantity', $stockItemData['quantity']);
            }


            DB::commit();

            return ApiResponse::success([], __('crud.created'));

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }
}
