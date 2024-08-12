<?php

namespace App\Http\Controllers;

use App\Models\materias_materiales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MateriasMaterialesController extends Controller
{
    public function index()
    {
        // $data =  DB::select("");
        $data =  materias_materiales::all();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        // $data = $request->all();
        // materias_materiales::insert($data);
        $a = new materias_materiales();
        $a->id_curso = $request->input('id_curso');
        $a->id_material = $request->input('id_material');
        $a->save();

        $dataRecienAdicionado =  DB::select("select cu.NombreCurso,cu.NivelCurso,cu.Malla,cu.Anio_id,mm.id_curso,mm.id_material from cursos cu, materias_materiales mm where cu.id = mm.id_curso and mm.id = $a->id");

        return response()->json(["mensaje" => "materiales Registrado Correctamente","data" => $dataRecienAdicionado[0]], 200);
    }

    public function show($id)
    {
        $data = materias_materiales::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        materias_materiales::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "materias_materiales Modificado Correctamente"], 200);
    }
    public function destroy($id)

    {
        $data =  DB::select("delete from materias_materiales where id='$id'");
        return response()->json(["mensaje" => "materias_materiales Eliminado Correctamente"], 200);
    }

}
