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
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
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
        $productBarcode = $request->productBarcode;

        $productBarcodeHistory = ReportProductBarcode::where('product_barcode', $productBarcode)->get();

        if (!$productBarcodeHistory->isEmpty()) {

            $report = MaintenanceReport::where('id', $productBarcodeHistory->first()->maintenance_report_id)->first();
            $maintenance = Maintenance::where('guid', $report->maintenance_guid)->first();
            $client = $maintenance->anagraphic;

            $address = AnagraphicAddress::where('anagraphic_guid', $client->guid)->first();
            $addressFormatted = $address->address . ' ' . $address->cap . ' ' . $address->city . ' (' . $address->province . ')';

            $productBarcodeHistoryFormatted = $productBarcodeHistory->map(function ($item) {
                return [
                    'maintenanceType' => $item->maintenance_type,
                    'maintenanceDate' => $item->created_at->format('d/m/Y'),
                ];
            })->toArray();

            return ApiResponse::success([
                'productBarcode' => $productBarcode,
                'productCodice' => '', // fill if needed
                'productDescription' => '', // fill if needed
                'clientName' => $client->regione_sociale,
                'clientAddress' => $addressFormatted,
                'productBarcodeHistory' => $productBarcodeHistoryFormatted
            ]);
        }

        // // Else: Query external SQL Server view
        // $qry = "
        //     SELECT
        //     A.azatt,
        //     A.Data,
        //     A.GruppoAttivita,
        //     A.TipoAttivita,
        //     A.RagioneSociale,
        //     A.CodiceProdotto,
        //     A.Matricola,
        //     A.Effettuato,
        //     A.Indirizzo,
        //     A.Localita,
        //     A.Provincia,
        //     A.CodGruppoAttivita,
        //     A.Note,
        //     A.Agente,
        //     A.IdProdottoSoggetto,
        //     A.CodiceAgente,
        //     A.DataUltimaManutenzione,
        //     D.MVDESART AS description
        // FROM [I5W_PRO_AHR].[dbo].[V_Attivita] A
        // LEFT JOIN [I5W_PRO_AHR].[dbo].[V_DDT_MATRICOLE] D
        //     ON A.Matricola = D.MTCODMAT
        // WHERE A.Matricola = ? AND A.Effettuato = 1

        // ";

        // $rawResults = DB::connection('db_pro_ms')->select($qry, [$productBarcode]);

        // if (empty($rawResults)) {
        //     return ApiResponse::error('No data found for this barcode.', []);
        // }

        // // Take first row for client info
        // $first = (array) $rawResults[0];

        // $maintenanceType = [
        //     'MANUTENZIONE' => MaintenanceType::MAINTANANCE->value,
        //     'INSTALLAZIONE' => MaintenanceType::INSTALLATION->value,
        //     'CONTROLLO' => MaintenanceType::CONTROL->value
        // ];

        // return ApiResponse::success([
        //     'productBarcode' => $first['Matricola'] ?? '',
        //     'productCodice' => $first['CodiceProdotto'] ?? '',
        //     'productDescription' => $first['description'] ?? '',
        //     'clientName' => $first['RagioneSociale'] ?? '',
        //     'clientAddress' => trim("{$first['Indirizzo']} {$first['Localita']} ({$first['Provincia']})"),
        //     'productBarcodeHistory' => collect($rawResults)->map(function ($row)use($maintenanceType) {
        //         return [
        //             'maintenanceType' => $maintenanceType[$row->GruppoAttivita],
        //             'maintenanceDate' => Carbon::parse($row->Data)->format('d/m/Y'),
        //         ];
        //     }),
        // ]);
    }



}
