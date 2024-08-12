<?php

namespace App\Http\Controllers;

use App\Models\eventos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EventosController extends Controller
{
    public function index()
    {
        // $data =  DB::select("");
        $data =  eventos::all();
        return response()->json($data, 200);
    }
    public function EventoActivo()
    {
        $data = eventos::where('Estado','=','ACTIVO')->get();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if($request->hasFile('ImagenQR')){
            $file = $request->file('ImagenQR');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/BoletasEventos/QRs/',$namefile);
        }
        if($request->hasFile('ImagenQR')){
            $data['ImagenQR']='BoletasEventos/QRs/'.$namefile;
        }
        else{
            $data['ImagenQR']='';
        }
        eventos::insert($data);
        return response()->json(["mensaje" => "eventos Registrado Correctamente"], 200);
    }

    public function show($id)
    {
        $data = eventos::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function updateEvento(Request $request, $id)
    {
        $data = $request->all();
        // $inscrito = eventos::findOrFail($id);
        $even = eventos::where('id','=',$id)->firstOrFail();

        if ($request->hasFile('ImagenQR'))
        {
            // ELIMINANDO ANTIGUA FOTO
            File::delete(public_path().'/'.$even->ImagenQR);
            //REALIZANDO CARGA DE LA NUEVA FOTO
            $file = $request->file('ImagenQR');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/BoletasEventos/QRs/',$namefile);
        }

        if ($request->hasFile('ImagenQR'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $data['ImagenQR'] = 'BoletasEventos/QRs/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $data['ImagenQR'] = $even->ImagenQR;
        }

        eventos::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "eventos Modificado Correctamente"], 200);
    }
    public function destroy($id)

    {

        $even =eventos::findOrFail($id);
        File::delete(public_path().'/'.$even->ImagenQR);
        $data =  DB::select("delete from eventos where id='$id'");
        return response()->json(["mensaje" => "eventos Eliminado Correctamente"], 200);
    }
}
