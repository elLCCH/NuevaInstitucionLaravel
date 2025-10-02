<?php

namespace App\Http\Controllers;

use App\Models\Calificaciones;
use App\Models\Estudiantes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\Else_;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class EstudiantesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = DB::select('select id, Ap_Paterno,Ap_Materno , Nombre,Foto , CI, Estado, Categoria from estudiantes order by id desc');
        // return $data;

        // $estudiante = Estudiantes::orderBy('id', 'DESC')->get();
        $data = DB::select("SELECT e.id, e.Ap_Paterno,e.Ap_Materno,e.Nombre,e.Turno,e.CI,e.Matricula,e.Categoria,e.Observacion,e.Estado, e.Curso_Solicitado,e.Edad,e.FechNac,e.Nivel, e.Verificacion
        FROM estudiantes e order by e.FechInsc desc");
        return $data;

    }
    public function EstudiantesAsignacionesInscritos($idGestion)
    {
        //LISTAR A TODOS LOS ESTUDIANTES INSCRITOS Y MOSTRAR CANTIDAD DE MATERIAS DESIGNADAS DETALLADAMENTE
        $dataest=DB::select("select id, CI,Ap_Paterno,Ap_Materno,Nombre,Curso_Solicitado,Turno from estudiantes where Observacion not like '%NO INSCRITO%'");

        // $fila=array();
        $Lista=array();
        foreach ($dataest as $k ) {
            $dataCalif=Calificaciones::where('estudiante_id',$k->id)->where('anio_id',$idGestion)->get();
            $Lista[]=Array("CI" => $k->CI,"Ap_Paterno"=>$k->Ap_Paterno,"Ap_Materno"=> $k->Ap_Materno,"Nombre"=>$k->Nombre,"Curso_Solicitado"=>$k->Curso_Solicitado,"Turno"=>$k->Turno,"Cantidad_Materias"=>count($dataCalif));
        }
        return $Lista;
    }
    public function EstudiantesAsignacionesNoInscritos($idGestion)
    {
        //LISTAR A TODOS LOS ESTUDIANTES INSCRITOS Y MOSTRAR CANTIDAD DE MATERIAS DESIGNADAS DETALLADAMENTE
        $dataest=DB::select("select id, CI,Ap_Paterno,Ap_Materno,Nombre,Curso_Solicitado,Turno from estudiantes where Observacion like '%NO INSCRITO%'");

        // $fila=array();
        $Lista=array();
        foreach ($dataest as $k ) {
            $dataCalif=Calificaciones::where('estudiante_id',$k->id)->where('anio_id',$idGestion)->get();
            $Lista[]=Array("CI" => $k->CI,"Ap_Paterno"=>$k->Ap_Paterno,"Ap_Materno"=> $k->Ap_Materno,"Nombre"=>$k->Nombre,"Curso_Solicitado"=>$k->Curso_Solicitado,"Turno"=>$k->Turno,"Cantidad_Materias"=>count($dataCalif));
        }
        return $Lista;
    }
    public function VerificarCursoParalelo(Request $request)
    {
        $CI= $request->CI;
        $Anio_id = $request->Anio_id;
        $estExiste = DB::select("select * from estudiantes where CI='$CI'");
        $data = DB::select("SELECT `calificaciones`.`id`,calificaciones.estudiante_id,calificaciones.anio_id, estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre,estudiantes.CI, cursos.NivelCurso, cursos.NombreCurso,anios.Anio
        FROM `calificaciones`
            LEFT JOIN `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
            LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
            LEFT JOIN `anios` ON `calificaciones`.`anio_id` = `anios`.`id` WHERE estudiantes.CI = '$CI' and anios.id = $Anio_id");
        return response()->json([
            "estExiste"=> $estExiste,
            "Lista" => $data,
        ], 200);
    }
    public function indexSelection($id)
    {
        $est = Estudiantes::where('id','=', $id)->first();
        return $est;
    }

    public function EstudianteCuadro($id)
    {
        $EstCuadro = DB::table('Estudiantes')
        ->join('Calificaciones', 'Estudiantes.id', '=', 'Calificaciones.estudiante_id')
        ->join('Cursos', 'Calificaciones.curso_id', '=', 'Cursos.id')
        ->where('Calificaciones.id','=',$id)
        ->get();


        return $EstCuadro->NivelCurso;
    }
    //LISTAR ESTADISTICAS DE ASIG ESTS
    public function EstadisticasAsigEstudiantes($idAnio)
    {
        $data = DB::select("SELECT cursos.Malla,cursos.NivelCurso,cursos.NombreCurso,cursos.Sigla,anios.Anio,
        (select COUNT(calif1.estudiante_id) from calificaciones calif1, estudiantes est1 where calif1.estudiante_id=est1.id and calif1.curso_id=cursos.id and calif1.Categoria = 'NUEVO' and est1.Sexo = 'MASCULINO') as Nuevos_M,
        (select COUNT(calif2.estudiante_id) from calificaciones calif2, estudiantes est2 where calif2.estudiante_id=est2.id and calif2.curso_id=cursos.id and calif2.Categoria = 'NUEVO' and est2.Sexo = 'FEMENINO') as Nuevos_F,
        (select COUNT(calif3.estudiante_id) from calificaciones calif3, estudiantes est3 where calif3.estudiante_id=est3.id and calif3.curso_id=cursos.id and calif3.Categoria = 'NUEVO') as Total_Nuevos,
        (select COUNT(calif4.estudiante_id) from calificaciones calif4, estudiantes est4 where calif4.estudiante_id=est4.id and calif4.curso_id=cursos.id and calif4.Categoria = 'ANTIGUO' and est4.Sexo = 'MASCULINO') as Antiguos_M,
        (select COUNT(calif5.estudiante_id) from calificaciones calif5, estudiantes est5 where calif5.estudiante_id=est5.id and calif5.curso_id=cursos.id and calif5.Categoria = 'ANTIGUO' and est5.Sexo = 'FEMENINO') as Antiguos_F,
        (select COUNT(calif6.estudiante_id) from calificaciones calif6, estudiantes est6 where calif6.estudiante_id=est6.id and calif6.curso_id=cursos.id and calif6.Categoria = 'ANTIGUO') as Total_Antiguos,
        (select COUNT(calif7.estudiante_id) from calificaciones calif7, estudiantes est7 where calif7.estudiante_id=est7.id and calif7.curso_id=cursos.id and est7.Sexo = 'MASCULINO') as Total_M,
        (select COUNT(calif8.estudiante_id) from calificaciones calif8, estudiantes est8 where calif8.estudiante_id=est8.id and calif8.curso_id=cursos.id and est8.Sexo = 'FEMENINO') as Total_F,
        (select COUNT(calif8.estudiante_id) from calificaciones calif8, estudiantes est8 where calif8.estudiante_id=est8.id and calif8.curso_id=cursos.id and est8.Sexo = 'MASCULINO' and calif8.Arrastre = 'ARRASTRE') as Arrastre_M,
        (select COUNT(calif8.estudiante_id) from calificaciones calif8, estudiantes est8 where calif8.estudiante_id=est8.id and calif8.curso_id=cursos.id and est8.Sexo = 'FEMENINO' and calif8.Arrastre = 'ARRASTRE') as Arrastre_F,
        (select COUNT(calif8.estudiante_id) from calificaciones calif8, estudiantes est8 where calif8.estudiante_id=est8.id and calif8.curso_id=cursos.id and calif8.Arrastre = 'ARRASTRE') as Total_Arrastres,
        (select COUNT(calif.estudiante_id) from calificaciones calif where calif.curso_id=cursos.id) as Total_Gral
        FROM `cursos`
            LEFT JOIN `anios` ON `cursos`.`Anio_id` = `anios`.`id` WHERE anios.id=$idAnio order by cursos.Malla,cursos.NivelCurso,cursos.Rango");
            return $data;
    }
    #region NEW GESTION
    /*PARA LA NUEVA GESTION*/
    public function DetectarCantidadEstudiantesInscritos(Request $request) //ESTADISTICAS
    {
        $cursos_solicitados = $request->data;
        $anioController = new AnioController(); //ANIO CONTROLLER
        $apisController = new ApisController(); //APIS CONTROLLER
        $ifa = $anioController->DeterminarInstituto();  //recogiendo nombre de q ifa

        // $cursos_solicitados = $apisController->ListarCursosApi(); //recogiendo todos los cursos q estan registrados ANTES USABAMOS
        // $cursos_solicitados =Estudiantes::distinct()->get(['Curso_Solicitado']); //recogiendo todos los cursos q estan registrados
        $instrumentosEspecialidad = $apisController->ListarInstrumentosApi(); //recogiendo los instrumentos de especialidad

        // $cursos_solicitados = collect($cursos_solicitados)->where('Ifa', $ifa)->all(); //filtramos los cursos q pertenecen a un ifa ANTES USABAMOS
        $instrumentosEspecialidad = collect($instrumentosEspecialidad)->where('Ifa', $ifa)->where('Estado','ACTIVO')->all(); //filtramos los instrumentos q pertenecen a un ifa
        // $Lista = array(); //CANTIDADES TOTALES CURSO
        // $ListaInst = array(); //CANTIDADES INST
        // $Lista[] =DB::select("select COUNT(*) AS 'ANTIGUOS NO INSCRITOS' from estudiantes e where e.Observacion LIKE '%NO INSCRITO%'")[0];
        // $Lista[]=DB::select("select COUNT(*) AS 'CANTIDAD INSCRITOS' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'")[0];
        $Lista=new Estudiantes(); //CANTIDADES TOTALES CURSO
        $ListaInst = new Estudiantes(); //CANTIDADES INST
        $Lista['ANTIGUOS NO INSCRITOS'] =DB::select("select COUNT(*) AS 'ANTIGUOS NO INSCRITOS' from estudiantes e where e.Observacion LIKE '%NO INSCRITO%'")[0];
        $Lista['CANTIDAD INSCRITOS']=DB::select("select COUNT(*) AS 'CANTIDAD INSCRITOS' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'")[0];
        $Lista['CANTIDAD NUEVOS INSCRITOS']=DB::select("select COUNT(*) AS 'CANTIDAD NUEVOS INSCRITOS' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%' and Categoria='NUEVO'")[0];
        $Lista['CANTIDAD ANTIGUOS INSCRITOS']=DB::select("select COUNT(*) AS 'CANTIDAD ANTIGUOS INSCRITOS' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%' and Categoria='ANTIGUO'")[0];

        foreach ($cursos_solicitados as $k) {
            $course=$k['Curso_Solicitado'];
            $resCantidadTotal =  DB::select("select COUNT(*) AS '$course' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='$course'");
            //FUNCIONA, ES CANTIDAD DE INSTRUMENTOS POR CURSO...
            foreach ($instrumentosEspecialidad as $i) {
                $inst=$i['InstEspecialidad'];
                $nameSelect = $course.' '.$inst;
                $resCantidadEspecialidadxCursos = DB::select("select COUNT(*) AS '$nameSelect' from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='$course' and (e.Especialidad LIKE '%$inst%')");
                $ListaInst[$nameSelect]=$resCantidadEspecialidadxCursos[0];
                // array_push($ListaInst,$resCantidadEspecialidadxCursos[0]); //ARRAY PUSH
            }
            $Lista[$course]=$resCantidadTotal[0];
            // array_push($Lista,$resCantidadTotal[0]); //ARRAY PUSH
        }
        return response()->json([
            "CantidadTotalCursos"=> $Lista,
            "CantidadInstrumentos" => $ListaInst,
        ], 200);
        return $Lista;
        // $AntiguosNoInscritos =DB::select("select COUNT(*) AS AntiguosNoInscritos from estudiantes e where e.Observacion LIKE '%NO INSCRITO%'");
        // $CantidadInscritos =DB::select("select COUNT(*) AS CantidadInscritos from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'");
        // //CANTIDADES DE ESTUDIANTES POR NIVEL
        // $PrimeroSuperior = DB::select("select COUNT(*) AS PrimeroSuperior from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO SUPERIOR'");
        // $SegundoSuperior = DB::select("select COUNT(*) AS SegundoSuperior from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO SUPERIOR'");
        // $TerceroSuperior = DB::select("select COUNT(*) AS TerceroSuperior from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO SUPERIOR'");
        // $PrimeroIntermedio = DB::select("select COUNT(*) AS PrimeroIntermedio from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INTERMEDIO'");
        // $SegundoIntermedio = DB::select("select COUNT(*) AS SegundoIntermedio from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INTERMEDIO'");
        // $TerceroIntermedio = DB::select("select COUNT(*) AS TerceroIntermedio from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INTERMEDIO'");
        // $PrimeroBasico = DB::select("select COUNT(*) AS PrimeroBasico from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO BASICO'");
        // $SegundoBasico = DB::select("select COUNT(*) AS SegundoBasico from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO BASICO'");
        // $TerceroBasico = DB::select("select COUNT(*) AS TerceroBasico from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO BASICO'");
        // $PrimeroIniciacion = DB::select("select COUNT(*) AS PrimeroIniciacion from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INICIACION'");
        // $SegundoIniciacion = DB::select("select COUNT(*) AS SegundoIniciacion from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INICIACION'");
        // $TerceroIniciacion = DB::select("select COUNT(*) AS TerceroIniciacion from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INICIACION'");


        // //cantidades de estudiantes por nivel INSTRUMENTO
        // $PrimeroSuperiorPiano    = DB::select("select COUNT(*) AS PrimeroSuperiorPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO SUPERIOR' and (e.Especialidad LIKE '%PIANO%')");
        // $PrimeroSuperiorViolin   = DB::select("select COUNT(*) AS PrimeroSuperiorViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO SUPERIOR' and (e.Especialidad LIKE '%VIOLIN%')");
        // $PrimeroSuperiorGuitarra = DB::select("select COUNT(*) AS PrimeroSuperiorGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO SUPERIOR' and (e.Especialidad LIKE '%GUITARRA%')");

        // $SegundoSuperiorPiano    = DB::select("select COUNT(*) AS SegundoSuperiorPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO SUPERIOR' and (e.Especialidad LIKE '%PIANO%')");
        // $SegundoSuperiorViolin   = DB::select("select COUNT(*) AS SegundoSuperiorViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO SUPERIOR' and (e.Especialidad LIKE '%VIOLIN%')");
        // $SegundoSuperiorGuitarra = DB::select("select COUNT(*) AS SegundoSuperiorGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO SUPERIOR' and (e.Especialidad LIKE '%GUITARRA%')");

        // $TerceroSuperiorPiano    = DB::select("select COUNT(*) AS TerceroSuperiorPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO SUPERIOR' and (e.Especialidad LIKE '%PIANO%')");
        // $TerceroSuperiorViolin   = DB::select("select COUNT(*) AS TerceroSuperiorViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO SUPERIOR' and (e.Especialidad LIKE '%VIOLIN%')");
        // $TerceroSuperiorGuitarra = DB::select("select COUNT(*) AS TerceroSuperiorGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO SUPERIOR' and (e.Especialidad LIKE '%GUITARRA%')");

        // $PrimeroIntermedioPiano    = DB::select("select COUNT(*) AS PrimeroIntermedioPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INTERMEDIO' and (e.Especialidad LIKE '%PIANO%')");
        // $PrimeroIntermedioViolin   = DB::select("select COUNT(*) AS PrimeroIntermedioViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INTERMEDIO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $PrimeroIntermedioGuitarra = DB::select("select COUNT(*) AS PrimeroIntermedioGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INTERMEDIO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $SegundoIntermedioPiano    = DB::select("select COUNT(*) AS SegundoIntermedioPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INTERMEDIO' and (e.Especialidad LIKE '%PIANO%')");
        // $SegundoIntermedioViolin   = DB::select("select COUNT(*) AS SegundoIntermedioViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INTERMEDIO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $SegundoIntermedioGuitarra = DB::select("select COUNT(*) AS SegundoIntermedioGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INTERMEDIO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $TerceroIntermedioPiano    = DB::select("select COUNT(*) AS TerceroIntermedioPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INTERMEDIO' and (e.Especialidad LIKE '%PIANO%')");
        // $TerceroIntermedioViolin   = DB::select("select COUNT(*) AS TerceroIntermedioViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INTERMEDIO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $TerceroIntermedioGuitarra = DB::select("select COUNT(*) AS TerceroIntermedioGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INTERMEDIO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $PrimeroBasicoPiano    = DB::select("select COUNT(*) AS PrimeroBasicoPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO BASICO' and (e.Especialidad LIKE '%PIANO%')");
        // $PrimeroBasicoViolin   = DB::select("select COUNT(*) AS PrimeroBasicoViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO BASICO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $PrimeroBasicoGuitarra = DB::select("select COUNT(*) AS PrimeroBasicoGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO BASICO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $SegundoBasicoPiano    = DB::select("select COUNT(*) AS SegundoBasicoPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO BASICO' and (e.Especialidad LIKE '%PIANO%')");
        // $SegundoBasicoViolin   = DB::select("select COUNT(*) AS SegundoBasicoViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO BASICO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $SegundoBasicoGuitarra = DB::select("select COUNT(*) AS SegundoBasicoGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO BASICO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $TerceroBasicoPiano    = DB::select("select COUNT(*) AS TerceroBasicoPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO BASICO' and (e.Especialidad LIKE '%PIANO%')");
        // $TerceroBasicoViolin   = DB::select("select COUNT(*) AS TerceroBasicoViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO BASICO' and (e.Especialidad LIKE '%VIOLIN%')");
        // $TerceroBasicoGuitarra = DB::select("select COUNT(*) AS TerceroBasicoGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO BASICO' and (e.Especialidad LIKE '%GUITARRA%')");

        // $PrimeroIniciacionPiano    = DB::select("select COUNT(*) AS PrimeroIniciacionPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INICIACION' and (e.Especialidad LIKE '%PIANO%')");
        // $PrimeroIniciacionViolin   = DB::select("select COUNT(*) AS PrimeroIniciacionViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INICIACION' and (e.Especialidad LIKE '%VIOLIN%')");
        // $PrimeroIniciacionGuitarra = DB::select("select COUNT(*) AS PrimeroIniciacionGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='PRIMERO INICIACION' and (e.Especialidad LIKE '%GUITARRA%')");

        // $SegundoIniciacionPiano    = DB::select("select COUNT(*) AS SegundoIniciacionPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INICIACION' and (e.Especialidad LIKE '%PIANO%')");
        // $SegundoIniciacionViolin   = DB::select("select COUNT(*) AS SegundoIniciacionViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INICIACION' and (e.Especialidad LIKE '%VIOLIN%')");
        // $SegundoIniciacionGuitarra = DB::select("select COUNT(*) AS SegundoIniciacionGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='SEGUNDO INICIACION' and (e.Especialidad LIKE '%GUITARRA%')");

        // $TerceroIniciacionPiano    = DB::select("select COUNT(*) AS TerceroIniciacionPiano from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INICIACION' and (e.Especialidad LIKE '%PIANO%')");
        // $TerceroIniciacionViolin   = DB::select("select COUNT(*) AS TerceroIniciacionViolin from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INICIACION' and (e.Especialidad LIKE '%VIOLIN%')");
        // $TerceroIniciacionGuitarra = DB::select("select COUNT(*) AS TerceroIniciacionGuitarra from estudiantes e where e.Observacion NOT LIKE '%NO INSCRITO%'	and e.Curso_Solicitado='TERCERO INICIACION' and (e.Especialidad LIKE '%GUITARRA%')");
        // return response()->json([

        //     "CantidadInscritos"=> $CantidadInscritos[0],
        //     "AntiguosNoInscritos" => $AntiguosNoInscritos[0],
        //     "PrimeroSuperior" => $PrimeroSuperior[0],
        //     "SegundoSuperior" => $SegundoSuperior[0],
        //     "TerceroSuperior" => $TerceroSuperior[0],
        //     "PrimeroIntermedio" => $PrimeroIntermedio[0],
        //     "SegundoIntermedio" => $SegundoIntermedio[0],
        //     "TerceroIntermedio" => $TerceroIntermedio[0],
        //     "PrimeroBasico"=> $PrimeroBasico[0],
        //     "SegundoBasico"=> $SegundoBasico[0],
        //     "TerceroBasico"=> $TerceroBasico[0],
        //     "PrimeroIniciacion" => $PrimeroIniciacion[0],
        //     "SegundoIniciacion" => $SegundoIniciacion[0],
        //     "TerceroIniciacion" => $TerceroIniciacion[0],
        //     "PrimeroSuperiorPiano" => $PrimeroSuperiorPiano[0],
        //     "PrimeroSuperiorViolin" => $PrimeroSuperiorViolin[0],
        //     "PrimeroSuperiorGuitarra" => $PrimeroSuperiorGuitarra[0],
        //     "SegundoSuperiorPiano" => $SegundoSuperiorPiano[0],
        //     "SegundoSuperiorViolin" => $SegundoSuperiorViolin[0],
        //     "SegundoSuperiorGuitarra" => $SegundoSuperiorGuitarra[0],
        //     "TerceroSuperiorPiano" => $TerceroSuperiorPiano[0],
        //     "TerceroSuperiorViolin" => $TerceroSuperiorViolin[0],
        //     "TerceroSuperiorGuitarra" => $TerceroSuperiorGuitarra[0],
        //     "PrimeroIntermedioPiano" => $PrimeroIntermedioPiano[0],
        //     "PrimeroIntermedioViolin" => $PrimeroIntermedioViolin[0],
        //     "PrimeroIntermedioGuitarra" => $PrimeroIntermedioGuitarra[0],
        //     "SegundoIntermedioPiano" => $SegundoIntermedioPiano[0],
        //     "SegundoIntermedioViolin" => $SegundoIntermedioViolin[0],
        //     "SegundoIntermedioGuitarra" => $SegundoIntermedioGuitarra[0],
        //     "TerceroIntermedioPiano" => $TerceroIntermedioPiano[0],
        //     "TerceroIntermedioViolin" => $TerceroIntermedioViolin[0],
        //     "TerceroIntermedioGuitarra" => $TerceroIntermedioGuitarra[0],
        //     "PrimeroBasicoPiano"    => $PrimeroBasicoPiano[0],
        //     "PrimeroBasicoViolin" => $PrimeroBasicoViolin[0],
        //     "PrimeroBasicoGuitarra" => $PrimeroBasicoGuitarra[0],
        //     "SegundoBasicoPiano" => $SegundoBasicoPiano[0],
        //     "SegundoBasicoViolin" => $SegundoBasicoViolin[0],
        //     "SegundoBasicoGuitarra" => $SegundoBasicoGuitarra[0],
        //     "TerceroBasicoPiano" => $TerceroBasicoPiano[0],
        //     "TerceroBasicoViolin" => $TerceroBasicoViolin[0],
        //     "TerceroBasicoGuitarra" => $TerceroBasicoGuitarra[0],
        //     "PrimeroIniciacionPiano"   => $PrimeroIniciacionPiano[0],
        //     "PrimeroIniciacionViolin" => $PrimeroIniciacionViolin[0],
        //     "PrimeroIniciacionGuitarra" => $PrimeroIniciacionGuitarra[0],
        //     "SegundoIniciacionPiano" => $SegundoIniciacionPiano[0],
        //     "SegundoIniciacionViolin" => $SegundoIniciacionViolin[0],
        //     "SegundoIniciacionGuitarra" => $SegundoIniciacionGuitarra[0],
        //     "TerceroIniciacionPiano" => $TerceroIniciacionPiano[0],
        //     "TerceroIniciacionViolin" => $TerceroIniciacionViolin[0],
        //     "TerceroIniciacionGuitarra" => $TerceroIniciacionGuitarra[0],

        // ], 200);
    }
    #endregion NEW GESTION
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function ObtenerNombreCompleto($id)
    {
        $estudiante = Estudiantes::where('id','=', $id)->first();
        $NombreCompleto = $estudiante->Ap_Paterno. ' ' .$estudiante->Ap_Materno. ' ' .$estudiante->Nombre;
        return $NombreCompleto;
    }
    public function OrdenarLista(Request $request)
    {
        return $request->Datos;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $requestData = $request->all();
        // if ($request->hasFile('Foto')) {
        //     $requestData['Foto'] = $request->file('Foto')
        //     ->store('uploads', 'public');
        // }

        // Estudiantes::insert($requestData);


        if($request->hasFile('Foto')){
            $file = $request->file('Foto');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/estudiantes/',$namefile);
        }
        if($request->hasFile('Certificado')){
            $fileCertificado = $request->file('Certificado');
            $namefileCertificado = time().$fileCertificado->getClientOriginalName();
            $fileCertificado->move(public_path().'/CertificadosNacDocumentos/',$namefileCertificado);
        }
        if($request->hasFile('DocColUni')){
            $fileDocColUni = $request->file('DocColUni');
            $namefileDocColUni = time().$fileDocColUni->getClientOriginalName();
            $fileDocColUni->move(public_path().'/DocColUniDocumentos/',$namefileDocColUni);
        }
        if($request->hasFile('CIDoc')){
            $fileCIDoc = $request->file('CIDoc');
            $namefileCIDoc = time().$fileCIDoc->getClientOriginalName();
            $fileCIDoc->move(public_path().'/CIDocumentos/',$namefileCIDoc);
        }
        if($request->hasFile('Boleta')){
            $fileBoleta = $request->file('Boleta');
            $namefileBoleta = time().$fileBoleta->getClientOriginalName();
            $fileBoleta->move(public_path().'/BoletaDocumentos/',$namefileBoleta);
        }

        $estudiante = new Estudiantes();
        if($request->hasFile('Foto')){$estudiante->Foto = 'estudiantes/'.$namefile;} else{$estudiante->Foto = '';}
        $estudiante->Ap_Paterno= $request->input('Ap_Paterno');
        $estudiante->Ap_Materno= $request->input('Ap_Materno');
        $estudiante->Nombre= $request->input('Nombre');
        $estudiante->Sexo= $request->input('Sexo');
        $estudiante->FechNac= $request->input('FechNac');
        $estudiante->Edad= $request->input('Edad');
        $estudiante->CI= $request->input('CI');
        $estudiante->Nombre_Padre= $request->input('Nombre_Padre');
        $estudiante->OcupacionP= $request->input('OcupacionP');
        $estudiante->NumCelP= $request->input('NumCelP');
        $estudiante->Nombre_Madre= $request->input('Nombre_Madre');
        $estudiante->OcupacionM= $request->input('OcupacionM');
        $estudiante->NumCelM= $request->input('NumCelM');
        $estudiante->Direccion= $request->input('Direccion');
        $estudiante->Admin_idPC= $request->input('Admin_idPC');
        $estudiante->Celular= $request->input('Celular');
        $estudiante->NColegio= $request->input('NColegio');
        $estudiante->TipoColegio= $request->input('TipoColegio');
        $estudiante->CGrado= $request->input('CGrado');
        $estudiante->CNivel= $request->input('CNivel');
        $estudiante->Especialidad= $request->input('Especialidad');
        $estudiante->Correo= $request->input('Correo');

        $estudiante->Password= Hash::make($request->input('Password')) ;
        $estudiante->Matricula= $request->input('Matricula');
        $estudiante->Observacion= $request->input('Observacion');
        $estudiante->Estado= $request->input('Estado');
        $estudiante->Carrera= $request->input('Carrera');
        $estudiante->Categoria= $request->input('Categoria');
        $estudiante->Turno= $request->input('Turno');
        $estudiante->Correo_Institucional= $request->input('Correo_Institucional');
        $estudiante->Curso_Solicitado= $request->input('Curso_Solicitado');
        $estudiante->Mension= $request->input('Mension'); //new
        $estudiante->Area= $request->input('Area'); //new
        $estudiante->Programa= $request->input('Programa'); //new
        $estudiante->Nivel= $request->input('Nivel'); //new
        $estudiante->Malla= $request->input('Malla'); //new
        $estudiante->Admin_id= $request->input('Admin_id');

        //PARA NUEVOS ATRIBUTOS
        $estudiante->FechInsc= $request->input('FechInsc'); //new
        $estudiante->LastAcciones= $request->input('LastAcciones'); //new
        $estudiante->Verificacion= $request->input('Verificacion'); //new
        $estudiante->Notas= $request->input('Notas'); //new
        $estudiante->Anotaciones= $request->input('Anotaciones'); //new
        $estudiante->UltimoAdmin= $request->input('UltimoAdmin'); //new
        //HASTA ACA NUEVOS ATRIBUTOS

        // $estudiante->created_at= '2022-02-18'; //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
        $estudiante->created_at=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
        $estudiante->updated_at=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE

        if($request->hasFile('Certificado')){$estudiante->Certificado = 'CertificadosNacDocumentos/'.$namefileCertificado;} else{$estudiante->Certificado = '';}
        if($request->hasFile('DocColUni')){$estudiante->DocColUni = 'DocColUniDocumentos/'.$namefileDocColUni;} else{$estudiante->DocColUni = '';}
        if($request->hasFile('CIDoc')){$estudiante->CIDoc = 'CIDocumentos/'.$namefileCIDoc;} else{$estudiante->CIDoc = '';}
        if($request->hasFile('Boleta')){$estudiante->Boleta = 'BoletaDocumentos/'.$namefileBoleta;} else{$estudiante->Boleta = '';}
        $estudiante->save();
        // return 'Estudiante Guardado';
        return $request;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Estudiantes  $estudiantes
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Estudiantes::where('id','=',$id)->firstOrFail();
        return $data;
    }
    public function SeleccionarPorCI(Request $request)
    {
        $CI= $request->CI;
        $data = Estudiantes::where('CI','=',$CI)->firstOrFail();
        return $data;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Estudiantes  $estudiantes
     * @return \Illuminate\Http\Response
     */
    public function edit(Estudiantes $estudiantes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Estudiantes  $estudiantes
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {

        //PARA SABER LAS UBICACIONES COPIE EL CODIGO DEL STORE
        // if($request->hasFile('Foto')){$estudiante->Foto = 'estudiantes/'.$namefile;} else{$estudiante->Foto = '';}
        // if($request->hasFile('Certificado')){$estudiante->Certificado = 'CertificadosNacDocumentos/'.$namefileCertificado;} else{$estudiante->Certificado = '';}
        // if($request->hasFile('DocColUni')){$estudiante->DocColUni = 'DocColUniDocumentos/'.$namefileDocColUni;} else{$estudiante->DocColUni = '';}
        // if($request->hasFile('CIDoc')){$estudiante->CIDoc = 'CIDocumentos/'.$namefileCIDoc;} else{$estudiante->CIDoc = '';}


        $requestData = $request->all();
        if ($request->hasFile('Foto'))
        {
            // ELIMINANDO ANTIGUA FOTO
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->Foto);
            //REALIZANDO CARGA DE LA NUEVA FOTO
            $file = $request->file('Foto');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/estudiantes/',$namefile);
            $requestData['Foto'] = 'estudiantes/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('Certificado'))
        {
            // ELIMINANDO ANTIGUA Certificado
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->Certificado);
            //REALIZANDO CARGA DE LA NUEVA Certificado
            $file = $request->file('Certificado');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/CertificadosNacDocumentos/',$namefile);
            $requestData['Certificado'] = 'CertificadosNacDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('DocColUni'))
        {
            // ELIMINANDO ANTIGUA DocColUni
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->DocColUni);
            //REALIZANDO CARGA DE LA NUEVA DocColUni
            $file = $request->file('DocColUni');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/DocColUniDocumentos/',$namefile);
            $requestData['DocColUni'] = 'DocColUniDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('CIDoc'))
        {
            // ELIMINANDO ANTIGUA CIDoc
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->CIDoc);
            //REALIZANDO CARGA DE LA NUEVA CIDoc
            $file = $request->file('CIDoc');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/CIDocumentos/',$namefile);
            $requestData['CIDoc'] = 'CIDocumentos/'.$namefile;
            // return 'paso';
        }

        if ($request->hasFile('Boleta'))
        {
            // ELIMINANDO ANTIGUA Boleta
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->Boleta);
            //REALIZANDO CARGA DE LA NUEVA Boleta
            $file = $request->file('Boleta');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/BoletaDocumentos/',$namefile);
            $requestData['Boleta'] = 'BoletaDocumentos/'.$namefile;
            // return 'paso';
        }
        Estudiantes::where('id','=',$id)->update($requestData);

        // return 'Datos Estudiante Modificados';
        return $requestData;
    }
    public function AbrirPDF(Request $request)
    {
        return $request->UrlPDF;
    }
    public function actualizar(Request $request, $id)
    {

        $requestData = $request->all();
        $estudiante =Estudiantes::findOrFail($id);
        if ($request->hasFile('Foto'))
        {
            // ELIMINANDO ANTIGUA FOTO

            File::delete(public_path().'/'.$estudiante->Foto);
            //REALIZANDO CARGA DE LA NUEVA FOTO
            $file = $request->file('Foto');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/estudiantes/',$namefile);

            // return 'paso';
        }
        // $requestData['Foto'] = 'estudiantes/'.$namefile;

        if ($request->hasFile('Foto'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $requestData['Foto'] = 'estudiantes/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $requestData['Foto'] = $estudiante->Foto;
        }

        if ($request->hasFile('Certificado'))
        {
            // ELIMINANDO ANTIGUA Certificado
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->Certificado);
            //REALIZANDO CARGA DE LA NUEVA Certificado
            $file = $request->file('Certificado');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/CertificadosNacDocumentos/',$namefile);
            $requestData['Certificado'] = 'CertificadosNacDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('Certificado'))
        {//SI TIENE Certificado ENTONCES EN Certificado poner sus cosas
            $requestData['Certificado'] = 'CertificadosNacDocumentos/'.$namefile;
        }
        else
        {//SINO TIENE Certificado Y AUN ASI QUIERE ACTUALIZAR
            $requestData['Certificado'] = $estudiante->Certificado;
        }


        if ($request->hasFile('DocColUni'))
        {
            // ELIMINANDO ANTIGUA DocColUni
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->DocColUni);
            //REALIZANDO CARGA DE LA NUEVA DocColUni
            $file = $request->file('DocColUni');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/DocColUniDocumentos/',$namefile);
            $requestData['DocColUni'] = 'DocColUniDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('DocColUni'))
        {//SI TIENE DocColUniDocumentos ENTONCES EN DocColUniDocumentos poner sus cosas
            $requestData['DocColUni'] = 'DocColUniDocumentos/'.$namefile;
        }
        else
        {//SINO TIENE DocColUniDocumentos Y AUN ASI QUIERE ACTUALIZAR
            $requestData['DocColUni'] = $estudiante->DocColUni;
        }



        if ($request->hasFile('CIDoc'))
        {
            // ELIMINANDO ANTIGUA CIDoc
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->CIDoc);
            //REALIZANDO CARGA DE LA NUEVA CIDoc
            $file = $request->file('CIDoc');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/CIDocumentos/',$namefile);
            $requestData['CIDoc'] = 'CIDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('CIDoc'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $requestData['CIDoc'] = 'CIDocumentos/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $requestData['CIDoc'] = $estudiante->CIDoc;
        }

        if ($request->hasFile('Boleta'))
        {
            // ELIMINANDO ANTIGUA Boleta
            $estudiante =Estudiantes::findOrFail($id);
            File::delete(public_path().'/'.$estudiante->Boleta);
            //REALIZANDO CARGA DE LA NUEVA Boleta
            $file = $request->file('Boleta');
            $namefile = time().$file->getClientOriginalName();
            $file->move(public_path().'/BoletaDocumentos/',$namefile);
            $requestData['Boleta'] = 'BoletaDocumentos/'.$namefile;
            // return 'paso';
        }
        if ($request->hasFile('Boleta'))
        {//SI TIENE FOTO ENTONCES EN Foto poner sus cosas
            $requestData['Boleta'] = 'BoletaDocumentos/'.$namefile;
        }
        else
        {//SINO TIENE FOTO Y AUN ASI QUIERE ACTUALIZAR
            $requestData['Boleta'] = $estudiante->Boleta;
        }
        //SI NO ES TIPO HASH CREAR NUEVO HASH
        if (Hash::needsRehash($request->Password))
        {
            $requestData['Password'] = Hash::make($request->Password);
        }
        //SI NO EXISTE O NO SE ENVIO EL PARAMETRO Password hacer
        if ($request->has('Password')) {
            //SI EXISTE
        } else {
            //NO EXISTE
            $requestData['Password'] = $estudiante->Password;
        }

        if ($request->Admin_id == 'null') {
            $requestData['Admin_id']=null;
        }
        if ($request->Observacion != 'NO INSCRITO') {
            $requestData['updated_at']= Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE

        }else{

            // $fechahoy=new Date();
            $requestData['created_at']=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
            $requestData['updated_at']=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
            //USAREMOS EL CREATED PARA EL CUADRO DE ESTUDIANTES
        }
        Estudiantes::where('id','=',$id)->update($requestData);
        return $request;
        // return $request;
    }
    public function EstudianteUpdatePlano(Request $request, $id)
    {

        $requestData = $request->all();
        $estudiante =Estudiantes::findOrFail($id);
        $requestData['Foto'] = $estudiante->Foto;
        $requestData['Certificado'] = $request->Certificado;
        $requestData['DocColUni'] = $request->DocColUni;
        $requestData['CIDoc'] = $request->CIDoc;
        $requestData['Boleta'] = $request->Boleta;
        //SI NO ES TIPO HASH CREAR NUEVO HASH
        if (Hash::needsRehash($request->Password))
        {
            $requestData['Password'] = Hash::make($request->Password);
        }
        //SI NO EXISTE O NO SE ENVIO EL PARAMETRO Password hacer
        if ($request->has('Password')) {
            //SI EXISTE
        } else {
            //NO EXISTE
            $requestData['Password'] = $estudiante->Password;
        }

        if ($request->Admin_id == 'null') {
            $requestData['Admin_id']=null;
        }
        if ($request->Observacion != 'NO INSCRITO') {
            $requestData['updated_at']= Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE

        }else{

            // $fechahoy=new Date();
            $requestData['created_at']=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
            $requestData['updated_at']=  Carbon::now(); //ESTO ES LO QUE HACE PARA QUE SEA LA FECHA LIMITE
            //USAREMOS EL CREATED PARA EL CUADRO DE ESTUDIANTES
        }
        Estudiantes::where('id','=',$id)->update($requestData);
        return $requestData;
        // return $request;
    }
    public function ReiniciarContrasenias(Request $request)
    {
        //RESETEAR CONTRASEÑAS A TODOS LOS ESTUDIANTES
        $passNew=$request->input('Password');

        $PasswordHash = Hash::make($passNew);
        DB::select("update estudiantes set Password='$PasswordHash'");
        return 'SE CAMBIO LA CONTRASEÑA';

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Estudiantes  $estudiantes
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // ELIMINANDO ANTIGUA FOTO
        $estudiante =Estudiantes::findOrFail($id);
        if(File::delete(public_path().'/'.$estudiante->Foto) and File::delete(public_path().'/'.$estudiante->Certificado) and File::delete(public_path().'/'.$estudiante->DocColUni) and File::delete(public_path().'/'.$estudiante->CIDoc) and File::delete(public_path().'/'.$estudiante->Boleta ))
        {
            Estudiantes::destroy($id);
            return 'eliminado';
        }
        else {
            Estudiantes::destroy($id);
            return 'no fue eliminado';
        }
        return 'gg';






        // $file = $estudiante->file('Foto');
        // $namefile = time().$file->getClientOriginalName();
        // if ($estudiante->hasFile('Foto'))
        // {
        //     $file->delete(public_path().'/estudiantes/',$namefile);
        // }


        // Estudiantes::destroy($id);
        // return 'Estudiante Eliminado';

    }
    public function autentificar(Request $request)
    {


        $CI = $request->input('CI');
        $pass = $request->input('Password');

        // Solo los números del CI ingresado
        $CI_numeric = preg_replace('/[^0-9]/', '', $CI);

        // Comparar con CI de la BD convertido a solo números
        $est = Estudiantes::whereRaw("REGEXP_REPLACE(CI, '[^0-9]', '') = ?", [$CI_numeric])->first();

        if ($est && Hash::check($pass, $est->Password)) {
            return $est;
        } else {
            return 'NOLOG';
        }
    }
    public function EliminarInactivos(Request $request)
    {



        //SELECCIONANDO LA LISTA DE TODOS LOS INACTIVOS
        $estudiante = Estudiantes::where('Estado','=','INACTIVO')->where('Categoria', '!=', 'POSTULANTE')->get();
        // RECORRIENDO TODOS LOS DATOS SELECCIONADOS
        foreach ($estudiante as $a)
        {
            // return $a->id;
            // ELIMINANDO ANTIGUA FOTO
            if(File::delete(public_path().'/'.$a->Foto) and File::delete(public_path().'/'.$a->Certificado) and File::delete(public_path().'/'.$a->DocColUni) and File::delete(public_path().'/'.$a->CIDoc) and File::delete(public_path().'/'.$a->Boleta ))
            {
                Estudiantes::destroy($a->id);
            }
            else
            {
                Estudiantes::destroy($a->id);
            }

        }

        return 'SE ELIMINARON A TODOS LOS INACTIVOS';



    }
}
