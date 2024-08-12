<?php

namespace App\Http\Controllers;

use App\Models\areaeventos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaeventosController extends Controller
{
  public function index()
    {
        // $data =  DB::select("");
        $data =  areaeventos::all();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        areaeventos::insert($data);
        return response()->json(["mensaje" => "areaeventos Registrado Correctamente"], 200);
    }

    public function show($id)
    {
        $data = areaeventos::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        areaeventos::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "areaeventos Modificado Correctamente"], 200);
    }
    public function destroy($id)

    {
        $data =  DB::select("delete from areaeventos where id='$id'");
        return response()->json(["mensaje" => "areaeventos Eliminado Correctamente"], 200);
    }
}
