<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Repositories\AtcLeadsRepositoryEloquent;

class AtcController extends Controller
{
    protected $leadsRepository;

    public function __construct(
        AtcLeadsRepositoryEloquent $leadsRepository
    ){
        $this->leadsRepository = $leadsRepository;
    }


    //
	public function processData(Request $request)
    {
        try{

            // Acceder a todos los datos enviados por POST
            $allData = $request->all();
            $fecha_lead = new Carbon($allData['date_created'] ?? now());

            $leadData = [
                "name" => $allData['full_name'] ?? ($allData['first_name'] ?? 'Sin nombre'. ' '.$allData['last_name'] ?? ''),
                "email" => $allData['email'] ?? null,
                "phone" => $allData['phone'] ?? null,
                "campaign" => $allData['contact']['lastAttributionSource']['campaign'] ?? $allData['contact']['attributionSource']['campaign'] ?? null,
                "utmSource" => $allData['contact']['lastAttributionSource']['utmSource'] ?? $allData['contact']['attributionSource']['utmSource'] ?? null,
                "utmMedium" => $allData['contact']['lastAttributionSource']['utmMedium'] ?? $allData['contact']['attributionSource']['utmMedium'] ?? null,
                "utmContent" => $allData['contact']['lastAttributionSource']['utmContent'] ?? $allData['contact']['attributionSource']['utmContent'] ?? null,
                "utmTerm" => $allData['contact']['lastAttributionSource']['utmTerm'] ?? $allData['contact']['attributionSource']['utmTerm'] ?? null,
                "utmKeyword" => $allData['contact']['lastAttributionSource']['utmKeyword'] ?? $allData['contact']['attributionSource']['utmKeyword'] ?? null,
                "utmMatchtype" => $allData['contact']['lastAttributionSource']['utmMatchtype'] ?? $allData['contact']['attributionSource']['utmMatchtype'] ?? null,
                "date_created" => $fecha_lead->tz('America/Mexico_City')->toDateTimeString(),
                "fullData" => $allData
            ];

            $lead = $this->leadsRepository->firstOrCreate(["contact_id" => $allData['contact_id']]);
            $lead->fill($leadData)->save();

            return response()->json(['success' => true, 'lead_id' => $lead->id]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
