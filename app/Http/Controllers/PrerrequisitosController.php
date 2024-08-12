<?php

namespace App\Http\Controllers;

use App\Models\Prerrequisitos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrerrequisitosController extends Controller
{/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $Prerrequisitos = Prerrequisitos::all();
        $Prerrequisitos =  DB::select("select p.id,p.id_materia_p,p.id_materia_s,
        m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin',
        m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec', p.Malla, m.NivelCurso as 'niv_prin',m2.NivelCurso as 'niv_sec'
        from prerrequisitos p LEFT JOIN
        cursos m ON m.id=p.id_materia_p LEFT JOIN
        cursos m2 ON m2.id=p.id_materia_s");
        return $Prerrequisitos;
    }
    public function indexListarxGestion(Request $request)
    {
        $Anio_id = $request->input('Anio_id');
        $Prerrequisitos =  DB::select("select p.id,p.id_materia_p,p.id_materia_s,p.Anio_id,
        m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin',
        m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec', p.Malla, m.NivelCurso as 'niv_prin',m2.NivelCurso as 'niv_sec'
        from prerrequisitos p LEFT JOIN
        cursos m ON m.id=p.id_materia_p LEFT JOIN
        cursos m2 ON m2.id=p.id_materia_s where p.Anio_id=$Anio_id");
        return $Prerrequisitos;
    }
    public function ObtenerPrerrequisitoxGestionyMalla(Request $request)
    {
        $Anio_id = $request->input('Anio_id');
        $Malla = $request->input('Malla');
        $Prerrequisitos =  DB::select("select p.id,p.id_materia_p,p.id_materia_s,p.Anio_id,
        m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin',
        m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec', p.Malla, m.NivelCurso as 'niv_prin',m2.NivelCurso as 'niv_sec'
        from prerrequisitos p LEFT JOIN
        cursos m ON m.id=p.id_materia_p LEFT JOIN
        cursos m2 ON m2.id=p.id_materia_s where p.Anio_id=$Anio_id and p.Malla='$Malla'");
        return $Prerrequisitos;
    }
    public function store(Request $request)
    {
        $requestData = $request->all();
        Prerrequisitos::insert($requestData);
        return 'Prerrequisitos creado';
    }
    public function show($id)
    {
        $data = Prerrequisitos::where('id','=',$id)->firstOrFail();
        return $data;
    }
    public function update(Request $request, $id)
    {
         $requestData = $request->all();
        Prerrequisitos::where('id','=',$id)->update($requestData);
        return $requestData;
    }

    public function destroy($id)
    {
        Prerrequisitos::destroy($id);
        return 'Prerrequisitos eliminado';
    }

}
