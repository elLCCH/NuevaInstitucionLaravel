<?php

namespace App\Http\Controllers;

use App\Models\Calificaciones;
use App\Models\Curso;
use App\Models\Estudiantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use uns;

class CalificacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $Curso = Calificaciones::all();

        return $Curso;
    }
    public function ListarXCursoCalif($id)
    {
        //
        $Curso = Calificaciones::where('curso_id','=',$id)->get();

        return $Curso;
    }
    public function ObtenerFechaRetiro(Request $request)
    {   //OBTIENE INFORMACION DE FECHAS DE RETIRADOS/PROMOVIDOS/OBSERVACIONES EN GENERAL
        $data1 = $request->input('ListaCentralizadorFinal');
        $data2 = $request->input('datosNecesarios');
        //DATOS DE LA ListaCentralizadorFinal
        $RequestData = $data1; // Accede directamente a $data1

        // DATOS NECESARIOS
        $reglaInstancia = $data2['regla']; // REGLA PARA SOPORTAR 3,2,1,0 MATERIAS PARA ABROBAR O REPROBAR
        $regimen = $data2['regimen']; // SABER SI ES ANUALIZADO O SEMESTRALIZADO
        $anioSelect = $data2['anio'];
        $lvlCurso = $data2['lvlCurso'];
        $ifa = $data2['ifa'];

        $cursoMayoria =''; //SABER A Q CURSO PERTENECE VIENDO LA MAYORIA DE SUS CURSOS
        //OBTENER FECHAS API SEGUN IFA Q PERTENECEN
        $fecha1erBimRetiro=' ';
        $fecha2doBimRetiro=' ';
        $fecha3erBimRetiro=' ';
        $fecha4toBimRetiro=' ';
        $fechaSemestralizadoRetiro=' ';
        $fechaDefaultRetiro=' ';

        // Instanciar el controlador y llamar al método
        $apiController = new ApisController();
        $dataFechasRetiro = $apiController->FechasRetiroApi(); // Procesa los datos como necesites

        switch ($ifa) {
            case 'MARIA LUISA LUZIO':
                $filtroInfoFechas = array_filter($dataFechasRetiro, function($elemento) {
                    return $elemento['Ifa'] === 'MARIA LUISA LUZIO';
                });
                foreach ($filtroInfoFechas as $elemento) {
                    switch ($elemento['Bimestre']) {
                        case 'PRIMER':
                            $fecha1erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEGUNDO':
                            $fecha2doBimRetiro = $elemento['Fecha'];
                            break;
                        case 'TERCER':
                            $fecha3erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'CUARTO':
                            $fecha4toBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEMESTRALIZADO':
                            $fechaSemestralizadoRetiro = $elemento['Fecha'];
                            break;
                        default:
                            $fechaDefaultRetiro = $elemento['Fecha'];
                            break;
                        }
                    }
                break;
            case 'DEL FOLKLORE ORURO':
                $filtroInfoFechas = array_filter($dataFechasRetiro, function($elemento) {
                    return $elemento['Ifa'] === 'DEL FOLKLORE ORURO';
                });
                foreach ($filtroInfoFechas as $elemento) {
                    switch ($elemento['Bimestre']) {
                        case 'PRIMER':
                            $fecha1erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEGUNDO':
                            $fecha2doBimRetiro = $elemento['Fecha'];
                            break;
                        case 'TERCER':
                            $fecha3erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'CUARTO':
                            $fecha4toBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEMESTRALIZADO':
                            $fechaSemestralizadoRetiro = $elemento['Fecha'];
                            break;
                        default:
                            $fechaDefaultRetiro = $elemento['Fecha'];
                            break;
                        }
                    }
                break;
            case 'BELLAS ARTES ORURO':
                $filtroInfoFechas = array_filter($dataFechasRetiro, function($elemento) {
                    return $elemento['Ifa'] === 'BELLAS ARTES ORURO';
                });
                foreach ($filtroInfoFechas as $elemento) {
                    switch ($elemento['Bimestre']) {
                        case 'PRIMER':
                            $fecha1erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEGUNDO':
                            $fecha2doBimRetiro = $elemento['Fecha'];
                            break;
                        case 'TERCER':
                            $fecha3erBimRetiro = $elemento['Fecha'];
                            break;
                        case 'CUARTO':
                            $fecha4toBimRetiro = $elemento['Fecha'];
                            break;
                        case 'SEMESTRALIZADO':
                            $fechaSemestralizadoRetiro = $elemento['Fecha'];
                            break;
                        default:
                            $fechaDefaultRetiro = $elemento['Fecha'];
                            break;
                        }
                    }
                break;
            default:
                # code...
                break;
        }

        //PARA LAS ESTAIDSTICAS EMPEZAMOS A REALIZAR LAS ACCIONES
        $resFechas = array(); //ACÁ LOS RESULTADOS DE LAS FECHAS DE RETIRO DE TODOS LOS ESTUDIANTES QUE CORRESPONDEN
        $resPromovidos = array(); //ACÁ LOS RESULTADOS DE LOS PROMOVIDOS DE TODOS LOS ESTUDIANTES QUE CORRESPONDEN
        $resObservaciones = array(); //ACÁ LOS RESULTADOS DE LAS OBSERVACIONES DE TODOS Y CADA UNO DE LOS ESTUDIANTES

        $cMaterias=array(); //PARA SABER LA CANTIDAD DE MATERIAS
        $cAprobados=array();
        $cReprobados=array();
        $cRetirados=array();
        $cCalifNull=array(); //QUE NO TIENE PROMEDIO

        foreach ($RequestData as $c) {
            $idEst= $c['id'];  //PARA INTERACTUAR CON SU id HACER ASI

            //OBTENER TODAS LAS CALIFICACIONES+NOMBRE DEL DOCENTE+NOMBRE DEL CURSO+MAS CALIF BIMESTRALES +NIVEL CURSO //OSEA TAMBIEN SI ARRASTRA MATERIAS DE ANTERIORES
            $datasql = DB::select("SELECT administrativos.id as Admin_id,administrativos.Foto,administrativos.Ap_Paterno,
            administrativos.Ap_Materno,administrativos.Nombre,calificaciones.PruebaRecuperacion,calificaciones.Primero,calificaciones.Segundo,
            calificaciones.Tercero,calificaciones.Cuarto,calificaciones.PromEvT,calificaciones.PromEvP,
            calificaciones.Primero,calificaciones.Segundo,calificaciones.Tercero,calificaciones.Cuarto,calificaciones.Promedio,
            calificaciones.Teorica1,calificaciones.Teorica2,calificaciones.Teorica3,calificaciones.Teorica4,
            calificaciones.Practica1,calificaciones.Practica2,calificaciones.Practica3,calificaciones.Practica4,calificaciones.anio_id,
            calificaciones.curso_id as Curso_id, calificaciones.estudiante_id,cursos.NombreCurso, cursos.NivelCurso,cursos.Tipo,
            cursos.Sigla,cursos.BiTriEstado FROM calificaciones LEFT JOIN estudiantes ON calificaciones.estudiante_id = estudiantes.id LEFT JOIN
            cursos ON calificaciones.curso_id = cursos.id  LEFT JOIN administrativos__cursos ON cursos.id = administrativos__cursos.Curso_id LEFT JOIN
             administrativos ON administrativos__cursos.Admin_id= administrativos.id where calificaciones.anio_id=$anioSelect and estudiante_id = $idEst");

            //CALCULAR A QUE NIVEL DE CURSO PERTENECE SEGUN LA CANTIDAD DE NIVEL_CURSO MAS FRECUENTE
            $nivelCursoCount = [];
            foreach ($datasql as $row) {
                $nivelCurso = $row->NivelCurso;
                if (!isset($nivelCursoCount[$nivelCurso])) {
                    $nivelCursoCount[$nivelCurso] = 0;
                } $nivelCursoCount[$nivelCurso]++;
            }
            $maxNivelCurso = '';
            $maxCount = 0;
            foreach ($nivelCursoCount as $nivelCurso => $count) {
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $maxNivelCurso = $nivelCurso;
                }
            }
            $cursoMayoria =$maxNivelCurso; //SABER A Q CURSO PERTENECE VIENDO LA MAYORIA DE SUS CURSOS


            //-----------------EMPEZANDO CON LOS CALCULOS DE TODO------------------
            //HACER EL CALCULO //$resFechas es el resultado de las fechas
            //ES ANUALIZADO
            $forPrimero=0;
            $forSegundo=0;
            $forTercero=0;
            $forCuarto=0;

            foreach ($datasql as $d) {
                if ($d->Promedio==0) {
                    if (($d->Teorica2+$d->Practica2)==0) {
                        $forPrimero++;
                    }
                    if (($d->Teorica2+$d->Practica2)==0) {
                        $forSegundo++;
                    }
                    if(($d->Teorica3+$d->Practica3)==0){
                        $forTercero++;
                    }
                    if(($d->Teorica4+$d->Practica4)==0){
                        $forCuarto++;
                    }
                }
            }

            // Determinar la mayor PARA SABER DESDE CUANDO SE RETIRO
            $maxValue = max($forPrimero,$forSegundo, $forTercero, $forCuarto);
            $fecha = ' ';

            if ($regimen=='ANUALIZADO'){ //SI ES ANUALIZADO LAS FECHAS SON POR BIMESTRES
                if ($maxValue == $forPrimero) {
                    $fecha = $fecha1erBimRetiro;
                } elseif ($maxValue == $forSegundo) {
                    $fecha = $fecha2doBimRetiro;
                } elseif ($maxValue == $forTercero) {
                    $fecha = $fecha3erBimRetiro;
                } elseif ($maxValue == $forCuarto) {
                    $fecha = $fecha4toBimRetiro;
                }
            }else{//NO ES ANUALIZADO, ENTONCES ES OTRO O SEMESTRALIZADO
                $fecha = $fechaSemestralizadoRetiro;
            }


            //FECHA SELECCIONADA DE RETIRO FINALIZADO
            $contadorMaterias=0; //PARA SABER LA CANTIDAD DE MATERIAS
            $contadorAprobados=0;
            $contadorReprobados=0;
            $contadorRetirados=0;
            $contadorCalifNull=0; //QUE NO TIENE PROMEDIO
            // array_push($resFechas,(string)$fecha);


            //INICIANDO  PRUEBA CON SOLO LOS PARA DETERMINAR EL PROMOVIDO Y ESTADO DEFINITIVO

            foreach ($datasql as $a) { //RECORRER MATERIA POR MATERIA
                $contadorMaterias++;
                if ($a->PruebaRecuperacion==null) { //VERIFICAR SI HAY CALIFICACION DE 2da INSTANCIA
                    //NO HAY 2da INSTANCIA / SI NO HAY ENTONCES INTERACTUAR CON EL Promedio
                    if ($a->Promedio > 60) {
                        //APROBÓ
                        $contadorAprobados++;
                    } else if ($a->Promedio == 0){
                        //RETIRADO
                        $contadorRetirados++;
                    } else if ($a->Promedio < 61){
                        //REPROBÓ
                        $contadorReprobados++;
                    } else{
                        //NO HAY CALIFICACION
                        $contadorCalifNull++;
                    }



                }else{
                    //SI TIENE 2da INSTANCIA / SI HAY, ENTONCES INTERACTUAR CON EL PruebaRecuperacion

                    if ($a->PruebaRecuperacion > 60) {
                        //APROBÓ
                        $contadorAprobados++;
                    } else if ($a->PruebaRecuperacion == 0){
                        //RETIRADO
                        $contadorRetirados++;
                    } else if ($a->PruebaRecuperacion < 61){
                        //REPROBÓ
                        $contadorReprobados++;
                    } else{
                        //NO HAY CALIFICACION
                        $contadorCalifNull++;
                    }

                }
            }

            //YA TENEMOS LISTOS LOS CONTADORES DE MATERIAS Y LOS CONTADORES DE APROBS REPROBADOS Y RETIRADOS DE MATERIAS DEL ESTUDIANTE
            if ($contadorRetirados==$contadorMaterias) {
                //EL ESTUDIANTE SE RETIRÓ EN TODAS LAS MATERIAS, ENTONCES ES RETIRADO
                $resPromovidos[] = 'NO';
                $resObservaciones[] = 'RETIRADO';
                $resFechas[] = (string)$fecha;
            }else if($contadorAprobados == $contadorMaterias){
                //EL ESTUDIANTE APROBÓ TODAS LAS MATERIAS, ENTONCES ES APROBADO
                if(str_contains($lvlCurso,'MEDIO')){//SI lvlCurso SE SELECCIONO LVL TECNICO MEDIO HACER
                    //NORMAL SIGUE SIENDO TECNICO MEDIO EL lvlCurso
                    if (str_contains($cursoMayoria, 'SEGUNDO')) { //HACE REFERENCIA A SEGUNDO O A SEGUNDO AÑO
                        $resPromovidos[] = 'NO';
                        $resObservaciones[] = 'APROBÓ';
                        $resFechas[] = ' ';
                    }else{
                        $resPromovidos[] = 'SI';
                        $resObservaciones[] = 'APROBÓ';
                        $resFechas[] = ' ';
                    }
                }else{
                    //NORMAL SIGUE SIENDO TECNICO SUPERIOR EL lvlCurso
                    if (str_contains($cursoMayoria, 'TERCER')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                        $resPromovidos[] = 'NO';
                        $resObservaciones[] = 'APROBÓ';
                        $resFechas[] = ' ';
                    }else{
                        $resPromovidos[] = 'SI';
                        $resObservaciones[] = 'APROBÓ';
                        $resFechas[] = ' ';
                    }
                }
            }else{
                //ANALISIS RESPECTO A LAS REGLAS SELECCIONADAS
                switch ($reglaInstancia) {
                    case 'TECNICO SUPERIOR': //SELECCIONO REGLA DE HASTA 3 ARRASTRES
                        if (($contadorReprobados+$contadorRetirados)>3) {
                            //EL ESTUDIANTE REPROBÓ MAS DE 3 MATERIAS, ENTONCES REPRUEBA TODO
                            $resPromovidos[] = 'NO';
                            $resObservaciones[] = 'REPROBADO';
                            $resFechas[] = ' ';
                        }else{
                        //APROBÓ CON ARRASTRE, ESTA DENTRO DEL RANGO y ES PROMOVIDO
                            //HACIENDO PRUEBAS DE SI ES EL ULTIMO CURSO
                            if ($regimen=='ANUALIZADO') { //SI ES ANUALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO

                                if(str_contains($lvlCurso,'MEDIO')){//SI lvlCurso SE SELECCIONO LVL TECNICO MEDIO HACER
                                    //NORMAL SIGUE SIENDO TECNICO MEDIO EL lvlCurso
                                    if (str_contains($cursoMayoria, 'SEGUNDO')) { //HACE REFERENCIA A SEGUNDO O A SEGUNDO AÑO
                                        $resPromovidos[] = 'NO';
                                        $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                        $resFechas[] = ' ';
                                    }else{
                                        $resPromovidos[] = 'SI';
                                        $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                        $resFechas[] = ' ';
                                    }
                                }else{
                                    //NORMAL SIGUE SIENDO TECNICO SUPERIOR EL lvlCurso
                                    if (str_contains($cursoMayoria, 'TERCER')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                        $resPromovidos[] = 'NO';
                                        $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                        $resFechas[] = ' ';
                                    }else{
                                        $resPromovidos[] = 'SI';
                                        $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                        $resFechas[] = ' ';
                                    }
                                }


                            }else{
                                //ES SEMESTRALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO
                                if (str_contains($cursoMayoria, 'SEXTO')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                    $resPromovidos[] = 'NO';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }else{
                                    $resPromovidos[] = 'SI';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }
                            }
                        }
                        break;
                    case 'TECNICO MEDIO': //SELECCIONO REGLA DE HASTA 2 ARRASTRES
                        if (($contadorReprobados+$contadorRetirados)>2) {
                            //EL ESTUDIANTE REPROBÓ MAS DE 2 MATERIAS, ENTONCES REPRUEBA TODO
                            $resPromovidos[] = 'NO';
                            $resObservaciones[] = 'REPROBADO';
                            $resFechas[] = ' ';
                        }else{
                        //APROBÓ CON ARRASTRE, ESTA DENTRO DEL RANGO y ES PROMOVIDO
                            //HACIENDO PRUEBAS DE SI ES EL ULTIMO CURSO
                            if ($regimen=='ANUALIZADO') { //SI ES ANUALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO
                                if (str_contains($cursoMayoria, 'SEGUNDO')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                    $resPromovidos[] = 'NO';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }else{
                                    $resPromovidos[] = 'SI';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }
                            }else{
                                //ES SEMESTRALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO
                                if (str_contains($cursoMayoria, 'SEXTO')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                    $resPromovidos[] = 'NO';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }else{
                                    $resPromovidos[] = 'SI';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }
                            }
                        }
                        break;
                    case 'CAPACITACION': //PARA CAPACITACION //SELECCIONO REGLA DE HASTA 0 ARRASTRES
                        if (($contadorReprobados+$contadorRetirados)>0) {
                            //EL ESTUDIANTE REPROBÓ MAS DE 0 MATERIAS, ENTONCES REPRUEBA TODO
                            $resPromovidos[] = 'NO';
                            $resObservaciones[] = 'REPROBADO';
                            $resFechas[] = ' ';
                        }else{
                            //ENTONCES OBVIAMENTE APROBÓ
                            $resPromovidos[] = 'SI';
                            $resObservaciones[] = 'APROBÓ';
                            $resFechas[] = ' ';
                        }
                        break;
                    default: //POR DEFECTO SERIA LA REGLA CON 3 MATERIAS
                        if (($contadorReprobados+$contadorRetirados)>3) {
                            //EL ESTUDIANTE REPROBÓ MAS DE 3 MATERIAS, ENTONCES REPRUEBA TODO
                            $resPromovidos[] = 'NO';
                            $resObservaciones[] = 'REPROBADO';
                            $resFechas[] = ' ';
                        }else{
                        //APROBÓ CON ARRASTRE, ESTA DENTRO DEL RANGO y ES PROMOVIDO
                            //HACIENDO PRUEBAS DE SI ES EL ULTIMO CURSO
                            if ($regimen=='ANUALIZADO') { //SI ES ANUALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO
                                if (str_contains($cursoMayoria, 'TERCER')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                    $resPromovidos[] = 'NO';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }else{
                                    $resPromovidos[] = 'SI';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }
                            }else{
                                //ES SEMESTRALIZADO
                                //SI ES EL ULTIMO CURSO YA NO PONER APROBO ARRASTRE SINO REPROBADO
                                if (str_contains($cursoMayoria, 'SEXTO')) { //HACE REFERENCIA A TERCERO SUPERIOR O A TERCER AÑO
                                    $resPromovidos[] = 'NO';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }else{
                                    $resPromovidos[] = 'SI';
                                    $resObservaciones[] = 'APROBÓ C/ ARRASTRE';
                                    $resFechas[] = ' ';
                                }
                            }
                        }
                        break;
                }
            }








            $cMaterias[] = $contadorMaterias;
            $cAprobados[] = $contadorAprobados;
            $cReprobados[] = $contadorReprobados;
            $cRetirados[] = $contadorRetirados;
            $cCalifNull[] = $contadorCalifNull;
        }
        // $data=json_encode($data); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES
        // return $data;    //TAMBN SE PUEDE SUMANDO $RequestData[n]['id']


        //NO ES NECESARIO HACER JSON_ENCODE
        return response()->json(["resFechas" =>$resFechas,"resPromovidos" =>$resPromovidos,"resObservaciones" =>$resObservaciones], 200);
        //ESTE ES PARA VER LOS NUMEROS DE ESTADISTICA DE LAS CANTIDADES DE LAS MATERIAS
        // return response()->json(["CantAprob" =>$cAprobados,"CantReprob" =>$cReprobados,"CantRetirado" =>$cRetirados,"CantMaterias" =>$cMaterias,"CantSinCalif" =>$cCalifNull], 200);
    }
    public function ListarForHeaderFinal(Request $request)
    {
        $course = $request->input('course');
        $id_gestion = $request->input('Anio_id');
        $Malla = $request->input('Malla');
        // $datasql = DB::select("SELECT *,SUBSTRING(Sigla,5,7) as SiglaNum from cursos where NivelCurso='$course' and Anio_id=$id_gestion order by SiglaNum asc"); //ANTES ORDENABAMOS POR SUBSTRING
        $datasql = DB::select("SELECT * from cursos where NivelCurso='$course' and Anio_id=$id_gestion and Malla='$Malla' order by Rango asc"); //AHORA POR RANGO


        $ArrayMats = array();
        $ArraySiglaP = array();
        $ArrayHoras = array();
        $ArrayMatsS = array();
        $ArraySiglaS = array();
        $contador=0;

        $Mats=array();
        $SiglaP=array();
        $Horas=array();
        $MatsS=array();
        $SiglaS=array();

        $data = array();
        foreach ($datasql as $cdata) {

            // $materiaid = $cdata->id; //SI FUNCIONABA; PERO CUANDO SE TRATABA DE PARALELO B, NO DETECTABA PRERREQUISITO A CAUSA DE LOS PK
            $materiaid = $cdata->Sigla;
            $ArrayMats[] = $cdata->NombreCurso;
            $ArraySiglaP[] = $cdata->Sigla;
            $ArrayHoras[] = $cdata->Horas;
            $textMats='';
            $textSiglas='';
            //SI FUNCIONABA; PERO CUANDO SE TRATABA DE PARALELO B, NO DETECTABA PRERREQUISITO A CAUSA DE LOS PK
            // $prerreqs = DB::select("select p.id,p.id_materia_p,p.id_materia_s,
            // m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin', m.Horas,m.NombreCurso,m.NivelCurso,
            // m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec'
            // from prerrequisitos p LEFT JOIN
            // cursos m ON m.id=p.id_materia_p LEFT JOIN
            // cursos m2 ON m2.id=p.id_materia_s
            // WHERE m.id=$materiaid");
            $prerreqs = DB::select("select p.id,p.id_materia_p,p.id_materia_s,
            m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin', m.Horas,m.NombreCurso,m.NivelCurso,
            m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec'
            from prerrequisitos p LEFT JOIN
            cursos m ON m.id=p.id_materia_p LEFT JOIN
            cursos m2 ON m2.id=p.id_materia_s
            WHERE m.Anio_id=$id_gestion and m.Sigla='$materiaid' and m.Malla='$Malla' and p.Anio_id=$id_gestion and p.Malla='$Malla'");
            foreach ($prerreqs as $p) {
                $textMats=$p->materia_sec.'/'.$textMats;
                $textSiglas=$p->cod_sec.'/'.$textSiglas;
            }
            $ArrayMatsS[]=rtrim($textMats,'/');;
            $ArraySiglaS[]=rtrim($textSiglas,'/');
            $contador = $contador+1;
        }
        $cantidadMaterias = $contador;
        for ($i=0; $i < $cantidadMaterias; $i++) {
            array_push($Mats,$ArrayMats[$i]); //PARA MATERIA PRINCIPAL
            array_push($SiglaP,$ArraySiglaP[$i]); //SIGLA PRINCIPAL
            array_push($Horas,$ArrayHoras[$i]); //PARA HORAS
            array_push($MatsS,$ArrayMatsS[$i]); //PARA PRERREQUISITOS
            array_push($SiglaS,$ArraySiglaS[$i]);
        }
        //$gg=json_encode($gg); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES

        // $data[0] = [$ArrayMatsS[0],$ArrayMatsS[1],$ArrayMatsS[2],$ArrayMatsS[3]];
        // $data[1] = [$ArraySiglaS[0],$ArraySiglaS[1],$ArraySiglaS[2],$ArraySiglaS[3]];
        // $data[2] = [$ArrayHoras[0],$ArrayHoras[1],$ArrayHoras[2],$ArrayHoras[3]];
        // $data[3] = [$ArraySiglaP[0],$ArraySiglaP[1],$ArraySiglaP[2],$ArraySiglaP[3]];
        // $data[4] = [$ArrayMats[0],$ArrayMats[1],$ArrayMats[2],$ArrayMats[3]];



        // $data[0] = $MatsS;
        // $data[1] = $SiglaS;
        // $data[2] = $Horas;
        // $data[3] = $SiglaP;
        // $data[4] = $Mats;
        $data[0] = $MatsS;
        // $data[1] = $SiglaS;
        $data[1] = $Horas;
        $data[2] = $SiglaP;
        $data[3] = $Mats;
        return $data;
    }
    public function CulminarInscripciones(Request $request){
        $Anio_id = $request->input('Anio_id');
        DB::select("UPDATE calificaciones AS c
        INNER JOIN estudiantes AS e ON c.Estudiante_id = e.id
        LEFT JOIN administrativos AS d ON e.Admin_id = d.id
        LEFT JOIN administrativos AS d2 ON e.Admin_idPC = d2.id
        SET c.Docente_Especialidad = CONCAT(COALESCE(d.Ap_Paterno, ''), ' ', COALESCE(d.Ap_Materno, ''), ' ', COALESCE(d.Nombre, '')),
            c.Docente_Practica = CONCAT(COALESCE(d2.Ap_Paterno, ''), ' ', COALESCE(d2.Ap_Materno, ''), ' ', COALESCE(d2.Nombre, '')),
            c.Especialidad_Estudiante = COALESCE(e.Especialidad, ''),
            c.Observacion_Estudiante = COALESCE(e.Observacion, ''),
            c.Malla_Estudiante = COALESCE(e.Malla, '')
        WHERE c.Anio_id = $Anio_id
        ");
        return 'SE CULMINO LA GESTION';
    }
    public function DeshacerCulminarInscripciones(Request $request){
        $Anio_id = $request->input('Anio_id');
        DB::select("UPDATE calificaciones AS c
        INNER JOIN estudiantes AS e ON c.Estudiante_id = e.id
        LEFT JOIN administrativos AS d ON e.Admin_id = d.id
        LEFT JOIN administrativos AS d2 ON e.Admin_idPC = d2.id
        SET c.Docente_Especialidad = null,
            c.Docente_Practica = null,
            c.Especialidad_Estudiante = null,
            c.Observacion_Estudiante = null,
            c.Malla_Estudiante = null
        WHERE c.Anio_id = $Anio_id
        ");
        return 'SE CULMINO LA GESTION';
    }
    public function ListarForHeaderFinal1(Request $request)
    {
        //FUNCIONA PERO SOLO CUANDO ES 1 SIGLA NOMAS COMO PRERREQUISITO
        // $data = Curso::where('NombreCurso','=',)
        $datasql = DB::select("select p.id,p.id_materia_p,p.id_materia_s,
        m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin', m.Horas,m.NombreCurso,m.NivelCurso,
        m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec'
        from prerrequisitos p LEFT JOIN
        cursos m ON m.id=p.id_materia_p LEFT JOIN
        cursos m2 ON m2.id=p.id_materia_s
        WHERE `m`.`NivelCurso`='PRIMERO SUPERIOR A'");

        $ArrayMats = array();
        $ArraySiglaP = array();
        $ArrayHoras = array();
        $ArrayMatsS = array();
        $ArraySiglaS = array();
        $contador=0;

        $Mats=array();
        $SiglaP=array();
        $Horas=array();
        $MatsS=array();
        $SiglaS=array();

        $data = array();
        foreach ($datasql as $cdata) {
            $ArrayMats[] = $cdata->mat_prin;
            $ArraySiglaP[] = $cdata->cod_prin;
            $ArrayHoras[] = $cdata->Horas;
            $ArrayMatsS[] = $cdata->materia_sec;
            $ArraySiglaS[] = $cdata->cod_sec;
            $contador = $contador+1;
        }
        $cantidadMaterias = $contador;
        for ($i=0; $i < $cantidadMaterias; $i++) {
            array_push($Mats,$ArrayMats[$i]);
            array_push($SiglaP,$ArraySiglaP[$i]);
            array_push($Horas,$ArrayHoras[$i]);
            array_push($MatsS,$ArrayMatsS[$i]);
            array_push($SiglaS,$ArraySiglaS[$i]);
        }
        //$gg=json_encode($gg); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES

        // $data[0] = [$ArrayMatsS[0],$ArrayMatsS[1],$ArrayMatsS[2],$ArrayMatsS[3]];
        // $data[1] = [$ArraySiglaS[0],$ArraySiglaS[1],$ArraySiglaS[2],$ArraySiglaS[3]];
        // $data[2] = [$ArrayHoras[0],$ArrayHoras[1],$ArrayHoras[2],$ArrayHoras[3]];
        // $data[3] = [$ArraySiglaP[0],$ArraySiglaP[1],$ArraySiglaP[2],$ArraySiglaP[3]];
        // $data[4] = [$ArrayMats[0],$ArrayMats[1],$ArrayMats[2],$ArrayMats[3]];



        $data[0] = $MatsS;
        $data[1] = $SiglaS;
        $data[2] = $Horas;
        $data[3] = $SiglaP;
        $data[4] = $Mats;


        return $data;
    }
    public function ListarEstadisticasCentralizadorFinal() //lista estadisticas de todos en uno
    {
        // $array = array();
        // $Lista[] = $array;

        $data = array();

        // array_push($competition_all, $newCompete);

        $_calif = new CalificacionesController();
        $Cursos = Curso::distinct()->get(['NivelCurso']);
        $gg = 0;
        foreach ($Cursos as $c) {
            $varCurso = $c->NivelCurso;

            $CentralizadorFinalData = $_calif->ListarEstadisticasForCentralizadorFinal($varCurso);
            //CANT RETIRADOS, APROBADOS, REPROBADOS

            //CONVIERTE UN ARRAY EN OBJETO
            // $ForTittles =(object)$CentralizadorFinalData; //metodo 1
            $ForTittles = json_decode(json_encode($CentralizadorFinalData), true); //metodo 3 //SI O SI TRUE Y ADEMAS SIRVE PARA Q NO DE ERROR
            //AHORA SI PODEMOS SELECCIONAR EL VALOR DE UNA FILA ENTERA CON [0] SIN TENER ERRORES

            $Generalaprobados = 0;
            $Generalreprobados = 0;
            $Generalretirados=0;
            // obtener names de columnas
            if ($gg==0) {
                $TitlesColums=array_keys($ForTittles[0]); //OBTENER TITULOS DE COLUMNAS
                // $dd = json_decode(json_encode($CentralizadorFinalData), true);
                $dd=$ForTittles;
                $aprobados = 0;
                $reprobados = 0;
                $retirados=0;

                //ITERACION ESTUDIANTE X ESTUDIANTE

                foreach ($dd as $e ) {
                    //CONTAR PARA ESTADISTICAS
                    for ($cont=6; $cont <count($TitlesColums); $cont++) {
                        // VERIFICANDO MATERIA POR MATERIAS
                        $title = $TitlesColums[$cont]; //ACA TENEMOS EL NOMBRE DE LA COLUMNA
                        if ($e[$title]!=0) {
                            if ($e[$title]<61) {
                            $reprobados++;
                            } else {
                            $aprobados++;
                            }
                        }
                        else{
                            $retirados++;
                            $reprobados++;
                        }


                    }

                    // if ($retirados==(count($TitlesColums)-6)) {
                    //     //PRUEBA DEL RETIRADO => TODO CERO o CANTIDAD DE CEROS = cantMaterias
                    //     //RETIRADO
                    //     $e['Observacion'] = 'RETIRADO';
                    // } else {
                    //     if ($aprobados==(count($TitlesColums)-6)) {
                    //     $e['Observacion'] = 'APROBÓ';
                    //     } else {
                    //     if (strpos($varCurso, 'SUPERIOR') !== false) { //SI EN EL TXT DE NIVEL HAY UNA PALABRA QUE TENGA SUPERIOR HACER
                    //         //PRUEBA DEL REPROBADO / APROBADO, SUPERIOR => REPRUEBA MAX 2 IGUAL APRUEBA PERO DEBE LLEVAR SOLO ESAS 2 MATERIAS
                    //         //SI TIENE MAS DE 2 MATERIAS REPROBADAS PIERDE EL AÑO
                    //         if($reprobados>2)
                    //         {
                    //         //REPROBADO - SUPERIOR
                    //         $e['Observacion'] = 'REPROBADO';
                    //         }else{
                    //         //APROBADO- SUPERIOR
                    //         $e['Observacion'] = 'APROBÓ C/ ARRASTRE';
                    //         }
                    //     } else {
                    //         //PRUEBA DEL REPROBADO / APROBADO, NIVEL CAPACITACION => REPRUEBA SOLO 1 Y PIERDE TODO
                    //         //SI TIENE 1 MATERIA REPROBADAS PIERDE EL AÑO
                    //         if($reprobados>0)
                    //         {
                    //         //REPROBADO - CAPACITACION
                    //         $e['Observacion'] = 'REPROBADO';
                    //         }else{
                    //         //APROBADO- CAPACITACION
                    //         $e['Observacion'] = 'APROBÓ';
                    //         }
                    //     }
                    //     }
                    // }
                }




            }
            $gg++;


            // foreach ($CentralizadorFinalData as $d ) {
            //     $obs=$d->Observacion;
            //     if ($obs=='RETIRADO') {
            //         $retirados++;
            //     }else if($obs=='REPROBADO'){
            //         $reprobados++;
            //     }else{
            //         $aprobados++;
            //     }
            // }


            // $data[] = ['CURSO'=>$varCurso,'APROBADOS'=>$aprobados,'REPROBADOS'=>$reprobados,'RETIRADOS'=>$retirados];
            $data[]=$CentralizadorFinalData;
        }
        return $reprobados;
    }

    public function ListarEstadisticasForCentralizadorFinal(String $course) //lista estadisticas por curso
    {
        $curso = $course;
        //CONSEGUIR ID DE CURSO POR NIVEL DE CURSO
        $Cursodata= DB::select("SELECT *,SUBSTRING(Sigla,5,7) as SiglaNum from cursos where NivelCurso='$curso' order by SiglaNum asc");
        //GUARDANDO ID DEL PRIMER CURSO DE LA PRIMERA FILA
        $idCurso = $Cursodata[0]->id;


        //ARMAR LISTA DE CURSOS PARA ENVIAR COMO LISTA DE MATERIAS
        $concatmat = array();
        foreach ($Cursodata as $cdata) {

            $varCurso = $cdata->id;
            $dataCurso = DB::select("select NombreCurso,Sigla from cursos where id=$varCurso");
            // dataVariable =  DB::select("delete from NombreTabla where PrimaryKey='Simbolo Dolarid'");
            $concatmat[] = $dataCurso[0]->NombreCurso;
        }


        // $concatmat = collect($concatmat)->sortBy('Sigla')->reverse()->toArray();
        $concatmat=json_encode($concatmat); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES
        $materias=$concatmat;


        // return $materias;


        //CONSEGUIR LISTAR TODOS LOS ESTUDIANTES DE UN CURSO POR SU ID DE CURSO (SELECCIONANDO SOLO SI ID del estudiante)
        $dataEsts = DB::select("SELECT `calificaciones`.estudiante_id FROM `calificaciones` LEFT JOIN
        `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id` LEFT JOIN
        `administrativos` ON `estudiantes`.`Admin_id` = `administrativos`.`id`
        WHERE calificaciones.curso_id = $idCurso ORDER BY estudiantes.Ap_Paterno , estudiantes.Ap_Materno, estudiantes.Nombre");



        //PROCEDER A CONSTRUIR LA DATA CENTRALIZADOR FINAL

        $data = array();
                foreach ($dataEsts as $est) {
                    // $CentralizadorData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                    $CentralizadorData=DB::select("CALL getCentralizadorFinal('$materias',$est->estudiante_id,'$curso')"); // PARA LLAMAR PROCEDURES
                    $data[] = $CentralizadorData[0];
                }
        return $data;
    }
    public function VerificarSegundaInstancia(Request $request)
    {
        // $dataRequest = $request->all();
        $idCurso= $request->input('curso_id');
        $mallatxt= $request->input('Malla');

        $anioid= $request->input('anio_id'); //7
        $idest = $request->input('estudiante_id'); //190

        $MateriasdelEstudiante = DB::select("SELECT * FROM calificaciones WHERE anio_id=$anioid AND estudiante_id=$idest");

        $contadorAprobados=0;
        $contadorReprobados=0;
        $contadorRetirados=0;
        $contadorSegundaInstancia=0;
        $contadorInvalidos=0; //CANTIDAD DE MATERIAS Q NO CUMPLEN CON LA NOTA DE 40
        $contadorCalifNull=0;
        foreach ($MateriasdelEstudiante as $a) {
            if ($a->Promedio==null) {
                $contadorCalifNull++;
            }else{
                if($a->Promedio < 61 && $a->Promedio > 39){
                    //SEGUNDA INSTANCIA
                    $contadorSegundaInstancia++;
                    $contadorReprobados++;
                } else if ($a->Promedio > 60) {
                    //APROBÓ
                    $contadorAprobados++;
                } else if ($a->Promedio == 0){
                    //RETIRADO
                    $contadorReprobados++;
                    $contadorRetirados++;
                } else if ($a->Promedio < 40 && $a->Promedio > 0){
                    //REPROBÓ/SIN DERECHO A 2da INSTANCIA
                    $contadorReprobados++;
                    $contadorInvalidos++;
                } else {
                    //REPROBÓ
                    $contadorReprobados++;
                }
            }
        }

        $RealizaraSegundaInstancia=true;
        if (($contadorSegundaInstancia+$contadorInvalidos+$contadorRetirados)>3) { //SI SON MAS DE 3 MATERIAS REPROBADAS NO PUEDE DAR 2DA INSTANCIA
            $RealizaraSegundaInstancia=false;
        } else{
            $RealizaraSegundaInstancia=true;
        }


        return $RealizaraSegundaInstancia;






        //-------------------------------------------------------------------

        // // OBTENER DATOS DE CURSO MEDIANTE ID
        // $cursoData= DB::select("SELECT * from cursos where id='$idCurso'");
        // // $curso = 'PRIMERO SUPERIOR A';
        // // $MateriaActual='TEORIA DEL SONIDO';
        // // $Nivel = 'TECNICO SUPERIOR';

        // $curso = $cursoData[0]->NivelCurso;
        // $MateriaActual=$cursoData[0]->NombreCurso;
        // if (strpos($curso, 'SUPERIOR') !== false) {
        //     $Nivel = 'TECNICO SUPERIOR';
        // }else{
        //     $Nivel = 'CAPACITACION';
        // }

        // // $CentralizadorFinalData = $_calif->ListarForCentralizadorFinal($NivelCurso);

        // //CONSEGUIR ID DE CURSO POR NIVEL DE CURSO
        // // $Cursodata= DB::select("SELECT *,SUBSTRING(Sigla,5,7) as SiglaNum from cursos where NivelCurso='$curso' order by SiglaNum asc");// ANTES ERA POR NUMERO EL ORDEN
        // $Cursodata= DB::select("SELECT * from cursos where NivelCurso='$curso' and anio_id=$anioid order by Rango asc"); //AHORA ORDENAMOS POR RANGO
        // //GUARDANDO ID DEL PRIMER CURSO DE LA PRIMERA FILA
        // $idCurso = $Cursodata[0]->id;


        // $concatmat = array();
        // foreach ($Cursodata as $cdata) {

        //     $varCurso = $cdata->id;
        //     $dataCurso = DB::select("select NombreCurso,Sigla from cursos where id=$varCurso");
        //     // dataVariable =  DB::select("delete from NombreTabla where PrimaryKey='Simbolo Dolarid'");
        //     $concatmat[] = $dataCurso[0]->NombreCurso;
        // }

        // $materiasEncontradas=json_encode($concatmat); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES
        // $materias=$materiasEncontradas;

        // //OBTENER INFO DE ESTUDIANTE DE SUS CALIFICACIONES DE TODAS SUS MATERIAS
        // $dataCentralizador = DB::select("CALL getCentralizadorFinalLCCHFinish('$materias',$request->estudiante_id,'$curso',$anioid,'$mallatxt')"); // PARA LLAMAR PROCEDURES

        // //VERIFICAR CALIFICACIONES
        // $contadorAprobados=0;
        // $contadorReprobados=0;
        // $contadorRetirados=0;
        // $contadorSegundaInstancia=0;
        // $contadorInvalidos=0; //CANTIDAD DE MATERIAS Q NO CUMPLEN CON LA NOTA DE 40
        // $CursosInvalidos = array();
        // // foreach ($Cursodata as $a) {
        // //     $nomMateria= $a->NombreCurso;
        // //     if ($dataCentralizador[0]->$nomMateria != null) {//aca debemos verificar si es null la calificacion, sino da error
        // //         $calif=(int)($dataCentralizador[0]->$nomMateria);
        // //         if ($calif == 0) {
        // //             $contadorRetirados++;
        // //         }
        // //         else
        // //         {
        // //             if ($calif<61) {
        // //                 $contadorReprobados++;
        // //                 if ($calif>39) {
        // //                     $contadorSegundaInstancia++;
        // //                 }else if($calif<40){
        // //                     $contadorInvalidos++;
        // //                     $CursosInvalidos[]=$nomMateria;
        // //                 }
        // //             }else{
        // //                 $contadorAprobados++;
        // //             }
        // //         }
        // //     }

        // // }
        // foreach ($Cursodata as $a) {
        //     $nomMateria = $a->NombreCurso;

        //     // Verificar si la propiedad existe y no es nula
        //     if (isset($dataCentralizador[0]->$nomMateria) && $dataCentralizador[0]->$nomMateria !== null) {
        //         $calif = (int)($dataCentralizador[0]->$nomMateria);

        //         if($calif < 61 && $calif > 39){
        //             //SEGUNDA INSTANCIA
        //             $contadorSegundaInstancia++;
        //             $contadorReprobados++;
        //         } else if ($calif > 60) {
        //             //APROBÓ
        //             $contadorAprobados++;
        //         } else if ($calif == 0){
        //             //RETIRADO
        //             $contadorReprobados++;
        //             $contadorRetirados++;
        //         } else if ($calif < 40 && $calif > 0){
        //             //REPROBÓ/SIN DERECHO A 2da INSTANCIA
        //             $contadorReprobados++;
        //             $contadorInvalidos++;
        //         } else {
        //             //REPROBÓ
        //             $contadorReprobados++;
        //         }


        //         // if ($calif == 0) {
        //         //     $contadorRetirados++;
        //         // } else {


        //         //     if ($calif < 61) {
        //         //         $contadorReprobados++;
        //         //         if ($calif > 39) {
        //         //             $contadorSegundaInstancia++;
        //         //         } else if ($calif < 40) {
        //         //             $contadorInvalidos++;
        //         //             $CursosInvalidos[] = $nomMateria;
        //         //         }
        //         //     } else {
        //         //         $contadorAprobados++;
        //         //     }
        //         // }
        //     }else{
        //         //NO PERTENECE A LA MATERIA
        //     }
        // }

        // $RealizaraSegundaInstancia=true;
        // if (($contadorSegundaInstancia+$contadorInvalidos+$contadorRetirados)>3) { //SI SON MAS DE 3 MATERIAS REPROBADAS NO PUEDE DAR 2DA INSTANCIA
        //     $RealizaraSegundaInstancia=false;
        // } else {
        //     //ESTA DENTRO DEL RANGO DE MATERIAS REPROBADAS DE LO PERMITIDO
        //     $RealizaraSegundaInstancia=true;

        //     // //$contadorSegundaInstancia //CANTIDAD DE MATERIAS VALIDAS PARA SEGUNDA INSTANCIA
        //     // // $reglaInstancia='';
        //     // switch ($Nivel) { //antes $Nivel
        //     //     case 'HASTA 3 MATERIAS DE 2DA INSTANCIAR':
        //     //         if ($contadorSegundaInstancia<4) { //SOLO SE ADMITEN HASTA 3 MATERIAS COMO 2DA INSTANCIA
        //     //                 $RealizaraSegundaInstancia=true;
        //     //         }
        //     //         break;
        //     //     case 'CAPACITACION':
        //     //         if($contadorRetirados!=0){
        //     //             $RealizaraSegundaInstancia=false;
        //     //         }else if ($contadorInvalidos!=0) {
        //     //             $RealizaraSegundaInstancia=false;
        //     //         }else{
        //     //             $RealizaraSegundaInstancia=true;
        //     //         }
        //     //         break;
        //     //     default:
        //     //         # code...
        //     //         break;
        //     // }
        // }

        // // //VERIFICACION EXTRA - SI LA MATERIA ACTUAL COINCIDE CON LA MATERIA INVALIDA POR LO TANTO FALSEAR
        // // for ($i=0; $i < $contadorInvalidos; $i++) {
        // //     if ($CursosInvalidos[$i]==$MateriaActual) {
        // //         $RealizaraSegundaInstancia=false;
        // //     }
        // // }

        // // return true;
        // return $RealizaraSegundaInstancia;
        // // return $contadorSegundaInstancia;

    }
    public function ListarForCentralizadorFinal(Request $request) //CENTRALIZADOR FINAL FINISH
    {
        // $data = DB::select("SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI  ,calificaciones.PruebaRecuperacion,calificaciones.curso_id as Curso_id, calificaciones.estudiante_id,cursos.NombreCurso,cursos.Horas, cursos.NivelCurso,cursos.Tipo,cursos.Sigla,cursos.BiTriEstado
        // FROM calificaciones LEFT JOIN
        // estudiantes ON calificaciones.estudiante_id = estudiantes.id LEFT JOIN
        // cursos ON calificaciones.curso_id = cursos.id
        // where cursos.NivelCurso='PRIMERO SUPERIOR A' ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla");


    //     $FF="SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI, calificaciones.Promedio,
    //     (CASE WHEN cursos.NombreCurso='APRECIACION MUSICAL' THEN calificaciones.Promedio ELSE 0 END) as 'APRECIACION MUSICAL',
    //      (CASE WHEN cursos.NombreCurso='ARMONIA I' THEN calificaciones.Promedio ELSE 0 END) as 'ARMONIA I',
    //       (CASE WHEN cursos.NombreCurso='INSTRUMENTO COMPLEMENTARIO I' THEN calificaciones.Promedio ELSE 0 END) as 'INSTRUMENTO COMPLEMENTARIO I',
    //    (CASE WHEN cursos.NombreCurso='INSTRUMENTO DE ESPECIALIDAD I' THEN calificaciones.Promedio ELSE 0 END) as 'INSTRUMENTO DE ESPECIALIDAD I',
    //    (CASE WHEN cursos.NombreCurso='LENGUAJES MUSICALES SUPERIOR' THEN calificaciones.Promedio ELSE 0 END) as 'LENGUAJES MUSICALES SUPERIOR',
    //    (CASE WHEN cursos.NombreCurso='PRACTICA DE CONJUNTOS I' THEN calificaciones.Promedio ELSE 0 END) as 'PRACTICA DE CONJUNTOS I',
    //    (CASE WHEN cursos.NombreCurso='TEORIA DEL SONIDO' THEN calificaciones.Promedio ELSE 0 END) as 'TEORIA DEL SONIDO'
    //    FROM estudiantes LEFT JOIN
    //    calificaciones ON estudiantes.id = calificaciones.estudiante_id LEFT JOIN
    //    -- estudiantes ON calificaciones.estudiante_id = estudiantes.id LEFT JOIN
    //    cursos ON calificaciones.curso_id = cursos.id
    //    where cursos.NivelCurso='PRIMERO SUPERIOR A' ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla";

    // $data=DB::select("SELECT example(751,'PRIMERO SUPERIOR A');");   //para llamar a funciones
    $TipoEsts = $request->input('Arrastre');
    // $TipoEsts = 'ARRA1STREss';
    $curso = $request->input('NivelCurso');
    $mallatxt = $request->input('Malla');
    $id_gestion= $request->input('Anio_id');
    //CONSEGUIR ID DE CURSO POR NIVEL DE CURSO
    // $Cursodata= DB::select("SELECT *,SUBSTRING(Sigla,5,7) as SiglaNum from cursos where NivelCurso='$curso' and anio_id=$id_gestion order by SiglaNum asc"); //ANTES POR LOS NUMEROS
    $Cursodata= DB::select("SELECT * from cursos where NivelCurso='$curso' and Malla='$mallatxt' and anio_id=$id_gestion order by Rango asc"); //AHORA ORDENAMOS POR RANGO
    //GUARDANDO ID DEL PRIMER CURSO DE LA PRIMERA FILA
    $idCurso = $Cursodata[0]->id;


    //ARMAR LISTA DE CURSOS PARA ENVIAR COMO LISTA DE MATERIAS
    // $inicio = '['; $fin ='"]';
    // $concatmat = '';
    // //(1) FUNCIONA PERO VARIA MUCHO O SE DESORDENA CON LOS """" ASI Q MEJOR ENVIAR UN CONJUNTO DE DATOS
    // foreach ($Cursodata as $c) {
    //     $concatmat='"'.$concatmat.'"'.','.'"'.$c->NombreCurso.''; //(1)
    // }
    // $concatmat = $inicio.$concatmat.$fin; //UNIENDO TODO
    // //ESTO CORRIGE ERRORES PERO AL CORREGIR SE VUELVE COMO UN CONJUNTO DE DATOS NORMAL COMO SI FUESE JSON, SIRVE (1)
    // $concatmat = preg_replace('/""""""""",/m',"", $concatmat);
    // $materias=$concatmat;



    $concatmat = array();
    foreach ($Cursodata as $cdata) {
        $varCurso = $cdata->id;
        $dataCurso = DB::select("select NombreCurso,Sigla from cursos where id=$varCurso and anio_id=$id_gestion");
        // dataVariable =  DB::select("delete from NombreTabla where PrimaryKey='Simbolo Dolarid'");
        $concatmat[] = $dataCurso[0]->NombreCurso;
    }
    // $concatmat = collect($concatmat)->sortBy('Sigla')->reverse()->toArray();
    $concatmat=json_encode($concatmat, JSON_UNESCAPED_UNICODE); //CONVIRTIENDO EN JSON PARA QUE NO DE ERRORES....... URGENTO SE PONE JSON_UNESCAPED_UNICODE PARA Q LAS TILDES NO SE PIERDAN
    $materias=$concatmat;

    //AYUDITA GPT
    // $concatmat = array();

    // foreach ($Cursodata as $cdata) {
    //     $varCurso = $cdata->id;
    //     $dataCurso = DB::select("select NombreCurso,Sigla from cursos where id=$varCurso and anio_id=$id_gestion");

    //     $curso = array(
    //         "nameCurso" => $dataCurso[0]->NombreCurso
    //     );
    //     array_push($concatmat, $curso);
    // }
    // $materias=$concatmat;

    // $concatmat = json_encode($concatmat);
    // //$concatmat = json_decode($concatmat);
    // // $new_string = "";
    // // foreach ($concatmat as $item) {
    // //     $new_string .= $item->nameCurso . ", ";
    // // }
    // // $new_string = rtrim($new_string, ", ");

    // $materias = $concatmat;
    //$materias = '{"nameCurso":"INSTRUMENTO DE ESPECIALIDAD II"},{"nameCurso":"PRACTICA DE CONJUNTOS II"},{"nameCurso":"MUSICA DE CAMARA I"},{"nameCurso":"ARMONIA II"},{"nameCurso":"FORMAS Y ANALISIS MUSICAL"},{"nameCurso":"HISTORIA DE LA MUSICA I"},{"nameCurso":"INSTRUMENTO COMPLEMENTARIO II"},{"nameCurso":"PRODUCCION MUSICAL Y GESTION CULTURAL"}';

    // return $materias;


    //CONSEGUIR LISTAR TODOS LOS ESTUDIANTES DE UN CURSO POR SU ID DE CURSO (SELECCIONANDO SOLO SI ID del estudiante)
    //2023 aumentamos para q se pueda listar a estudiantes DE
    $dataEsts = array();
    switch ($TipoEsts) {
        case 'ARRASTRE':
            //SOLO ESTUDIANTES REGULARES
            $CursoData = Curso::where('NivelCurso','=', $curso)->where('Anio_id','=',$id_gestion)->get();
            //obtener la lista de los estudiantes pero solo por su estudiante_id ...
            //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
            $dataEsts = array();
            foreach ($CursoData as $k ) {
                $CalificacionesData = Calificaciones::where('curso_id','=', $k->id)->where('Anio_id','=',$id_gestion)->get();
                $CalificacionesData = $CalificacionesData->unique('estudiante_id');

                $cont=0;
                foreach ($CalificacionesData as $C) {
                    // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                    $EstudiantesData = DB::select("SELECT `calificaciones`.estudiante_id
                    FROM `estudiantes`
                        LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                        LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
                        LEFT JOIN `administrativos` ON `administrativos`.`id` = `estudiantes`.`Admin_id`
                        WHERE estudiantes.id=$C->estudiante_id and calificaciones.Arrastre='ARRASTRE' and calificaciones.anio_id=$id_gestion and cursos.NivelCurso='$curso'");

                    if (Count($EstudiantesData)!=0) {
                        $dataEsts[] = $EstudiantesData[0];
                    }

                }
            }
            break;
        case 'REGULAR':
            //SOLO ESTUDIANTES REGULARES
            $CursoData = Curso::where('NivelCurso','=', $curso)->where('Anio_id','=',$id_gestion)->get();
            //obtener la lista de los estudiantes pero solo por su estudiante_id ...
            //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
            $dataEsts = array();
            foreach ($CursoData as $k ) {
                $CalificacionesData = Calificaciones::where('curso_id','=', $k->id)->where('Anio_id','=',$id_gestion)->get();
                $CalificacionesData = $CalificacionesData->unique('estudiante_id');

                $cont=0;
                foreach ($CalificacionesData as $C) {
                    // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                    $EstudiantesData = DB::select("SELECT `calificaciones`.estudiante_id
                    FROM `estudiantes`
                        LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                        LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
                        LEFT JOIN `administrativos` ON `administrativos`.`id` = `estudiantes`.`Admin_id`
                        WHERE estudiantes.id=$C->estudiante_id and calificaciones.Arrastre='REGULAR' and calificaciones.anio_id=$id_gestion and cursos.NivelCurso='$curso'");

                        if (Count($EstudiantesData)!=0) {
                            $dataEsts[] = $EstudiantesData[0];
                        }
                }
            }
            break;

        default:
            //TODOS
            // $dataEsts = DB::select("SELECT `calificaciones`.estudiante_id FROM `calificaciones` LEFT JOIN
            // `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id` LEFT JOIN
            // `administrativos` ON `estudiantes`.`Admin_id` = `administrativos`.`id`
            // WHERE calificaciones.curso_id = $idCurso and calificaciones.anio_id=$id_gestion ORDER BY estudiantes.Ap_Paterno , estudiantes.Ap_Materno, estudiantes.Nombre");

            $CursoData = Curso::where('NivelCurso','=', $curso)->where('Anio_id','=',$id_gestion)->get();
            //obtener la lista de los estudiantes pero solo por su estudiante_id ...
            //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
            $dataEsts = array();
            foreach ($CursoData as $k ) {
                $CalificacionesData = Calificaciones::where('curso_id','=', $k->id)->where('Anio_id','=',$id_gestion)->get();
                $CalificacionesData = $CalificacionesData->unique('estudiante_id');

                $cont=0;
                foreach ($CalificacionesData as $C) {
                    // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                    $EstudiantesData = DB::select("SELECT `calificaciones`.estudiante_id
                    FROM `estudiantes`
                        LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                        LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
                        LEFT JOIN `administrativos` ON `administrativos`.`id` = `estudiantes`.`Admin_id`
                        WHERE estudiantes.id=$C->estudiante_id and calificaciones.anio_id=$id_gestion and cursos.NivelCurso='$curso'");


                    if (Count($EstudiantesData)!=0) {
                        $dataEsts[] = $EstudiantesData[0];
                    }
                }
            }

            break;
    }
    //ELIMINAR ESTUDIANTES REPETIDOS - FILTRAR ESTUDIANTES REPETIDOS - ELIMINADOR
    $dataEstsNew=array();
    foreach($dataEsts as $w){
        if(count(collect($dataEstsNew)->where('estudiante_id', $w->estudiante_id)->all())==0){
            $dataEstsNew[] = $w;
        }
    }
    //PROCEDER A CONSTRUIR LA DATA CENTRALIZADOR FINAL
    // $data=DB::select("SET numList = ARRAY[1,2,3,4,5,6]; CALL example1(751,'PRIMERO SUPERIOR A')"); // PARA LLAMAR PROCEDURES
    // $curso = $request->input('NivelCurso');
    // $materias="";
    // $materias= '["APRECIACION MUSICAL","ARMONIA I","INSTRUMENTO COMPLEMENTARIO I","INSTRUMENTO DE ESPECIALIDAD I","LENGUAJES MUSICALES SUPERIOR",
    // "PRACTICA DE CONJUNTOS I","TEORIA DEL SONIDO"]';
    // $data=DB::select("CALL getCentralizadorFinal('$materias',735,'$curso')"); // PARA LLAMAR PROCEDURES
    //$data = "CALL getCentralizadorFinal('$txt',751,'$curso');";

    $data = array(); //ES CON SEXO
    foreach ($dataEstsNew as $est) {
        // $CentralizadorData = Estudiantes::where('id','=', $C->estudiante_id)->first();
        $CentralizadorData=DB::select("CALL getCentralizadorFinalLCCHFinish('$materias',$est->estudiante_id,'$curso',$id_gestion,'$mallatxt')"); // PARA LLAMAR PROCEDURES
        // $CentralizadorData=DB::select("CALL getCentralizadorFinal(?,?,?,?), ['$materias',$est->estudiante_id,'$curso',$id_gestion,$materias]"); // PARA LLAMAR PROCEDURES
        $data[] = $CentralizadorData[0];
    }
    $data2 = array(); //ES SIN SEXO
    foreach ($dataEstsNew as $est) {
        // $CentralizadorData = Estudiantes::where('id','=', $C->estudiante_id)->first();
        $CentralizadorData=DB::select("CALL getCentralizadorFinalLCCHFinishSinSexo('$materias',$est->estudiante_id,'$curso',$id_gestion,'$mallatxt')"); // PARA LLAMAR PROCEDURES
        // $CentralizadorData=DB::select("CALL getCentralizadorFinalSinSexo(?,?,?,?), ['$materias',$est->estudiante_id,'$curso',$id_gestion,$materias]"); // PARA LLAMAR PROCEDURES
        $data2[] = $CentralizadorData[0];
    }

    return response()->json(["lista1" =>$data,"lista2" =>$data2,], 200);
    // return response()->json(["lista1" =>$dataEstsNew,"lista2" =>$Cursodata,], 200);

#region SE LOGRO GRACIAS A
//ESTE FUE DE AYUDA PARA LOGRARLO
// DELIMITER ;
// DELIMITER $$
// CREATE PROCEDURE ejemploProcedimiento(IN arreglo TEXT)
// BEGIN
//     DECLARE i INT DEFAULT 0;
//     DECLARE elemento VARCHAR(255);
//     DECLARE numElementos INT;

//     SET numElementos = JSON_LENGTH(arreglo);

//     WHILE i < numElementos DO
//         SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//         // realizar alguna operación con cada elemento del arreglo
//         SELECT elemento;
//         SET i = i + 1;
//     END WHILE;
// END
// $$

    //ESTE ES EL PERO DEL PASADO FINISH ORIGINAL 100 POR CIENTO NO FAKE //SE CORRIGIO LA PARTE Q EN EL CENTRALIZADOR FINAL NO HACIA CASO A LAS 2das INSTANCIAS
    // -- PROCEDURE FINALIZADO ESTE ES EL Q SE USA... ES CON DATOS DIANMICOS
    // DELIMITER ;
    // DELIMITER $$
    // CREATE OR REPLACE PROCEDURE getCentralizadorFinalSinSexo(dataArray VARCHAR(500),idEst INT, nombreCurso VARCHAR(100),idGestion INT)
    // BEGIN

    //   DECLARE _result varchar(10000) DEFAULT '';
    //   DECLARE _counter INT DEFAULT 0;
    //   DECLARE _value varchar(50);

    //   SET @ini = 'SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI,estudiantes.id,estudiantes.Especialidad,';
    //   SET @fin = CONCAT('FROM estudiantes LEFT JOIN calificaciones ON estudiantes.id = calificaciones.estudiante_id LEFT JOIN cursos ON calificaciones.curso_id = cursos.id where cursos.NivelCurso="',nombreCurso,'" AND estudiantes.id="',idEst,'" AND calificaciones.anio_id="',idGestion,'" ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla;');

    //   -- SET @myjson = '["APRECIACION MUSICAL","ARMONIA I","INSTRUMENTO COMPLEMENTARIO I","INSTRUMENTO DE ESPECIALIDAD I","LENGUAJES MUSICALES SUPERIOR", "PRACTICA DE CONJUNTOS I","TEORIA DEL SONIDO"]';
    //       SET @myjson = dataArray;

    //   WHILE _counter < JSON_LENGTH(@myjson)-1 DO
    //     -- do whatever, e.g. add-up strings...
    //     -- SET _result = CONCAT(_result, _counter, '-', JSON_VALUE(@myjson, CONCAT('$[',_counter,']')), '#');
    // 	 SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso="',JSON_VALUE(@myjson, CONCAT('$[',_counter,']')),'" AND estudiantes.id = ',idEst,' THEN

    //                           (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
    //                           ELSE 0 END) as "',JSON_VALUE(@myjson, CONCAT('$[',_counter,']')),'",');
    //     SET _counter = _counter + 1;
    //   END WHILE;

    //   -- ULTIMA ITERACION PARA EVITAR ERROR DEL FROM
    // 	SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso="',JSON_VALUE(@myjson, CONCAT('$[',_counter,']')),'" AND estudiantes.id = ',idEst,' THEN
    //                          (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
    //                          ELSE 0 END) as "',JSON_VALUE(@myjson, CONCAT('$[',_counter,']')),'" ');
    //     -- SET @sql = CONCAT(@ini,_result,@fin);
    //     -- RETURN @sql;
    //     SET @sql = CONCAT(@ini,_result,@fin);
    //   PREPARE stmt FROM @sql;
    //   EXECUTE stmt;
    //   DEALLOCATE PREPARE stmt;
    // END
    // $$


    //----------------------ESTE ES EL YA OFICIAL FINISH
//     -- PROCEDURE FINALIZADO ESTE ES EL Q SE USA... ES CON DATOS DIANMICOS SE CAMBIO EL NAME
//  -- drop PROCEDURE getCentralizadorFinal;
//     DELIMITER ;
//     DELIMITER $$
//     CREATE OR REPLACE PROCEDURE getCentralizadorFinalLCCHFin(IN arreglo TEXT,IN idEst INT, IN nombreCurso VARCHAR(255),IN idGestion INT)
// BEGIN

//       DECLARE _result varchar(10000) DEFAULT '';
//       -- DECLARE _value varchar(50);
//     DECLARE i INT DEFAULT 0;
//     DECLARE elemento VARCHAR(255);
//     DECLARE numElementos INT;
//     SET numElementos = JSON_LENGTH(arreglo);


//     SET @ini = 'SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI,estudiantes.id,estudiantes.Especialidad,estudiantes.Sexo,';
//     SET @fin = CONCAT('FROM estudiantes LEFT JOIN calificaciones ON estudiantes.id = calificaciones.estudiante_id LEFT JOIN cursos ON calificaciones.curso_id = cursos.id where cursos.NivelCurso="',nombreCurso,'" AND estudiantes.id="',idEst,'" AND calificaciones.anio_id="',idGestion,'" ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla;');

//     WHILE i < numElementos-1 DO
//         SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//         -- realizar alguna operación con cada elemento del arreglo
// 		SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' THEN
//                           	(IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                              ELSE 0 END) as ',elemento,',');

//         -- SELECT elemento;
//         SET i = i + 1;
//     END WHILE;

//     -- ULTIMA ITERACION PARA EVITAR ERROR DEL FROM
//         SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//         -- realizar alguna operación con cada elemento del arreglo
//     	SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' THEN
//                              (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                              ELSE 0 END) as ',elemento,' ');
//         SET @sql = CONCAT(@ini,_result,@fin);
//       PREPARE stmt FROM @sql;
//       EXECUTE stmt;
//       DEALLOCATE PREPARE stmt;
// END;

//     $$


// -- PARA HACER FUNCIONAR - SIRVE ES TODO
// SET @data = '[\"INSTRUMENTO DE ESPECIALIDAD II\",\"PRACTICA DE CONJUNTOS II\",\"MUSICA DE CAMARA I\",\"ARMONIA II\",\"FORMAS Y ANALISIS MUSICAL\",\"HISTORIA DE LA MUSICA I\",\"INSTRUMENTO COMPLEMENTARIO II\",\"PRODUCCION MUSICAL Y GESTION CULTURAL\"]';
//     CALL getCentralizadorFinalLCCHFin(@data,751,'SEGUNDO SUPERIOR A',4);
// SET @data = '[\"INSTRUMENTO DE ESPECIALIDAD III\",\"PRACTICA DE CONJUNTOS III\",\"MUSICA DE CAMARA II\",\"INFORMATICA MUSICAL\",\"FORMAS Y ANALISIS MUSICAL II\",\"HISTORIA DE LA MUSICA II\",\"PRACTICA CORAL/METODOLOGIA DE LA INVESTIGACION\"]';
//     CALL getCentralizadorFinalLCCHFin(@data,525,'TERCERO SUPERIOR A',4);


//----------------------- ACA PRESENTO A LA FINALISSIMA QUE TOMA EN CUENTA MATERIAS QUE NO PERTENECEN O NO REGISTRADOS "recuerda el NULL"
// -- drop PROCEDURE getCentralizadorFinal;
//     DELIMITER ;
//     DELIMITER $$
//     CREATE OR REPLACE PROCEDURE getCentralizadorFinalLCCHFin(IN arreglo TEXT,IN idEst INT, IN nombreCurso VARCHAR(255),IN idGestion INT)
// BEGIN

//       DECLARE _result varchar(10000) DEFAULT '';
//       -- DECLARE _value varchar(50);
//     DECLARE i INT DEFAULT 0;
//     DECLARE elemento VARCHAR(255);
//     DECLARE numElementos INT;
//     SET numElementos = JSON_LENGTH(arreglo);


//     SET @ini = 'SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI,estudiantes.id,estudiantes.Especialidad,estudiantes.Sexo,';
//     SET @fin = CONCAT('FROM estudiantes LEFT JOIN calificaciones ON estudiantes.id = calificaciones.estudiante_id LEFT JOIN cursos ON calificaciones.curso_id = cursos.id where cursos.NivelCurso="',nombreCurso,'" AND estudiantes.id="',idEst,'" AND calificaciones.anio_id="',idGestion,'" ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla;');

//     WHILE i < numElementos-1 DO
//         SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//         -- realizar alguna operación con cada elemento del arreglo
// 		SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' AND calificaciones.anio_id = ',idGestion,'  THEN
//                           	(IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                              ELSE NULL END) as ',elemento,',');

//         -- SELECT elemento;
//         SET i = i + 1;
//     END WHILE;

//     -- ULTIMA ITERACION PARA EVITAR ERROR DEL FROM
//         SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//         -- realizar alguna operación con cada elemento del arreglo
//     	SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' AND calificaciones.anio_id = ',idGestion,'  THEN
//                              (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                              ELSE NULL END) as ',elemento,' ');
//         SET @sql = CONCAT(@ini,_result,@fin);
//       PREPARE stmt FROM @sql;
//       EXECUTE stmt;
//       DEALLOCATE PREPARE stmt;
// END;

//     $$






//------------NEW NOVIEMBRE 2024. CODIGO TOMANDO EN CUENTA LA MALLA PARA EVITAR PROBLEMAS///data: aumentar el "SinSexo" al final del nombre y quitar el 'estudiantes.Sexo,'
// -- drop PROCEDURE getCentralizadorFinalLCCHFinish;
// DELIMITER ;
// DELIMITER $$
// CREATE OR REPLACE PROCEDURE getCentralizadorFinalLCCHFinish(IN arreglo TEXT,IN idEst INT, IN nombreCurso VARCHAR(255),IN idGestion INT,IN Mallatxt VARCHAR(255))
// BEGIN

//   DECLARE _result varchar(10000) DEFAULT '';
//   -- DECLARE _value varchar(50);
// DECLARE i INT DEFAULT 0;
// DECLARE elemento VARCHAR(255);
// DECLARE numElementos INT;
// SET numElementos = JSON_LENGTH(arreglo);


// SET @ini = 'SELECT estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre, estudiantes.CI,estudiantes.id,estudiantes.Especialidad,estudiantes.Sexo,';
// SET @fin = CONCAT('FROM estudiantes LEFT JOIN calificaciones ON estudiantes.id = calificaciones.estudiante_id LEFT JOIN cursos ON calificaciones.curso_id = cursos.id where cursos.NivelCurso="',nombreCurso,'" AND cursos.Malla="',Mallatxt,'" AND estudiantes.id="',idEst,'" AND calificaciones.anio_id="',idGestion,'" ORDER BY estudiantes.Ap_Paterno,estudiantes.Ap_Materno,estudiantes.Nombre, estudiante_id, cursos.Sigla;');

// WHILE i < numElementos-1 DO
//     SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//     -- realizar alguna operación con cada elemento del arreglo
//     SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' AND calificaciones.anio_id = ',idGestion,'  THEN
//                           (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                          ELSE NULL END) as ',elemento,',');

//     -- SELECT elemento;
//     SET i = i + 1;
// END WHILE;

// -- ULTIMA ITERACION PARA EVITAR ERROR DEL FROM
//     SET elemento = JSON_EXTRACT(arreglo, CONCAT('$[', i, ']'));
//     -- realizar alguna operación con cada elemento del arreglo
//     SET _result = CONCAT(_result,'SUM(CASE WHEN cursos.NombreCurso=',elemento,' AND estudiantes.id = ',idEst,' AND calificaciones.anio_id = ',idGestion,'  THEN
//                          (IF (calificaciones.PruebaRecuperacion IS NULL,calificaciones.Promedio ,calificaciones.PruebaRecuperacion ))
//                          ELSE NULL END) as ',elemento,' ');
//     SET @sql = CONCAT(@ini,_result,@fin);
//   PREPARE stmt FROM @sql;
//   EXECUTE stmt;
//   DEALLOCATE PREPARE stmt;
// END;

// $$
    }
    public function EncontrarNivelCurso(Request $request,$id)
    {

        //VARIABLES DE SESSION
        // session(['idCarroCompra' => '15320']); //GUARDAR
        // $valor_almacenado = session('idCarroCompra'); //OBTENER

        //VARIABLES DE SESSION PERO EN MODO ANGULAR
        // sessionStorage.setItem('Nombre', 'Miguel Antonio') //GUARDAR
        // sessionStorage.Apellido = 'Márquez Montoya' //GUARDAR
        //OBTENER
        // let firstName = sessionStorage.getItem('Nombre'),
        // lastName  = sessionStorage.Apellido


        //SELECT `cursos`.*, estudiantes.Ap_Paterno, estudiantes.Ap_Materno, estudiantes.Nombre FROM `cursos`	LEFT JOIN `calificaciones` ON `calificaciones`.`curso_id` = `cursos`.`id` LEFT JOIN `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id` WHERE estudiantes.id = 83
        $CursoData = DB::select("SELECT `cursos`.`NivelCurso`,estudiantes.id,anios.id,anios.Anio FROM `cursos` LEFT JOIN `calificaciones` ON `calificaciones`.`curso_id` = `cursos`.`id` LEFT JOIN `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id` LEFT JOIN `anios` ON `calificaciones`.`anio_id` = `anios`.`id` WHERE estudiantes.id = $id AND anios.Anio=2022 LIMIT 1");
        $NivelCursoObtenido = $CursoData;



        // $CalificacionesData = Calificaciones::where('estudiante_id','=', $id)->first();
        // $idCursoObtenido = $CalificacionesData->curso_id;
        // $CursoData = Curso::where('id','=', $idCursoObtenido)->first();
        // $NivelCursoObtenido = $CursoData->NivelCurso;
        // return $id;
        // session(['SessionNivel' => $NivelCursoObtenido]);
        return $NivelCursoObtenido;
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

        // $data = $request;
        // foreach ($data as $k) {
        //     Calificaciones::insert($k);
        // }
        $data = $request->toArray();
        for ($i=0; $i < sizeof($data); $i++) {
            // $fila = $request[$i];
             Calificaciones::insert($data[$i]);
        }

        return $request;
        // return 'calificacion creado';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Calificaciones  $calificaciones
     * @return \Illuminate\Http\Response
     */
    public function show(Calificaciones $calificaciones)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Calificaciones  $calificaciones
     * @return \Illuminate\Http\Response
     */
    public function edit(Calificaciones $calificaciones)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Calificaciones  $calificaciones
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $requestData = $request->all();

        Calificaciones::where('id','=',$id)->update($requestData);

        return $requestData;
    }
    public function ActualizarCalificacionesCurso(Request $request)
    {
        $dataCalificaciones = $request->all();
        // for ($i=0; $i < count($dataCalificaciones); $i++) {

        // }
        foreach ($dataCalificaciones as $c ) {
                // $calif = new Calificaciones();
            $getEst=Estudiantes::where('CI', '=', $c['CI'])->firstOrFail();
            $getIDCalif= Calificaciones::where('anio_id','=',$c['anio_id'])->where('curso_id','=',$c['curso_id'])->where('estudiante_id','=',$getEst['id'])->firstOrFail();
            // $Carnet = $getIDCalif;
            // $Carnet = $getIDCalif['id'];
            $evaluacion = $c['Evaluacion'];
            try {
                switch ($evaluacion) {
                    case 'PRIMERA EVALUACION':
                        $updateData = [
                            'Practica1' => intval($c['Practica1']),
                            'Teorica1' => intval($c['Teorica1']),
                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];

                        break;
                    case 'SEGUNDA EVALUACION':
                        $updateData = [
                            'Practica2' => intval($c['Practica2']),
                            'Teorica2' => intval($c['Teorica2']),
                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];
                        break;
                    case 'TERCERA EVALUACION':
                        $updateData = [
                            'Practica3' => intval($c['Practica3']),
                            'Teorica3' => intval($c['Teorica3']),
                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];
                        break;
                    case 'CUARTA EVALUACION':
                        $updateData = [
                            'Practica4' => intval($c['Practica4']),
                            'Teorica4' => intval($c['Teorica4']),
                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];
                        break;
                    case 'SEGUNDA INSTANCIA':
                        $updateData = [
                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'PruebaRecuperacion' => $c['PruebaRecuperacion'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];
                        break;
                    case 'SEMESTRALIZADO':
                        $updateData = [

                            'PromEvP' => $c['PromEvP'],
                            'PromEvT' => $c['PromEvT'],
                            'Promedio' => $c['Promedio'],
                            'PruebaRecuperacion' => $c['PruebaRecuperacion'],
                            'anio_id' => intval($c['anio_id']),
                            'curso_id' => $c['curso_id']
                        ];
                        break;
                    default:
                        # code...
                        break;
                }

                Calificaciones::where('id', $getIDCalif['id'])->update($updateData);
            } catch (\Throwable $th) {
                //NO SE ENCONTRO EL CARNET; PERO SEGUIMOS SGTE ITERACION
                continue;
            }



        }
        return $dataCalificaciones;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Calificaciones  $calificaciones
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data =  DB::select("delete from calificaciones where id='$id'");
        return response()->json(["mensaje" => "calificaciones Eliminado Correctamente"], 200);
    }
    public function EliminarEstudianteDelCurso(Request $request)
    {
        $idEst = $request->input('idEst');
        $Anio_id = $request->input('Anio_id');
        Calificaciones::where('estudiante_id',$idEst)->where('anio_id',$Anio_id)->delete();
        return 'Eliminacion del Estudiante Curso Correcto';
    }
}
