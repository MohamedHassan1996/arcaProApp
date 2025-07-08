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
use App\Models\ProParameterValue;
use App\Models\ReportProductBarcode;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            $monthsOfPeriodicMaintenance = (int)ProParameterValue::where('parameter_value', 'MANUTENZIONE')->where('parameter_id', 20)->first()->description ;
            $monthsOfControlledMaintenance = (int)ProParameterValue::where('parameter_value', 'CONTROLLO')->where('parameter_id', 20)->first()->description;


            $maintenanceTypesGuids = [
                '28e1c7d1-3a11-4660-8e6c-66dab6e17ec5' => MaintenanceType::INSTALLATION->value,
                'fa7202e8-65a4-49b4-83f5-39784ca1f22f' => MaintenanceType::MAINTANANCE->value,
                'e7740d9b-551f-416f-954c-a648c281d436' => MaintenanceType::CONTROL->value
            ];

            foreach ($data['reports'] as $key => $report) {
                $path = null;


                if(isset($report['path'])) {
                    $path = Storage::disk('public')->putFileAs('maintenance_reports', $report['path'], Str::random(10));
                }

                $maintenanceDetails = DB::connection('proMaintenances')
                    ->table('maintenances')
                    ->join('maintenance_details', 'maintenance_details.maintenance_guid', '=', 'maintenances.guid')
                    ->where('maintenances.guid', $report['maintenanceGuid'])
                    ->get();

                $maintenance = Maintenance::where('guid', $report['maintenanceGuid'])->first();

                $maintenanceProductGuids = [];

                foreach ($maintenanceDetails as $key => $detail) {
                    $exploded = explode('##', $detail->product_guids);

                    $clientProductBarcodes = DB::connection('proMaintenances')
                        ->table('anagraphic_product_codes')
                        ->whereIn('guid', $exploded)
                        ->get();

                    foreach ($clientProductBarcodes as $item) {

                        $hasMaintenacePeriod = null;

                        if($maintenanceTypesGuids[$detail->tipo_intervento_guid] == MaintenanceType::CONTROL->value) {
                            $hasMaintenacePeriod = DB::connection('proMaintenances')
                                ->table('product_maintenance_periods')
                                ->where('anagraphic_guid', $maintenance->anagraphic_guid)
                                ->where('product_barcode_guids', 'like', '%'.$item->guid.'%')
                                ->where('maintenance_type', '2')
                                ->first()?->period??null;

                            if(!$hasMaintenacePeriod) {
                                $hasMaintenacePeriod = DB::connection('proMaintenances')
                                    ->table('product_maintenance_periods')
                                    ->where('anagraphic_guid', $maintenance->anagraphic_guid)
                                    ->where('maintenance_type', '1')
                                    ->first()?->period??null;
                            }

                            if(!$hasMaintenacePeriod) {

                                $hasMaintenacePeriod = $monthsOfControlledMaintenance;
                            }


                        }elseif($maintenanceTypesGuids[$detail->tipo_intervento_guid] == MaintenanceType::MAINTANANCE->value) {
                            $hasMaintenacePeriod = DB::connection('proMaintenances')
                                ->table('product_maintenance_periods')
                                ->where('anagraphic_guid', $maintenance->anagraphic_guid)
                                ->where('product_barcode_guids', 'like', '%'.$item->guid.'%')
                                ->where('maintenance_type', '1')
                                ->first()?->period??null;
                            if(!$hasMaintenacePeriod) {
                                $hasMaintenacePeriod = DB::connection('proMaintenances')
                                    ->table('product_maintenance_periods')
                                    ->where('anagraphic_guid', $maintenance->anagraphic_guid)
                                    ->where('maintenance_type', '2')
                                    ->first()?->period??null;
                            }

                            if(!$hasMaintenacePeriod) {

                                $hasMaintenacePeriod = $monthsOfPeriodicMaintenance;
                            }
                        }
                        $maintenanceProductGuids[] = [
                            'productBarcodeGuid' => $item->guid,
                            'productDescription' => $item->description,
                            'productBarcode' => $item->barcode,
                            'maintenanceType' => $maintenanceTypesGuids[$detail->tipo_intervento_guid] ?? null,
                            'maintenancePeriod' => $hasMaintenacePeriod,
                        ];
                    }

                }



                $maintenanceStartDate = isset($report['date']) ? Carbon::parse($report['date'])->startOfDay() : Carbon::parse($maintenance->start_date)->startOfDay();

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
                    'report_date' => $maintenanceStartDate
                ]);

                foreach ($maintenanceProductGuids as $key => $productCodice) {
                    // $reportProductBarcode = ReportProductBarcode::create([
                    //     'maintenance_report_id' => $maintenanceReport->id,
                    //     'product_barcode' => $productCodice,
                    //     'product_guid' => $maintenanceProductGuids[$key],
                    //     'maintenance_type' => 1
                    // ]);

                    $event = CalendarEvent::where('product_barcode', $productCodice['productBarcode'])->where('maintenance_type', $productCodice['maintenanceType'])->orderByDesc('start_at')->where('is_done', 0)->first();


                    if($event) {
                        $event->is_done = 1;
                        $event->start_at = $maintenanceStartDate;
                        $event->end_at = $maintenanceStartDate;
                        $event->save();
                    }else{
                        CalendarEvent::create([
                            'title' => $productCodice['productDescription'],
                            'description' => $productCodice['productDescription'],
                            'maintenance_type' => $productCodice['maintenanceType'],
                            'product_barcode' => $productCodice['productBarcode'],
                            'start_at' => $maintenanceStartDate,
                            'end_at' => $maintenanceStartDate,
                            'is_all_day' => 1,
                            'client_guid' => $maintenance->anagraphic_guid
                        ]);
                    }


                    $nextMaintenanceDate = null;
                    if($productCodice['maintenanceType'] == MaintenanceType::MAINTANANCE->value || $productCodice['maintenanceType'] == MaintenanceType::INSTALLATION->value){
                        $nextMaintenanceDate = $maintenanceStartDate->addDays($productCodice['maintenancePeriod']);
                    }elseif($productCodice['maintenanceType'] == MaintenanceType::CONTROL->value) {
                        $nextMaintenanceDate = $maintenanceStartDate->addDays($productCodice['maintenancePeriod']);
                    }

                    $nextEvent = CalendarEvent::create([
                        'title' => $productCodice['productDescription'],
                        'description' => $productCodice['productDescription'],
                        'maintenance_type' => $productCodice['maintenanceType'],
                        'product_barcode' => $productCodice['productBarcode'],
                        'start_at' => $nextMaintenanceDate,
                        'end_at' => $nextMaintenanceDate,
                        'is_all_day' => 1,
                        'is_done' => 0,
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
