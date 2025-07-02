<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Anagraphic;
use App\Models\AnagraphicAddress;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;


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
        $barcode = $request->productBarcode;

        $clientProductBarcode = DB::connection('proMaintenances')->table('anagraphic_product_codes')->where('barcode', $barcode)->first();

        if (!$clientProductBarcode) {
            return ApiResponse::error('Product not found');
        }

        $calendarEvent = CalendarEvent::where('product_barcode', $barcode)->first();

        $installation = CalendarEvent::where('product_barcode', $barcode)
            ->where('maintenance_type', MaintenanceType::INSTALLATION->value)
            ->orderByDesc('start_at')
            ->first();

        $history = CalendarEvent::where('product_barcode', $barcode)
            ->orderBy('start_at')
            ->get()
            ->map(fn($item) => [
                'maintenanceType' => $item->maintenance_type,
                'maintenanceDate' => $item->start_at->format('d/m/Y'),
            ])
            ->toArray();

        $client = Anagraphic::where('guid', $calendarEvent->client_guid)->first();
        $address = AnagraphicAddress::where('anagraphic_guid', $calendarEvent->client_guid)->first();

        return [
            'maintenanceType' => $calendarEvent->maintenance_type,
            'productBarcode' => $calendarEvent->product_barcode,
            'productCode' => $clientProductBarcode->codice,
            'productDescription' => trim($calendarEvent->description) . ' - ' . $calendarEvent->product_barcode,
            'maintenanceDate' => $calendarEvent->start_at->format('d/m/Y'),
            'clientName' => $client?->regione_sociale ?? '',
            'clientAddress' => $address
                ? trim("{$address->address} {$address->city} ({$address->province})")
                : '',
            'installationDate' => $installation ? Carbon::parse($installation->start_at)->format('d/m/Y') : '',
            'productBarcodeHistory' => $history,
        ];


    }



}
