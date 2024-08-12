<?php

namespace App\Http\Controllers;

use App\Models\materiales;
use App\Models\archivos;
use App\Models\materias_materiales;
use App\Models\Curso;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MaterialesController extends Controller
{
    public function index()
    {
       // Obtener todos los materiales
       $materiales = Materiales::all();

       // Crear una colección vacía para almacenar los materiales con sus archivos
       $materialesConArchivosVisibilidad = collect();
    //    $materialesVisibilidad = collect();

       // Iterar sobre cada material para obtener sus archivos relacionados
       foreach ($materiales as $material) {
           // Obtener los archivos relacionados con el material actual
           $archivos = Archivos::where('id_material', $material->id)->get();
           $visibility = materias_materiales::where('id_material', $material->id)->get();

           $DataVisibilidad = Array();
           foreach ($visibility as $v) {
            // $filaCurso = Curso::where('id','=',$v->id_curso)->firstOrFail();
            $filaCurso = DB::select("select mm.id_curso,mm.id_material,c.NombreCurso,c.NivelCurso,c.Malla,c.Anio_id from cursos c,materias_materiales mm where c.id = mm.id_curso and c.id=$v->id_curso");
            // $filaCurso['id_material']=$material->id;
            $DataVisibilidad[] = $filaCurso[0];
           }
           // Agregar los archivos al material y agregar el material a la colección
           $material->archivos = $archivos;
           $material->visibilidad = $DataVisibilidad;

           $materialesConArchivosVisibilidad->push($material);
       }

       // Devolver la colección de materiales con archivos relacionados
       return response()->json($materialesConArchivosVisibilidad, 200);
    }
    public function CargarMaterialesCurso($idCurso)
    {
       // Obtener Info de los materiales
    //    $cursoId = 451; // Supongamos que estás buscando materiales para el curso con id 3
       $cursoId = $idCurso;

        $materiales = DB::table('materiales')
            ->join('materias_materiales', 'materiales.id', '=', 'materias_materiales.id_material')
            ->where('materias_materiales.id_curso', $cursoId)
            ->select('materiales.*') // Seleccionas todas las columnas de materiales
            ->distinct() // Para evitar duplicados si un material está en más de un curso
            ->get();

       // Crear una colección vacía para almacenar los materiales con sus archivos
       $materialesConArchivosVisibilidad = collect();
    //    $materialesVisibilidad = collect();

       // Iterar sobre cada material para obtener sus archivos relacionados
       foreach ($materiales as $material) {
           // Obtener los archivos relacionados con el material actual
           $archivos = Archivos::where('id_material', $material->id)->get();
           $visibility = materias_materiales::where('id_material', $material->id)->get();

           $DataVisibilidad = Array();
           foreach ($visibility as $v) {
            // $filaCurso = Curso::where('id','=',$v->id_curso)->firstOrFail();
            $filaCurso = DB::select("select mm.id_curso,mm.id_material,c.NombreCurso,c.NivelCurso,c.Malla,c.Anio_id from cursos c,materias_materiales mm where c.id = mm.id_curso and c.id=$v->id_curso");
            // $filaCurso['id_material']=$material->id;
            $DataVisibilidad[] = $filaCurso[0];
           }
           // Agregar los archivos al material y agregar el material a la colección
           $material->archivos = $archivos;
           $material->visibilidad = $DataVisibilidad;

           $materialesConArchivosVisibilidad->push($material);
       }

       // Devolver la colección de materiales con archivos relacionados
       return response()->json($materialesConArchivosVisibilidad, 200);
    }
    public function ListaAgrupacionForVisibility(Request $request, $id)
    {

        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');
        // $tipo = $request->query('tipo');
        // $curso = Curso::whereRaw('NivelCurso=?',$tipo)->orderBy('NombreCurso','desc')->get();
        // return $curso;

        // $adminID = $request->input('admin_id');
        $materialID = $id;
        // $curso = Curso::query()->orderBy('NivelCurso', 'ASC')->get(); //ACA SELECCIONA TODOS LOS CURSOS DE LA TABLA PARA LAS MANIOBRAS

        //PERO YANO LO NECESITAMOS YA Q TENEMOS QUE HACERLO POR GESTION
        $curso =Curso::where('Anio_id',$Anio_id)->where('Malla',$Malla)->orderBy('NivelCurso')->orderBy('Rango')->get();
        // return $curso;

        try {
            foreach ($curso as $C) {
                // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                $NewCurso = Curso::where('id','=',$C->id)->first(); //SELECCIONANDO LOS CURSOS CARGADOS O TODOS LOS Q PREFERIMOS
                $verificacion = materias_materiales::where('id_material','=', $materialID)->where('id_curso','=',$C->id)->first(); //HACEMOS LA ENCONTRACION DE DATOS
                if ($verificacion != "" || $verificacion !=null) {
                    //ESTE MATERIAL ES VISIBLE PARA ESTE CURSO ENTONCES ACTIVADO
                    $NewCurso['Existencia'] = 'ACTIVADO';
                } else {
                    //ESTE MATERIAL NO ES VISIBLE PARA ESTE CURSO, X LO TANTO VERIFICAR SI OTROS DOCENTES SI LO TIENEN
                    $NewCurso['Existencia'] = 'INACTIVO';
                }
                $Lista[] = $NewCurso;
            }

            return $Lista;
        } catch (\Throwable $e) {
            // return $request;
            report($e);
            return false;
        }

    }
    public function EliminarVisibilityMaterial(Request $request)
    {
        $materialID = $request->input('id_material');
        $cursoID = $request->input('id_curso');

        $verificacion = materias_materiales::where('id_material','=', $materialID)->where('id_curso','=',$cursoID)->first();
        materias_materiales::destroy($verificacion->id);
        return 'SE ELIMINO DEL EL MATERIAL DEL CURSO';

    }

    public function store(Request $request)
    {
        $data = $request->all();
        materiales::insert($data);

        $ultimoMaterial = materiales::latest('id')->first();
        return response()->json(["mensaje" => "materiales Registrado Correctamente","data" => $request,"ultimoId"=> $ultimoMaterial], 200);
    }

    public function show($id)
    {
        $data = materiales::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    public function updatemateriales(Request $request, $id)
    {
        $data = $request->all();
        materiales::where('id','=',$id)->update($data);
        return response()->json(["mensaje" => "materiales Modificado Correctamente"], 200);
    }
    public function destroy($id)

    {
        $data =  DB::select("delete from materiales where id='$id'");
        return response()->json(["mensaje" => "materiales Eliminado Correctamente"], 200);
    }
}
