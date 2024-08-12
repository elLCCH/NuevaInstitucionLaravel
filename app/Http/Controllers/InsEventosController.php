<?php

namespace App\Http\Controllers;

use App\Models\ins_eventos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InsEventosController extends Controller
{
    public function index()
    {
        $data =  DB::select("SELECT `ins_eventos`.*, `eventos`.`Nombre_Evento`
        FROM `ins_eventos`
            LEFT JOIN `eventos` ON `ins_eventos`.`id_evento` = `eventos`.`id` order by ins_eventos.id desc;");
        // $data =  ins_eventos::all();
        return response()->json($data, 200);
    }
    public function ObtenerPostulantesEvento($idEvento)
    {
        $data =  DB::select("SELECT `ins_eventos`.*, `eventos`.`Nombre_Evento`
        FROM `ins_eventos`
            LEFT JOIN `eventos` ON `ins_eventos`.`id_evento` = `eventos`.`id` where ins_eventos.id_evento=$idEvento order by ins_eventos.id desc;");
        return response()->json($data, 200);
    }
    public function store(Request $request)
    {
        // if($request->hasFile('Boleta_Pago')){
        //     $file = $request->file('Boleta_Pago');
        //     $namefile = time().$file->getClientOriginalName();
        //     $file->move(public_path().'/BoletasEventos/',$namefile);
        // }
        $data = $request->all();
        // if($request->hasFile('Boleta_Pago')){
        //     $data['Boleta_Pago']='BoletasEventos/'.$namefile;
        // }
        // else{
        //     $data['Boleta_Pago']='';
        // }
        ins_eventos::insert($data);
        return response()->json(["mensaje" => "ins_eventos Registrado Correctamente"], 200);
    }

    public function show($id)
    {
        $data = ins_eventos::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }
    public function SeleccionarPorCIidEvento(Request $request)
    {
        try {
            $CI = $request->CI;
            $IDEvento = $request->id_evento;

            $data = ins_eventos::where('CI', '=', $CI)
                               ->where('id_evento', '=', $IDEvento)
                               ->firstOrFail();

            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['data' => 'NO ENCONTRADO'], 200);
        }
    }

    public function updateInsEvento(Request $request, $id)
    {
        $data = $request->all();
        // $inscrito = ins_eventos::findOrFail($id);
        $inscrito = ins_eventos::where('id','=',$id)->firstOrFail();

        // if ($request->hasFile('Boleta_Pago'))
        // {
        //     // ELIMINANDO ANTIGUA FOTO
        //     File::delete(public_path().'/'.$inscrito->Boleta_Pago);
        //     //REALIZANDO CARGA DE LA NUEVA FOTO
        //     $file = $request->file('Boleta_Pago');
        //     $namefile = time().$file->getClientOriginalName();
        //     $file->move(public_path().'/BoletasEventos/',$namefile);
        // }

        // if ($request->hasFile('Boleta_Pago'))
        // {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
        //     $data['Boleta_Pago'] = 'BoletasEventos/'.$namefile;
        // }
        // else
        // {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
        //     $data['Boleta_Pago'] = $inscrito->Boleta_Pago;
        // }


        try {

        File::delete(public_path().'/'.$inscrito->Boleta_Pago);
        ins_eventos::where('id','=',$id)->update($data);
        return response()->json(["mensaje" =>$request], 200);
        } catch (\Throwable $th) {
            ins_eventos::where('id','=',$id)->update($data);
            return response()->json(["mensaje" =>$request], 200);
        }
    }
    public function destroy($id)
    {
        $inscrito =ins_eventos::findOrFail($id);
        File::delete(public_path().'/'.$inscrito->Boleta_Pago);
        $data =  DB::select("delete from ins_eventos where id='$id'");
        return response()->json(["mensaje" => "ins_eventos Eliminado Correctamente"], 200);
    }
}
