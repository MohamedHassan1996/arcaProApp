<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\MaintenanceStockItem;
use App\Models\ReportProductBarcode;
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


            DB::beginTransaction();

            $monthsOfPeriodicMaintenance = 3;
            $monthsOfControlledMaintenance = 6;

            foreach ($data['reports'] as $key => $report) {
                $path = null;

                if(isset($data['path'])) {
                    $path = Storage::disk('public')->putAs('maintenance_reports', $report['path'], Str::random(10));
                }

                $maintenanceProductGuids = DB::connection('proMaintenances')
                    ->table('maintenances')
                    ->join('maintenance_details', 'maintenance_details.maintenance_guid', '=', 'maintenances.guid')
                    ->where('maintenances.guid', $report['maintenanceGuid'])
                    ->pluck('maintenance_details.product_guids') // get product_guids from details
                    ->toArray();

                // explode '##' into array of product GUIDs
                $maintenanceProductGuids = array_map(fn($guids) => explode('##', $guids), $maintenanceProductGuids);

                // optional: flatten the array if you want a single list of GUIDs
                $maintenanceProductGuids = array_merge(...$maintenanceProductGuids);

                $maintenanceReport = MaintenanceReport::create([
                    'maintenance_guid' => $report['maintenanceGuid'],
                    'leave_at' => $report['leaveAt'],
                    'arrive_at' => $report['arriveAt'],
                    'is_one_work_period' => $report['isOneWorkPeriod'],
                    'work_times' => $report['workTimes'],
                    'numbe_of_meals' => $report['numberOfMeals'],
                    'product_codices' => $report['productCodices'],
                    'note' => $report['note'],
                    'path' => $path,
                    'report_date' => $report['date']
                ]);

                $maintenance = Maintenance::where('guid', $report['maintenanceGuid'])->first();

                foreach ($report['productCodices'] as $key => $productCodice) {
                    $reportProductBarcode = ReportProductBarcode::create([
                        'maintenance_report_id' => $maintenanceReport->id,
                        'product_barcode' => $productCodice,
                        'product_guid' => $maintenanceProductGuids[$key],
                        'maintenance_type' => 1
                    ]);


                    $nextMaintenanceDate = null;
                    if($reportProductBarcode->maintenance_type == MaintenanceType::MAINTANANCE || $reportProductBarcode->maintenance_type == MaintenanceType::INSTALLATION) {
                        $nextMaintenanceDate = $reportProductBarcode->report_date->addMonths($monthsOfPeriodicMaintenance);

                    }elseif($reportProductBarcode->maintenance_type == MaintenanceType::CONTROL) {
                        $nextMaintenanceDate = $reportProductBarcode->report_date->addMonths($monthsOfControlledMaintenance);
                    }

                    CalendarEvent::create([
                        'title' => $reportProductBarcode->product_barcode,
                        'description' => null,
                        'maintenance_type' => MaintenanceType::MAINTANANCE->value,
                        'start_at' => $nextMaintenanceDate,
                        'end_at' => $nextMaintenanceDate,
                        'is_all_day' => true,
                        'client_guid' => $maintenance->anagraphic_guid
                    ]);

                }

                $stockItems = $report['stockItems'];

                foreach ($stockItems as $stockItemData) {
                    $stockItem = MaintenanceStockItem::create([
                        'maintenance_report_id' => $maintenanceReport->id,
                        'stock_item_guid' => $stockItemData['vehicleStockGuid'],
                        'quantity' => $stockItemData['quantity'],
                    ]);

                    DB::connection('arca_pro')->table('tb_magazzino')->where('guid', $stockItemData['vehicleStockGuid'])->update([
                        'quantita' => DB::raw('quantita - ' . $stockItemData['quantity']),
                        'versione' => now()
                    ]);
                }
            }


            DB::commit();

            return ApiResponse::success([], __('crud.created'));

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }
}
