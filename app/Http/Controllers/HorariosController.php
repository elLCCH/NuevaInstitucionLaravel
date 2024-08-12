<?php

namespace App\Http\Controllers;

use App\Models\horarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HorariosController extends Controller
{
    public function index(Request $request)
        {
            // $data =  DB::select("");

            $Anio_id = $request->query('Anio_id');
            $data = horarios::whereRaw('Anio_id=?',$Anio_id)->orderBy('NivelCurso','desc')->get();
            return response()->json($data, 200);
        }

        public function store(Request $request)
        {
            $data = $request->all();
            if($request->hasFile('Horario')){
                $file = $request->file('Horario');
                $namefile = time().$file->getClientOriginalName();
                $file->move(public_path().'/Horarios/',$namefile);
            }
            if($request->hasFile('Horario')){
                $data['Horario']='Horarios/'.$namefile;
            }
            else{
                $data['Horario']='';
            }
            Horarios::insert($data);
            return response()->json(["mensaje" => "Horarios Registrado Correctamente"], 200);

        }

        public function show($id)
        {
            $data = Horarios::where('id','=',$id)->firstOrFail();
            return response()->json($data, 200);
        }

        public function updateHorario(Request $request, $id)
        {
            $data = $request->all();
            // $inscrito = horarios::findOrFail($id);
            $even = horarios::where('id','=',$id)->firstOrFail();

            if ($request->hasFile('Horario'))
            {
                // ELIMINANDO ANTIGUA FOTO
                File::delete(public_path().'/'.$even->Horario);
                //REALIZANDO CARGA DE LA NUEVA FOTO
                $file = $request->file('Horario');
                $namefile = time().$file->getClientOriginalName();
                $file->move(public_path().'/Horarios/',$namefile);
            }

            if ($request->hasFile('Horario'))
            {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
                $data['Horario'] = 'Horarios/'.$namefile;
            }
            else
            {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
                $data['Horario'] = $even->Horario;
            }

            horarios::where('id','=',$id)->update($data);
            return response()->json(["mensaje" => "horarios Modificado Correctamente"], 200);
        }
        public function destroy($id)

        {

            $even =horarios::findOrFail($id);
            File::delete(public_path().'/'.$even->Horario);
            $data =  DB::select("delete from horarios where id='$id'");
            return response()->json(["mensaje" => "horarios Eliminado Correctamente"], 200);

        }
}
