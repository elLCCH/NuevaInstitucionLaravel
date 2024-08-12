<?php

namespace App\Http\Controllers;

use App\Models\archivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ArchivosController extends Controller
{
    public function index()
    {
        // $data =  DB::select("");
        $data =  archivos::all();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $resultados = [];

        if ($request->hasFile('archivos')) {
            $archivos = $request->file('archivos');

            foreach ($archivos as $archivo) {
                $nombreArchivo = time() . '¡' . $archivo->getClientOriginalName();
                $archivo->move(public_path() . '/Archivos/Materiales/', $nombreArchivo);
                // $archivosGuardados[] = 'Archivos/Materiales/' . $nombreArchivo;

                $a = new archivos();
                $a->File = 'Archivos/Materiales/' . $nombreArchivo;
                $a->id_material = $request->input('idMaterial');
                $a->save();
                $resultados[] = [
                    'NombreArchivo' => $a->File,
                    'id' => $a->id, // Asegúrate de que tu modelo tenga un id autogenerado
                ];

            }

            return response()->json(["mensaje" => "SE SUBIERON LOS ARCHIVOS CORRECTAMENTE", "data" => $resultados], 200);
        }

        return response()->json(["mensaje" => "NO SE SUBIO NINGUN ARCHIVO","data" => $resultados], 200);
    }



    public function show($id)
    {
        $data = archivos::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        archivos::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "archivos Modificado Correctamente"], 200);
    }
    public function destroy($id)
    {
        $archivo =archivos::findOrFail($id);
        if(File::delete(public_path().'/'.$archivo->File))
        {
            DB::select("delete from archivos where id='$id'");
            return response()->json(["mensaje" => "archivos Eliminado Correctamente"], 200);
        }else{
            DB::select("delete from archivos where id='$id'");
            return response()->json(["mensaje" => "SOLO SE ELIMINO DATO DE BD"], 200);
        }

    }
}
