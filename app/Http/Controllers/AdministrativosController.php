<?php

namespace App\Http\Controllers;

use App\Models\Administrativos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdministrativosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $administrativo = Administrativos::all();
        // $administrativo =  DB::select("select * from administrativos order by Ap_Paterno, Ap_Materno, Nombre"); //FUNCIONA PERO NO INCLUYE CANTIDAD DE ESTUDIANTES POR DOCENTE
        $administrativo =  DB::select("SELECT a.*, (select COUNT(*) AS CantidadEstudiantes from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%' AND a.id=e.Admin_id) as CantidadEstudiantesDocente
        FROM administrativos a  order by a.Ap_Paterno, a.Ap_Materno, a.Nombre"); //ESTE ES EL ACTUAL QUE INCLUYE ESTOS DATOS
        return $administrativo;
    }
    public function DiferenciadorIndex(Request $request)
    {
        // $administrativo = Administrativos::all();
        $tipo = $request->query('tipo');
        $administrativo = Administrativos::whereRaw('tipo=?',$tipo)->get();
        return $administrativo;
    }
    public function EncontrarDocenteEspecialidad(Request $request,$id)
    {


        $docente = Administrativos::where('id','=', $id)->first();

        return $docente;
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

        if($request->hasFile('Foto')){
            $file = $request->file('Foto');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/administrativos/',$namefile);
        }



        $administrativo = new Administrativos();
        if($request->hasFile('Foto')){$administrativo->Foto = 'Administrativos/'.$namefile;} else{$administrativo->Foto = '';}
        $administrativo->Ap_Paterno= $request->input('Ap_Paterno');
        $administrativo->Ap_Materno= $request->input('Ap_Materno');
        $administrativo->Nombre= $request->input('Nombre');
        $administrativo->Sexo= $request->input('Sexo');
        $administrativo->FechNac= $request->input('FechNac');
        $administrativo->CI= $request->input('CI');
        $administrativo->Tipo= $request->input('Tipo');
        $administrativo->Password= Hash::make($request->input('Password')) ;
        //$administrativo->curso_id= $request->input('curso_id');
        $administrativo->Estado= $request->input('Estado');
        $administrativo->Celular= $request->input('Celular');
        $administrativo->CelularTrabajo= $request->input('CelularTrabajo');
        $administrativo->Cargo= $request->input('Cargo');
        $administrativo->Biografia= $request->input('Biografia');
        $administrativo->save();
        return 'administrativo Guardado';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Administrativos  $administrativos
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $data = Administrativos::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Administrativos  $administrativos
     * @return \Illuminate\Http\Response
     */
    public function edit(Administrativos $administrativos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Administrativos  $administrativos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Administrativos $administrativos)
    {
        //
    }
    public function actualizar(Request $request, $id)
    {

        $requestData = $request->all();
        $administrativo =Administrativos::findOrFail($id);
        if ($request->hasFile('Foto'))
        {
            // ELIMINANDO ANTIGUA FOTO

            File::delete(public_path().'/'.$administrativo->Foto);
            //REALIZANDO CARGA DE LA NUEVA FOTO
            $file = $request->file('Foto');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/administrativos/',$namefile);

            // return 'paso';
        }
        // $requestData['Foto'] = 'Administrativos/'.$namefile;

        if ($request->hasFile('Foto'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $requestData['Foto'] = 'administrativos/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $requestData['Foto'] = $administrativo->Foto;
        }

        //SINO SE ENVIO EL PARAMETRO Password hacer
        if ($request->has('Password')) {
            //SI SE ENVIO
            //SI NO ES TIPO HASH CREAR NUEVO HASH
            if (Hash::needsRehash($request->Password))
            {
                $requestData['Password'] = Hash::make($request->Password);
            }
        } else {
            //NO SE ENVIO
            $requestData['Password'] = $administrativo->Password;
        }

        Administrativos::where('id','=',$id)->update($requestData);
        // return 'Datos administrativo Modificados';
        // return $request;
        return $requestData;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Administrativos  $administrativos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         // ELIMINANDO ANTIGUA FOTO
         $administrativo =Administrativos::findOrFail($id);
         if(File::delete(public_path().'/'.$administrativo->Foto))
         {
             Administrativos::destroy($id);
             return 'eliminado';
         }
         else {
            Administrativos::destroy($id);
             return 'eliminado';
         }
         return 'Eliminacion Correcta';
    }
    public function autentificar(Request $request)
    {


        //FINISH
        $CI = $request->input('CI');
        $pass = $request->input('Password');
        $admin = Administrativos::where('CI','=', $CI)->first();

        try {
            if (Hash::check($pass, $admin->Password)) {

            return $admin;
            }
            else
            {
                // return $admin;
                return 'NOLOG';

            }
        } catch (\Throwable $th) {
            return 'NOLOG';
        }

    }
}
