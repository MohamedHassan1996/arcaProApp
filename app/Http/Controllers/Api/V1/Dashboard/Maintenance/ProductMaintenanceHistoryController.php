<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use App\Models\ReportProductBarcode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\MaintenanceRequestMail;
use App\Models\AnagraphicAddress;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

use function PHPSTORM_META\map;

class ProductMaintenanceHistoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index(Request $request)
    {
        $productBarcodeHistory = ReportProductBarcode::where('product_barcode', $request->productBarcode)->get();

        $report = MaintenanceReport::where('id', $productBarcodeHistory->first()->maintenance_report_id)->first();

        $maintenance = Maintenance::where('guid', $report->maintenance_guid)->first();

        $client = $maintenance->anagraphic;

        $address = AnagraphicAddress::where('anagraphic_guid', $client->guid)->first();

        $addressFormatted = $address->address.' '.$address->cap.' '.$address->city.' ('.$address->province.')';


        $productBarcodeHistoryFormatted = $productBarcodeHistory->map(function ($item) {
            return [
                'maintenanceType' => $item->maintenance_type,
                'maintenanceDate' => $item->created_at->format('d/m/Y'),
            ];
        })->toArray();

        return ApiResponse::success([
            'productBarcode' => $request->productBarcode,
            'productCodice' => '',
            'productDescription' => '',
            'clientName' => $client->regione_sociale,
            'clientAddress' => $addressFormatted,
            'productBarcodeHistory' => $productBarcodeHistoryFormatted
        ]);
    }


}
