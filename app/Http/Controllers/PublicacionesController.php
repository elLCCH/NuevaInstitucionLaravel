<?php

namespace App\Http\Controllers;

use App\Models\Publicaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PublicacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $publicacion = Publicaciones::all();
        // return $publicacion;
        $tipo = $request->query('tipo');
        $publicacion = Publicaciones::whereRaw('tipo=?',$tipo)->orderBy('Fecha','desc')->get();
        // $publicacion->orderBy('Fecha','desc')->get();
        return $publicacion;
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
        if($request->hasFile('File')){
            $file = $request->file('File');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/publicaciones/',$namefile);
        }
        $publicacion = new Publicaciones();
        $publicacion->Titulo= $request->input('Titulo');
        $publicacion->Descripcion= $request->input('Descripcion');
        $publicacion->Fecha= $request->input('Fecha');
        if($request->hasFile('File')){$publicacion->File = 'publicaciones/'.$namefile;}
        else{$publicacion->File = '';}
        $publicacion->Tipo= $request->input('Tipo');
        $publicacion->Enlace= $request->input('Enlace');
        $publicacion->Admin_id= $request->input('Admin_id');
        $publicacion->save();
        return $publicacion;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Publicaciones  $publicaciones
     * @return \Illuminate\Http\Response
     */
    public function show(Publicaciones $publicaciones)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Publicaciones  $publicaciones
     * @return \Illuminate\Http\Response
     */
    public function edit(Publicaciones $publicaciones)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publicaciones  $publicaciones
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Publicaciones $publicaciones)
    {
        //
    }
    public function actualizar(Request $request, $id)
    {
        
        $requestData = $request->all();
        $publicacion =Publicaciones::findOrFail($id);
        if ($request->hasFile('File')) 
        {
            // ELIMINANDO ANTIGUA File
            
            File::delete(public_path().'/'.$publicacion->File);
            //REALIZANDO CARGA DE LA NUEVA File
            $file = $request->file('File');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/publicaciones/',$namefile);
            
            // return 'paso';
        }
        // $requestData['File'] = 'Publicaciones/'.$namefile;
        
        if ($request->hasFile('File')) 
        {//SI TIENE File ENTONCES EN File poner sus cosas
            $requestData['File'] = 'Publicaciones/'.$namefile;
        }
        else
        {//SINO TIENE File Y AUN ASI QUIERE ACTUALIZAR
            $requestData['File'] = $publicacion->File;
        }
        Publicaciones::where('id','=',$id)->update($requestData);
        return 'Datos de Publicacion Modificado';
        // return $request;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Publicaciones  $publicaciones
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         // ELIMINANDO ANTIGUA File
         $publicacion =Publicaciones::findOrFail($id);
         if(File::delete(public_path().'/'.$publicacion->File))
         {
             Publicaciones::destroy($id);    
             return 'eliminado';
         }
         else {
            Publicaciones::destroy($id);  
             return 'eliminado';
         }
         return 'gg';
    }
}
