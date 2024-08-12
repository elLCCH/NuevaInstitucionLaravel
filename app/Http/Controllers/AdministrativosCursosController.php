<?php

namespace App\Http\Controllers;

use App\Models\Administrativos_Cursos;
use Illuminate\Http\Request;

class AdministrativosCursosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function PruebaExistencia(Request $request)
    {
        $admin = $request->input('admin_id');
        $curso = $request->input('curso_id');
        // $verificacion = Administrativos_Cursos::where('Admin_id','=',$admin)->get();  
        // $verificacion = verificacion::where('Curso_id','=',$curso)->get();  
        $verificacion = Administrativos_Cursos::where('Admin_id','=', $admin)->where('Curso_id','=',$curso)->first();
        if ($verificacion != "" || $verificacion !=null) {
            // return $verificacion;
            return 'EXISTE';
        } else {
            return 'NOTEXISTE';
        }
        
    }
    public function EliminarAdminCursos(Request $request)
    {
        $admin = $request->input('admin_id');
        $curso = $request->input('curso_id');
        
        $verificacion = Administrativos_Cursos::where('Admin_id','=', $admin)->where('Curso_id','=',$curso)->first();
        Administrativos_Cursos::destroy($verificacion->id);  
        return 'SE ELIMINO DEL EL ADMIN DEL CURSO';
        
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $curso = $request->input('curso_id');
        $admin = $request->input('admin_id');
        $AdminCurso = new Administrativos_Cursos();
        $AdminCurso->Curso_id= (int)$curso;
        $AdminCurso->Admin_id= (int)$admin;
        $AdminCurso->save();
        // return 'Estudiante Guardado';
        return $AdminCurso;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Administrativos_Cursos  $administrativos_Cursos
     * @return \Illuminate\Http\Response
     */
    public function show(Administrativos_Cursos $administrativos_Cursos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Administrativos_Cursos  $administrativos_Cursos
     * @return \Illuminate\Http\Response
     */
    public function edit(Administrativos_Cursos $administrativos_Cursos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Administrativos_Cursos  $administrativos_Cursos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Administrativos_Cursos $administrativos_Cursos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Administrativos_Cursos  $administrativos_Cursos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Administrativos_Cursos $administrativos_Cursos)
    {
        //
    }
}
