<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\MaintenanceRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function store(Request $request)
{
    try {
        DB::beginTransaction();

        $request->validate([
            'productBarcode' => 'required|string',
            'note' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10000'
        ]);

        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('maintenance_requests', 'public'); // stored in storage/app/public/maintenance_requests
                $uploadedFiles[] = $path; // store only relative path like: maintenance_requests/example.pdf
            }
        }

        // Send email
        Mail::to('mr10dev10@gmail.com')->send(new MaintenanceRequestMail(
            $request->productBarcode,
            $request->note??'',
            $uploadedFiles // pass the relative paths only
        ));

        DB::commit();

        return ApiResponse::success([], 'Maintenance request sent successfully.');
    } catch (\Throwable $th) {
        DB::rollBack();
        throw $th;
    }
}



}
