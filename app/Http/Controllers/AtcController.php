<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AtcController extends Controller
{
    //
	public function processData(Request $request)
    {
        // Acceder a todos los datos enviados por POST
        $allData = $request->all();
        $contents = json_encode($allData, JSON_PRETTY_PRINT);
        Storage::put(uniqid().'.json', $contents);

        // Si quieres acceder a un campo específico, por ejemplo 'nombre'
        // $nombre = $request->input('nombre');

        // Puedes retornar los datos directamente para mostrarlos en pantalla
        return response()->json($allData);

        // O si quieres pasar los datos a una vista (Blade)
        // return view('show-data', ['data' => $allData]);
    }
}
