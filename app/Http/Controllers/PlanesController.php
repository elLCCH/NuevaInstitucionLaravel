<?php

namespace App\Http\Controllers;

use App\Models\planes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PlanesController extends Controller
{
    public function index(Request $request)
    {
        // $data =  DB::select("");

        $Anio_id = $request->query('Anio_id');
        // $data = planes::whereRaw('Anio_id=?',$Anio_id)->orderBy('NivelCurso','desc')->get();
        $data=DB::select("SELECT planes.id,planes.NivelCurso,planes.Descripcion,planes.Documento,planes.Anio_id,planes.Admin_id, administrativos.Ap_Paterno,administrativos.Ap_Materno,administrativos.Nombre,cursos.NombreCurso
        FROM `planes`
            LEFT JOIN `administrativos` ON `planes`.`Admin_id` = `administrativos`.`id`
            LEFT JOIN `cursos` ON `planes`.`NombreMateria` = `cursos`.`id` where planes.Anio_id=$Anio_id ORDER BY planes.id DESC");
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if($request->hasFile('Documento')){
            $file = $request->file('Documento');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/planes/',$namefile);
        }
        if($request->hasFile('Documento')){
            $data['Documento']='planes/'.$namefile;
        }
        else{
            $data['Documento']='';
        }
        planes::insert($data);
        return response()->json(["mensaje" => "planes Registrado Correctamente"], 200);

    }

    public function show($id)
    {
        $data = planes::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function updatePlan(Request $request, $id)
    {
        $data = $request->all();
        // $inscrito = planes::findOrFail($id);
        $even = planes::where('id','=',$id)->firstOrFail();

        if ($request->hasFile('Documento'))
        {
            // ELIMINANDO ANTIGUA FOTO
            File::delete(public_path().'/'.$even->Documento);
            //REALIZANDO CARGA DE LA NUEVA FOTO
            $file = $request->file('Documento');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/planes/',$namefile);
        }

        if ($request->hasFile('Documento'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $data['Documento'] = 'planes/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $data['Documento'] = $even->Documento;
        }

        planes::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "planes Modificado Correctamente"], 200);
    }
    public function destroy($id)

    {

        $even =planes::findOrFail($id);
        File::delete(public_path().'/'.$even->Documento);
        $data =  DB::select("delete from planes where id='$id'");
        return response()->json(["mensaje" => "planes Eliminado Correctamente"], 200);

    }
}
