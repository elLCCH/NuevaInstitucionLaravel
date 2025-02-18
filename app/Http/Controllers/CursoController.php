<?php

namespace App\Http\Controllers;

use App\Models\Administrativos;
use App\Models\Calificaciones;
use App\Models\Curso;
use App\Models\Estudiantes;
use App\Models\Administrativos_Cursos;
use App\Models\Prerrequisitos;
use App\Models\Anio;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $Curso = Curso::all();


        $Malla = $request->query('Malla');
        $Anio_id = $request->query('Anio_id');
        if ($Malla =='nulo') {
            $data = Curso::whereRaw('Anio_id=?',$Anio_id)->orderBy('NivelCurso','desc')->orderBy('Rango','asc')->get();
        }else{
            $data = Curso::whereRaw('Anio_id=?',$Anio_id)->whereRaw('Malla=?',$Malla)->orderBy('NivelCurso','desc')->orderBy('Rango','asc')->get();
        }

        return $data;
    }
    public function RespaldarSiglas(Request $request)
    {
        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');
        DB::select("update cursos set SiglaRespaldo = Sigla where Anio_id=$Anio_id and Malla='$Malla'");
        return 'SE RESPALDO CORRECTAMENTE';

    }

    public function ClonarGestion(Request $request)
    {
        $Anio_idinput = $request->input('Anio_id'); // GESTION A CLONAR
        $New_Anio_idinput = $request->input('New_Anio_id'); // NUEVA GESTION

        // CLONANDO MATERIAS PARA NUEVA GESTION
        $dataMateriass = curso::where('Anio_id', '=', $Anio_idinput)->get();

        foreach ($dataMateriass as $c) {
            $curso = new curso();
            $curso->NombreCurso = $c->NombreCurso;
            $curso->NivelCurso = $c->NivelCurso;
            $curso->Sigla = $c->Sigla;
            $curso->Tipo = $c->Tipo;
            $curso->BiTriEstado = 'NINGUNA EVALUACION';
            $curso->Horas = $c->Horas;
            $curso->Malla = $c->Malla;
            $curso->Rango = $c->Rango;
            $curso->Anio_id = $New_Anio_idinput;
            $curso->save();

            // Mapear los nuevos IDs de las materias clonadas
            $materiasClonadas[$c->id] = $curso->id;
        }

        // Clonar prerrequisitos
        $PrerreqAnteriorGestion = Prerrequisitos::where('Anio_id', '=', $Anio_idinput)->get();

        foreach ($PrerreqAnteriorGestion as $r) {
            // Obtener el nuevo ID de la materia primaria
            $id_materia_p_new = $materiasClonadas[$r->id_materia_p];

            // Obtener el nuevo ID de la materia secundaria
            $id_materia_s_new = $materiasClonadas[$r->id_materia_s];

            // Obtener la malla de la materia primaria clonada
            $malla_primaria_new = $r->Malla;

            $Prerreq = new Prerrequisitos();
            $Prerreq->id_materia_p = $id_materia_p_new;
            $Prerreq->id_materia_s = $id_materia_s_new;
            $Prerreq->Malla = $malla_primaria_new; // Asignar la malla
            $Prerreq->Anio_id = $New_Anio_idinput;
            $Prerreq->save();
        }

        // return 'CLONACION EXITOSA';
        return $dataMateriass; // O cualquier otro dato que desees devolver
    }

    public function CursosUniqueSigla()
    {
        $dataUnique=DB::select("SELECT DISTINCT Sigla, NombreCurso FROM cursos ORDER BY Sigla");
        $Lista = array();
        foreach ($dataUnique as $C) {
            $data = Curso::where('Sigla','=', $C->Sigla)->first();
            $Lista[] = $data;
        }
        return $Lista;
    }
    public function MateriasxAnioMallaNivelCurso(Request $request)
    {
        //USADO PARA CARGAR TODAS LAS MATEREIAS DE UN ANIO MALLA NIVEL // USADO PARA ASIGNAR MATERIAS A LOS ESTUDIANTES
        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');
        $NivelCurso = $request->input('NivelCurso');
        // $NivelCurso = 'TERCERO BASICO B';

        $VerificarPorParalelo = $request->input('VerificarPorParalelo'); //SI ES TRUE ENTONCES VERIFICAREMOS POR PARALELO, DIGAMOS SI el B sus prerrequisitos los tiene con el C que asi sea, si es false entonces si o si con A
        $NivelCursoPeroEnA = substr($NivelCurso, 0, -1) . 'A'; //ENTONCES HACER LA VERIFICACION CON EL PARALELO A por defecto
        // if ($VerificarPorParalelo == false) {
        //     $NivelCursoPeroEnA = substr($NivelCurso, 0, -1) . 'A'; //ENTONCES HACER LA VERIFICACION CON EL PARALELO A por defecto
        // }else{
        //     $NivelCursoPeroEnA = $NivelCurso; //ENTONCES VERIFICAR PRERREQUISITOS CON EL PARALELO ASIGNADO B con B sino B con C etc
        // }
        $EsPrimerAnio = $request->input('EsPrimerAnio'); //PARA SABER SI SON MATERIAS DE PRIMER AÑO QUE NO TIENEN PRERREQUISITO

        //OBTENCION DE MATERIAS DE UN CURSO EJEM: MATERIAS DE PRIMERO BASICO A, PRIMERO BASICO B, PRIMERO SUPERIOR B ETC
        $materias = DB::select("SELECT cursos.id,`cursos`.`Sigla` as 'cod_prin',cursos.Anio_id,cursos.NivelCurso as 'CursoP',cursos.Malla,cursos.NombreCurso as 'mat_prin',
        cursos.id as 'id_materia_p',prerrequisitos.id_materia_s,(select c.Sigla from cursos c where c.id=prerrequisitos.id_materia_s and prerrequisitos.Malla='$Malla' and prerrequisitos.Anio_id='$Anio_id') as 'cod_sec',(select cc.SiglaRespaldo from cursos cc where cc.id=prerrequisitos.id_materia_s) as 'cod_secRespaldo'
                FROM `cursos`
                    LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id` where cursos.Anio_id=$Anio_id and cursos.Malla='$Malla' and cursos.NivelCurso = '$NivelCurso'");
        $materiasRespaldo = $materias;
        $materiasRespaldo1 = $materias;
        $materiasRespaldo2 = $materias;

        if ($VerificarPorParalelo == false && $EsPrimerAnio != 'PRIMER ANIO') { //osea nocheck nocheck
            // ENTONCES VER CON PARALELO A
            $materias2 = DB::select("SELECT
            cursos.id,
            `cursos`.`Sigla` as 'cod_prin',
            cursos.Anio_id,
            cursos.NivelCurso as 'CursoP',
            cursos.Malla,
            cursos.NombreCurso as 'mat_prin',
            cursos.id as 'id_materia_p',
            (
                SELECT
                    GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
                FROM
                    prerrequisitos p
                WHERE
                    p.id_materia_p = (
                        SELECT
                            c.id
                        FROM
                            cursos c
                        WHERE
                            c.Sigla = cursos.Sigla
                            AND c.Anio_id = $Anio_id
                        LIMIT 1
                    )
                LIMIT 1
            ) AS 'id_materia_sec',
            (
                SELECT
                    GROUP_CONCAT(c.Sigla)
                FROM
                    cursos c
                WHERE
                    c.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_sec',
            (
                SELECT
                    GROUP_CONCAT(cc.SiglaRespaldo)
                FROM
                    cursos cc
                WHERE
                    cc.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_secRespaldo'
            FROM
                `cursos`
            LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            WHERE
                cursos.Anio_id = $Anio_id
                AND cursos.Malla = '$Malla'
                AND cursos.NivelCurso = '$NivelCursoPeroEnA';
            ");
            //CODIGO QUE HACE Q SE DETECTEN MATERIAS DE OTROS PARALELOS
            // Crear un mapa basado en el código principal para $materias2
            $materias2Map = [];
            foreach ($materias2 as $mat2) {
                $materias2Map[$mat2->cod_prin] = $mat2->cod_sec;
            }

            // Actualizar $materias con los prerrequisitos correspondientes de $materias2
            foreach ($materias as &$mat1) {
                // Verificar si el código principal existe en $materias2Map
                if (isset($materias2Map[$mat1->cod_prin])) {
                    // Asignar prerrequisitos del paralelo A a $prerrequisitosParaleloA
                    $prerrequisitosParaleloA = $materias2Map[$mat1->cod_prin];

                    // Actualizar el atributo cod_sec con los prerrequisitos del paralelo A
                    $mat1->cod_sec = $prerrequisitosParaleloA;
                }
            }
        }else if ($VerificarPorParalelo == false && $EsPrimerAnio == 'PRIMER ANIO') { //osea nocheck check
            $materias2 = DB::select("SELECT
            cursos.id,
            `cursos`.`Sigla` as 'cod_prin',
            cursos.Anio_id,
            cursos.NivelCurso as 'CursoP',
            cursos.Malla,
            cursos.NombreCurso as 'mat_prin',
            cursos.id as 'id_materia_p',
            (
                SELECT
                    GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
                FROM
                    prerrequisitos p
                WHERE
                    p.id_materia_p = (
                        SELECT
                            c.id
                        FROM
                            cursos c
                        WHERE
                            c.Sigla = cursos.Sigla
                            AND c.Anio_id = $Anio_id
                        LIMIT 1
                    )
                LIMIT 1
            ) AS 'id_materia_sec',
            (
                SELECT
                    GROUP_CONCAT(c.Sigla)
                FROM
                    cursos c
                WHERE
                    c.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_sec',
            (
                SELECT
                    GROUP_CONCAT(cc.SiglaRespaldo)
                FROM
                    cursos cc
                WHERE
                    cc.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_secRespaldo'
            FROM
                `cursos`
            LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            WHERE
                cursos.Anio_id = $Anio_id
                AND cursos.Malla = '$Malla'
                AND cursos.NivelCurso = '$NivelCurso';
            ");
            // //VERIFICAR SI HAY NULOS
            // $materiasSinNulos = array_values(array_filter($materias, function ($materia) {
            //     return $materia->cod_sec !== null;
            // }));

            // // SI ES VACIO ENTONCES VERIFICAR CON PARALELO A
            // if (empty($materiasSinNulos)) {
            //     // Obtener prerrequisitos del mismo nivel pero en paralelo A



            // }
            // Crear un mapa basado en el código principal para $materias2
            $materias2Map = [];
            foreach ($materias2 as $mat2) {
                $materias2Map[$mat2->cod_prin] = '';
            }

            // Actualizar $materias con los prerrequisitos correspondientes de $materias2
            foreach ($materias as &$mat1) {
                // Verificar si el código principal existe en $materias2Map
                if (isset($materias2Map[$mat1->cod_prin])) {
                    // Asignar prerrequisitos del paralelo A a $prerrequisitosParaleloA
                    $prerrequisitosParaleloA = $materias2Map[$mat1->cod_prin];

                    // Actualizar el atributo cod_sec con los prerrequisitos del paralelo A
                    $mat1->cod_sec = $prerrequisitosParaleloA;
                }
            }
        }else if ($VerificarPorParalelo == true && $EsPrimerAnio != 'PRIMER ANIO') { //osea check nocheck
            // ENTONCES VER CON PARALELO A
            $materias2 = DB::select("SELECT
            cursos.id,
            `cursos`.`Sigla` as 'cod_prin',
            cursos.Anio_id,
            cursos.NivelCurso as 'CursoP',
            cursos.Malla,
            cursos.NombreCurso as 'mat_prin',
            cursos.id as 'id_materia_p',
            (
                SELECT
                    GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
                FROM
                    prerrequisitos p
                WHERE
                    p.id_materia_p = (
                        SELECT
                            c.id
                        FROM
                            cursos c
                        WHERE
                            c.Sigla = cursos.Sigla
                            AND c.Anio_id = $Anio_id
                        LIMIT 1
                    )
                LIMIT 1
            ) AS 'id_materia_sec',
            (
                SELECT
                    GROUP_CONCAT(c.Sigla)
                FROM
                    cursos c
                WHERE
                    c.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_sec',
            (
                SELECT
                    GROUP_CONCAT(cc.SiglaRespaldo)
                FROM
                    cursos cc
                WHERE
                    cc.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_secRespaldo'
            FROM
                `cursos`
            LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            WHERE
                cursos.Anio_id = $Anio_id
                AND cursos.Malla = '$Malla'
                AND cursos.NivelCurso = '$NivelCurso';
            ");
            //CODIGO QUE HACE Q SE DETECTEN MATERIAS DE OTROS PARALELOS
            // Crear un mapa basado en el código principal para $materias2
            $materias2Map = [];
            foreach ($materias2 as $mat2) {
                $materias2Map[$mat2->cod_prin] = $mat2->cod_sec;
            }

            // Actualizar $materias con los prerrequisitos correspondientes de $materias2
            foreach ($materias as &$mat1) {
                // Verificar si el código principal existe en $materias2Map
                if (isset($materias2Map[$mat1->cod_prin])) {
                    // Asignar prerrequisitos del paralelo A a $prerrequisitosParaleloA
                    $prerrequisitosParaleloA = $materias2Map[$mat1->cod_prin];

                    // Actualizar el atributo cod_sec con los prerrequisitos del paralelo A
                    $mat1->cod_sec = $prerrequisitosParaleloA;
                }
            }
        }else if ($VerificarPorParalelo == true && $EsPrimerAnio == 'PRIMER ANIO') { //osea check check
            $materias2 = DB::select("SELECT
            cursos.id,
            `cursos`.`Sigla` as 'cod_prin',
            cursos.Anio_id,
            cursos.NivelCurso as 'CursoP',
            cursos.Malla,
            cursos.NombreCurso as 'mat_prin',
            cursos.id as 'id_materia_p',
            (
                SELECT
                    GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
                FROM
                    prerrequisitos p
                WHERE
                    p.id_materia_p = (
                        SELECT
                            c.id
                        FROM
                            cursos c
                        WHERE
                            c.Sigla = cursos.Sigla
                            AND c.Anio_id = $Anio_id
                        LIMIT 1
                    )
                LIMIT 1
            ) AS 'id_materia_sec',
            (
                SELECT
                    GROUP_CONCAT(c.Sigla)
                FROM
                    cursos c
                WHERE
                    c.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_sec',
            (
                SELECT
                    GROUP_CONCAT(cc.SiglaRespaldo)
                FROM
                    cursos cc
                WHERE
                    cc.id IN (
                        SELECT p.id_materia_s
                        FROM prerrequisitos p
                        WHERE p.id_materia_p = cursos.id
                            AND prerrequisitos.Malla = '$Malla'
                            AND prerrequisitos.Anio_id = '$Anio_id'
                    )
            ) as 'cod_secRespaldo'
            FROM
                `cursos`
            LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            WHERE
                cursos.Anio_id = $Anio_id
                AND cursos.Malla = '$Malla'
                AND cursos.NivelCurso = '$NivelCurso';
            ");
            // //VERIFICAR SI HAY NULOS
            // $materiasSinNulos = array_values(array_filter($materias, function ($materia) {
            //     return $materia->cod_sec !== null;
            // }));

            // // SI ES VACIO ENTONCES VERIFICAR CON PARALELO A
            // if (empty($materiasSinNulos)) {
            //     // Obtener prerrequisitos del mismo nivel pero en paralelo A



            // }
            // Crear un mapa basado en el código principal para $materias2
            $materias2Map = [];
            foreach ($materias2 as $mat2) {
                $materias2Map[$mat2->cod_prin] = '';
            }

            // Actualizar $materias con los prerrequisitos correspondientes de $materias2
            foreach ($materias as &$mat1) {
                // Verificar si el código principal existe en $materias2Map
                if (isset($materias2Map[$mat1->cod_prin])) {
                    // Asignar prerrequisitos del paralelo A a $prerrequisitosParaleloA
                    $prerrequisitosParaleloA = $materias2Map[$mat1->cod_prin];

                    // Actualizar el atributo cod_sec con los prerrequisitos del paralelo A
                    $mat1->cod_sec = $prerrequisitosParaleloA;
                }
            }
        }
        // EXCLUSIÓN DE FILAS CON PRIMERA LETRA DE cod_sec COMO COMA
        $materias = array_values(array_filter($materiasRespaldo, function ($materia) {
            return $materia->cod_sec !== null && substr($materia->cod_sec, 0, 1) !== ',';
        }));
        ////////////////////////////
        // if ($VerificarPorParalelo == false) { //SI "VERIFICAR PRERREQUISITOS POR PARALELO" ES FALSE, ENTONCES VERIFICAMOS CON PARALELO A
        //     $materiasRespaldo = DB::select("SELECT
        //         cursos.id,
        //         `cursos`.`Sigla` as 'cod_prin',
        //         cursos.Anio_id,
        //         cursos.NivelCurso as 'CursoP',
        //         cursos.Malla,
        //         cursos.NombreCurso as 'mat_prin',
        //         cursos.id as 'id_materia_p',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
        //             FROM
        //                 prerrequisitos p
        //             WHERE
        //                 p.id_materia_p = (
        //                     SELECT
        //                         c.id
        //                     FROM
        //                         cursos c
        //                     WHERE
        //                         c.Sigla = cursos.Sigla
        //                         AND c.Anio_id = $Anio_id
        //                     LIMIT 1
        //                 )
        //             LIMIT 1
        //         ) AS 'id_materia_sec',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(c.Sigla)
        //             FROM
        //                 cursos c
        //             WHERE
        //                 c.id IN (
        //                     SELECT p.id_materia_s
        //                     FROM prerrequisitos p
        //                     WHERE p.id_materia_p = cursos.id
        //                         AND prerrequisitos.Malla = '$Malla'
        //                         AND prerrequisitos.Anio_id = '$Anio_id'
        //                 )
        //         ) as 'cod_sec',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(cc.SiglaRespaldo)
        //             FROM
        //                 cursos cc
        //             WHERE
        //                 cc.id IN (
        //                     SELECT p.id_materia_s
        //                     FROM prerrequisitos p
        //                     WHERE p.id_materia_p = cursos.id
        //                         AND prerrequisitos.Malla = '$Malla'
        //                         AND prerrequisitos.Anio_id = '$Anio_id'
        //                 )
        //         ) as 'cod_secRespaldo'
        //     FROM
        //         `cursos`
        //     LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
        //     WHERE
        //         cursos.Anio_id = $Anio_id
        //         AND cursos.Malla = '$Malla'
        //         AND cursos.NivelCurso = '$NivelCursoPeroEnA';
        //     ");
        //     if ($EsPrimerAnio != 'PRIMER ANIO'){
        //         //NO ES SU PRIMERO AÑO
        //         // //EXCLUCION DE NULL EN cod_sec
        //         // $materiasSinNulos = array_values(array_filter($materiasRespaldo, function ($materia) {
        //         //     return $materia->cod_sec !== null;
        //         // }));

        //     }else{
        //         //SI ES SU PRIMER ANIO APLICAR su MISMO PARALELO
        //         $materiasRespaldo1 = DB::select("SELECT
        //         cursos.id,
        //         `cursos`.`Sigla` as 'cod_prin',
        //         cursos.Anio_id,
        //         cursos.NivelCurso as 'CursoP',
        //         cursos.Malla,
        //         cursos.NombreCurso as 'mat_prin',
        //         cursos.id as 'id_materia_p',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
        //             FROM
        //                 prerrequisitos p
        //             WHERE
        //                 p.id_materia_p = (
        //                     SELECT
        //                         c.id
        //                     FROM
        //                         cursos c
        //                     WHERE
        //                         c.Sigla = cursos.Sigla
        //                         AND c.Anio_id = $Anio_id
        //                     LIMIT 1
        //                 )
        //             LIMIT 1
        //         ) AS 'id_materia_sec',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(c.Sigla)
        //             FROM
        //                 cursos c
        //             WHERE
        //                 c.id IN (
        //                     SELECT p.id_materia_s
        //                     FROM prerrequisitos p
        //                     WHERE p.id_materia_p = cursos.id
        //                         AND prerrequisitos.Malla = '$Malla'
        //                         AND prerrequisitos.Anio_id = '$Anio_id'
        //                 )
        //         ) as 'cod_sec',
        //         (
        //             SELECT
        //                 GROUP_CONCAT(cc.SiglaRespaldo)
        //             FROM
        //                 cursos cc
        //             WHERE
        //                 cc.id IN (
        //                     SELECT p.id_materia_s
        //                     FROM prerrequisitos p
        //                     WHERE p.id_materia_p = cursos.id
        //                         AND prerrequisitos.Malla = '$Malla'
        //                         AND prerrequisitos.Anio_id = '$Anio_id'
        //                 )
        //         ) as 'cod_secRespaldo'
        //         FROM
        //             `cursos`
        //         LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
        //         WHERE
        //             cursos.Anio_id = $Anio_id
        //             AND cursos.Malla = '$Malla'
        //             AND cursos.NivelCurso = '$NivelCurso';
        //         ");
        //         $materiasRespaldo= $materiasRespaldo1;
        //     }
        //     // EXCLUSIÓN DE FILAS CON PRIMERA LETRA DE cod_sec COMO COMA
        //     $materias = array_values(array_filter($materiasRespaldo, function ($materia) {
        //         return $materia->cod_sec !== null && substr($materia->cod_sec, 0, 1) !== ',';
        //     }));

        // }else{
        //     //PASA POR AQUI SI "VERIFICAR PRERREQUISITOS POR PARALELO" ES TRUE, ENTONCES HACER LA VERIFICACION CON B a B, B a C, B a A ... EN SI LA CONFIGURACION MANUAL

        //     $materiasRespaldo = DB::select("SELECT
        //     cursos.id,
        //     `cursos`.`Sigla` as 'cod_prin',
        //     cursos.Anio_id,
        //     cursos.NivelCurso as 'CursoP',
        //     cursos.Malla,
        //     cursos.NombreCurso as 'mat_prin',
        //     cursos.id as 'id_materia_p',
        //     (
        //         SELECT
        //             GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
        //         FROM
        //             prerrequisitos p
        //         WHERE
        //             p.id_materia_p = (
        //                 SELECT
        //                     c.id
        //                 FROM
        //                     cursos c
        //                 WHERE
        //                     c.Sigla = cursos.Sigla
        //                     AND c.Anio_id = $Anio_id
        //                 LIMIT 1
        //             )
        //         LIMIT 1
        //     ) AS 'id_materia_sec',
        //     (
        //         SELECT
        //             GROUP_CONCAT(c.Sigla)
        //         FROM
        //             cursos c
        //         WHERE
        //             c.id IN (
        //                 SELECT p.id_materia_s
        //                 FROM prerrequisitos p
        //                 WHERE p.id_materia_p = cursos.id
        //                     AND prerrequisitos.Malla = '$Malla'
        //                     AND prerrequisitos.Anio_id = '$Anio_id'
        //             )
        //     ) as 'cod_sec',
        //     (
        //         SELECT
        //             GROUP_CONCAT(cc.SiglaRespaldo)
        //         FROM
        //             cursos cc
        //         WHERE
        //             cc.id IN (
        //                 SELECT p.id_materia_s
        //                 FROM prerrequisitos p
        //                 WHERE p.id_materia_p = cursos.id
        //                     AND prerrequisitos.Malla = '$Malla'
        //                     AND prerrequisitos.Anio_id = '$Anio_id'
        //             )
        //     ) as 'cod_secRespaldo'
        //     FROM
        //         `cursos`
        //     LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
        //     WHERE
        //         cursos.Anio_id = $Anio_id
        //         AND cursos.Malla = '$Malla'
        //         AND cursos.NivelCurso = '$NivelCurso';
        //     ");
        //     if ($EsPrimerAnio != 'PRIMER ANIO'){
        //         //EXCLUCION DE NULL EN cod_sec
        //         $materiasSinNulos = array_values(array_filter($materiasRespaldo, function ($materia) {
        //             return $materia->cod_sec !== null;
        //         }));
        //     }
        //     // EXCLUSIÓN DE FILAS CON PRIMERA LETRA DE cod_sec COMO COMA
        //     $materias = array_values(array_filter($materiasRespaldo, function ($materia) {
        //         return $materia->cod_sec !== null && substr($materia->cod_sec, 0, 1) !== ',';
        //     }));
        // }
        // $materias = $materiasRespaldo;







            // //OBTENIENDO PRERREQUISITOS RESPECTO A NIVEL CURSO PRIMERO BASICO A, PRIMERO BASICO B, PRIMERO SUPERIOR B ETC
            // $materias = DB::select("SELECT
            //     cursos.id,
            //     `cursos`.`Sigla` as 'cod_prin',
            //     cursos.Anio_id,
            //     cursos.NivelCurso as 'CursoP',
            //     cursos.Malla,
            //     cursos.NombreCurso as 'mat_prin',
            //     cursos.id as 'id_materia_p',
            //     (
            //         SELECT
            //             GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
            //         FROM
            //             prerrequisitos p
            //         WHERE
            //             p.id_materia_p = (
            //                 SELECT
            //                     c.id
            //                 FROM
            //                     cursos c
            //                 WHERE
            //                     c.Sigla = cursos.Sigla
            //                     AND c.Anio_id = $Anio_id
            //                 LIMIT 1
            //             )
            //         LIMIT 1
            //     ) AS 'id_materia_sec',
            //     (
            //         SELECT
            //             GROUP_CONCAT(c.Sigla)
            //         FROM
            //             cursos c
            //         WHERE
            //             c.id IN (
            //                 SELECT p.id_materia_s
            //                 FROM prerrequisitos p
            //                 WHERE p.id_materia_p = cursos.id
            //                     AND prerrequisitos.Malla = '$Malla'
            //                     AND prerrequisitos.Anio_id = '$Anio_id'
            //             )
            //     ) as 'cod_sec',
            //     (
            //         SELECT
            //             GROUP_CONCAT(cc.SiglaRespaldo)
            //         FROM
            //             cursos cc
            //         WHERE
            //             cc.id IN (
            //                 SELECT p.id_materia_s
            //                 FROM prerrequisitos p
            //                 WHERE p.id_materia_p = cursos.id
            //                     AND prerrequisitos.Malla = '$Malla'
            //                     AND prerrequisitos.Anio_id = '$Anio_id'
            //             )
            //     ) as 'cod_secRespaldo'
            // FROM
            //     `cursos`
            // LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            // WHERE
            //     cursos.Anio_id = $Anio_id
            //     AND cursos.Malla = '$Malla'
            //     AND cursos.NivelCurso = '$NivelCurso';
            // ");
            // if ($EsPrimerAnio == 'PRIMER ANIO') { //SI ES PRIMER AÑO ENTONCES NO EXCLUIR NULOS NI NADA //NOTA: ESTA PALABRA PRIMER ANIO ES GENERAL PARA TODOS LOS INSTITUTOS

            //     //SI ES PRIMER AÑO ENTONCES HACER VERIFICACION CON PARALELO A
            //     $materiasRespaldo = DB::select("SELECT
            //     cursos.id,
            //     `cursos`.`Sigla` as 'cod_prin',
            //     cursos.Anio_id,
            //     cursos.NivelCurso as 'CursoP',
            //     cursos.Malla,
            //     cursos.NombreCurso as 'mat_prin',
            //     cursos.id as 'id_materia_p',
            //     (
            //         SELECT
            //             GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
            //         FROM
            //             prerrequisitos p
            //         WHERE
            //             p.id_materia_p = (
            //                 SELECT
            //                     c.id
            //                 FROM
            //                     cursos c
            //                 WHERE
            //                     c.Sigla = cursos.Sigla
            //                     AND c.Anio_id = $Anio_id
            //                 LIMIT 1
            //             )
            //         LIMIT 1
            //     ) AS 'id_materia_sec',
            //     (
            //         SELECT
            //             GROUP_CONCAT(c.Sigla)
            //         FROM
            //             cursos c
            //         WHERE
            //             c.id IN (
            //                 SELECT p.id_materia_s
            //                 FROM prerrequisitos p
            //                 WHERE p.id_materia_p = cursos.id
            //                     AND prerrequisitos.Malla = '$Malla'
            //                     AND prerrequisitos.Anio_id = '$Anio_id'
            //             )
            //     ) as 'cod_sec',
            //     (
            //         SELECT
            //             GROUP_CONCAT(cc.SiglaRespaldo)
            //         FROM
            //             cursos cc
            //         WHERE
            //             cc.id IN (
            //                 SELECT p.id_materia_s
            //                 FROM prerrequisitos p
            //                 WHERE p.id_materia_p = cursos.id
            //                     AND prerrequisitos.Malla = '$Malla'
            //                     AND prerrequisitos.Anio_id = '$Anio_id'
            //             )
            //     ) as 'cod_secRespaldo'
            // FROM
            //     `cursos`
            // LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            // WHERE
            //     cursos.Anio_id = $Anio_id
            //     AND cursos.Malla = '$Malla'
            //     AND cursos.NivelCurso = '$NivelCursoPeroEnA';
            // ");
            // $materias = $materiasRespaldo;
            // }else{
            //     //EXCLUCION DE NULL EN cod_sec
            //     $materiasSinNulos = array_values(array_filter($materias, function ($materia) {
            //         return $materia->cod_sec !== null;
            //     }));

            //     if ($VerificarPorParalelo == false) {
            //         //OBLIGANDO A QUE SEA EMPTY ---- SI OBLIGAMOS A QUE SEA NULL ESTMOS HACIENDO QUE SI O SI SUS PRERREQ SEAN TOMADOS DEL PARALELO A
            //         $materiasSinNulos = null;
            //     }



            //     //VERIFICAR SI $materiasSinNulos ESTA VACIO
            //     //Si ESTA VACIO el $materiasSinNulos podemos recorrer con un foreach las filas de $materias,
            //     //al recorrer fila por fila debemos reemplazar los cod_sec que eran nulos por los prerrequisitos del mismo Nivel pero en paralelo A
            //     //ahora recorriendo fila a fila obviamente mantenemos los cursos del $NivelCurso original, pero estaria con los prerrequisitos cod_sec como si fuera paralelo A
            //     if (empty($materiasSinNulos)) {
            //         // Obtener prerrequisitos del mismo nivel pero en paralelo A
            //         $materias2 = DB::select("SELECT
            //         cursos.id,
            //         `cursos`.`Sigla` as 'cod_prin',
            //         cursos.Anio_id,
            //         cursos.NivelCurso as 'CursoP',
            //         cursos.Malla,
            //         cursos.NombreCurso as 'mat_prin',
            //         cursos.id as 'id_materia_p',
            //         (
            //             SELECT
            //                 GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
            //             FROM
            //                 prerrequisitos p
            //             WHERE
            //                 p.id_materia_p = (
            //                     SELECT
            //                         c.id
            //                     FROM
            //                         cursos c
            //                     WHERE
            //                         c.Sigla = cursos.Sigla
            //                         AND c.Anio_id = $Anio_id
            //                     LIMIT 1
            //                 )
            //             LIMIT 1
            //         ) AS 'id_materia_sec',
            //         (
            //             SELECT
            //                 GROUP_CONCAT(c.Sigla)
            //             FROM
            //                 cursos c
            //             WHERE
            //                 c.id IN (
            //                     SELECT p.id_materia_s
            //                     FROM prerrequisitos p
            //                     WHERE p.id_materia_p = cursos.id
            //                         AND prerrequisitos.Malla = '$Malla'
            //                         AND prerrequisitos.Anio_id = '$Anio_id'
            //                 )
            //         ) as 'cod_sec',
            //         (
            //             SELECT
            //                 GROUP_CONCAT(cc.SiglaRespaldo)
            //             FROM
            //                 cursos cc
            //             WHERE
            //                 cc.id IN (
            //                     SELECT p.id_materia_s
            //                     FROM prerrequisitos p
            //                     WHERE p.id_materia_p = cursos.id
            //                         AND prerrequisitos.Malla = '$Malla'
            //                         AND prerrequisitos.Anio_id = '$Anio_id'
            //                 )
            //         ) as 'cod_secRespaldo'
            //         FROM
            //             `cursos`
            //         LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            //         WHERE
            //             cursos.Anio_id = $Anio_id
            //             AND cursos.Malla = '$Malla'
            //             AND cursos.NivelCurso = '$NivelCursoPeroEnA';
            //         ");
            //         //CODIGO QUE HACE Q SE DETECTEN MATERIAS DE OTROS PARALELOS
            //         // Crear un mapa basado en el código principal para $materias2
            //         $materias2Map = [];
            //         foreach ($materias2 as $mat2) {
            //             $materias2Map[$mat2->cod_prin] = $mat2->cod_sec;
            //         }

            //         // Actualizar $materias con los prerrequisitos correspondientes de $materias2
            //         foreach ($materias as &$mat1) {
            //             // Verificar si el código principal existe en $materias2Map
            //             if (isset($materias2Map[$mat1->cod_prin])) {
            //                 // Asignar prerrequisitos del paralelo A a $prerrequisitosParaleloA
            //                 $prerrequisitosParaleloA = $materias2Map[$mat1->cod_prin];

            //                 // Actualizar el atributo cod_sec con los prerrequisitos del paralelo A
            //                 $mat1->cod_sec = $prerrequisitosParaleloA;
            //             }
            //         }


            //     }

            //     // EXCLUSIÓN DE FILAS CON PRIMERA LETRA DE cod_sec COMO COMA
            //     $materias = array_values(array_filter($materias, function ($materia) {
            //         return $materia->cod_sec !== null && substr($materia->cod_sec, 0, 1) !== ',';
            //     }));

            // }


        return $materias;
    }


    public function MateriasxAnioNivel(Request $request)
    {
        //USADO PARA CARGAR TODAS LAS MATEREIAS DE UN ANIO MALLA NIVEL // USADO PARA ASIGNAR MATERIAS A LOS ESTUDIANTES
        // $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');
        $NivelCurso = $request->input('NivelCurso');

        $materias = DB::select("SELECT cursos.id,`cursos`.`Sigla` as 'cod_prin',cursos.Anio_id,cursos.NivelCurso as 'CursoP',cursos.Malla,cursos.NombreCurso as 'mat_prin', cursos.Malla,
        cursos.id as 'id_materia_p',prerrequisitos.id_materia_s,(select c.Sigla from cursos c where c.id=prerrequisitos.id_materia_s) as 'cod_sec'
                FROM `cursos`
                    LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id` where cursos.Anio_id=$Anio_id and cursos.NivelCurso = '$NivelCurso'");
        $SiTienePrerreq = true;
        foreach ($materias as $k) {
            if ($k->cod_sec==null) {
                $SiTienePrerreq = false;
            }
        }
        if ($SiTienePrerreq ==false) {
            $materias = DB::select("SELECT cursos.id,`cursos`.`Sigla` as 'cod_prin',cursos.Anio_id,cursos.NivelCurso as 'CursoP',cursos.Malla,cursos.NombreCurso as 'mat_prin', cursos.Malla,
            cursos.id as 'id_materia_p',(SELECT p.id_materia_s as 'id_materia_s2' from prerrequisitos p where p.id_materia_p =  (select c.id from cursos c where c.Sigla=cursos.Sigla and c.NivelCurso NOT LIKE 'SEGUNDO BASICO B' and c.Anio_id=$Anio_id LIMIT 1)LIMIT 1) AS 'id_materia_sec',(select c.Sigla from cursos c where c.id=id_materia_sec) as 'cod_sec'
                    FROM `cursos`
                        LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id` where cursos.Anio_id=$Anio_id and cursos.NivelCurso = '$NivelCurso';
            ");
        }
        return $materias;
    }
    public function MateriasxAnioMalla(Request $request)
    {
        //USADO PARA CARGAR TODAS LAS MATEREIAS DE UN ANIO MALLA NIVEL // USADO PARA ASIGNAR MATERIAS A LOS ESTUDIANTES
        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');


        // $materias = DB::select("SELECT cursos.id,`cursos`.`Sigla` as 'cod_prin',cursos.Anio_id,cursos.NivelCurso as 'CursoP',cursos.Malla,cursos.NombreCurso as 'mat_prin',
        // cursos.id as 'id_materia_p',prerrequisitos.id_materia_s,(select c.Sigla from cursos c where c.id=prerrequisitos.id_materia_s) as 'cod_sec'
        //         FROM `cursos`
        //             LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id` where cursos.Anio_id=$Anio_id and cursos.Malla='$Malla' ");
        // $SiTienePrerreq = true;
        // foreach ($materias as $k) {
        //     if ($k->cod_sec==null) {
        //         $SiTienePrerreq = false;
        //     }
        // }
        // if ($SiTienePrerreq ==false) {
        //     $materias = DB::select("SELECT cursos.id,`cursos`.`Sigla` as 'cod_prin',cursos.Anio_id,cursos.NivelCurso as 'CursoP',cursos.Malla,cursos.NombreCurso as 'mat_prin',
        //     cursos.id as 'id_materia_p',(SELECT p.id_materia_s as 'id_materia_s2' from prerrequisitos p where p.id_materia_p =  (select c.id from cursos c where c.Sigla=cursos.Sigla and c.NivelCurso NOT LIKE 'SEGUNDO BASICO B' and c.Anio_id=$Anio_id LIMIT 1)LIMIT 1) AS 'id_materia_sec',(select c.Sigla from cursos c where c.id=id_materia_sec) as 'cod_sec'
        //             FROM `cursos`
        //                 LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id` where cursos.Anio_id=$Anio_id and cursos.Malla='$Malla';
        //     ");
        // }
        $materias = DB::select("SELECT
                cursos.id,
                `cursos`.`Sigla` as 'cod_prin',
                cursos.Anio_id,
                cursos.NivelCurso as 'CursoP',
                cursos.Malla,
                cursos.NombreCurso as 'mat_prin',
                cursos.id as 'id_materia_p',
                (
                    SELECT
                        GROUP_CONCAT(p.id_materia_s) as 'id_materia_s2'
                    FROM
                        prerrequisitos p
                    WHERE
                        p.id_materia_p = (
                            SELECT
                                c.id
                            FROM
                                cursos c
                            WHERE
                                c.Sigla = cursos.Sigla
                                AND c.Anio_id = $Anio_id
                            LIMIT 1
                        )
                    LIMIT 1
                ) AS 'id_materia_sec',
                (
                    SELECT
                        GROUP_CONCAT(c.Sigla)
                    FROM
                        cursos c
                    WHERE
                        c.id IN (
                            SELECT p.id_materia_s
                            FROM prerrequisitos p
                            WHERE p.id_materia_p = cursos.id
                                AND prerrequisitos.Malla = '$Malla'
                                AND prerrequisitos.Anio_id = '$Anio_id'
                        )
                ) as 'cod_sec',
                (
                    SELECT
                        GROUP_CONCAT(cc.SiglaRespaldo)
                    FROM
                        cursos cc
                    WHERE
                        cc.id IN (
                            SELECT p.id_materia_s
                            FROM prerrequisitos p
                            WHERE p.id_materia_p = cursos.id
                                AND prerrequisitos.Malla = '$Malla'
                                AND prerrequisitos.Anio_id = '$Anio_id'
                        )
                ) as 'cod_secRespaldo'
            FROM
                `cursos`
            LEFT JOIN `prerrequisitos` ON `prerrequisitos`.`id_materia_p` = `cursos`.`id`
            WHERE
                cursos.Anio_id = $Anio_id
                AND cursos.Malla = '$Malla';
            ");
        return $materias;
    }
    public function ListaAgrupacionMateriasXCursos(Request $request, $id)
    {

        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');

        $Lista=[];
        // $tipo = $request->query('tipo');
        // $curso = Curso::whereRaw('NivelCurso=?',$tipo)->orderBy('NombreCurso','desc')->get();
        // return $curso;

        // $adminID = $request->input('admin_id');
        $adminID = $id;
        // $curso = Curso::query()->orderBy('NivelCurso', 'ASC')->get(); //ACA SELECCIONA TODOS LOS CURSOS DE LA TABLA PARA LAS MANIOBRAS

        //PERO YANO LO NECESITAMOS YA Q TENEMOS QUE HACERLO POR GESTION
        $curso =Curso::where('Anio_id',$Anio_id)->where('Malla',$Malla)->orderBy('NivelCurso','desc')->get();
        // return $curso;

        try {
            foreach ($curso as $C) {
                // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                $NewCurso = Curso::where('id','=',$C->id)->first();

                $verificacion = Administrativos_Cursos::where('Admin_id','=', $adminID)->where('Curso_id','=',$C->id)->first();
                if ($verificacion != "" || $verificacion !=null) {
                    //ESTE DOCENTE TIENE PUESTO EL CURSO ENTONCES ACTIVADO
                    $NewCurso['Existencia'] = 'ACTIVADO';

                    $NewCurso['idAdmin'] = '';
                    $NewCurso['Ap_Paterno'] = '';
                    $NewCurso['Ap_Materno'] = '';
                    $NewCurso['Nombre'] = '';
                } else {
                    //ESTE DONCENTE NO TIENE ACTIVADO ESTE CURSO, X LO TANTO VERIFICAR SI OTROS DOCENTES SI LO TIENEN
                    $AllAdmin = Administrativos::query()->get();
                    foreach ($AllAdmin as $admin) {
                        $verificacionOtrosAdmins = Administrativos_Cursos::where('Admin_id','=', $admin->id)->where('Curso_id','=',$C->id)->first();
                        if ($verificacionOtrosAdmins != "" || $verificacionOtrosAdmins !=null) {
                            //EL ADMINISTRADOR X YA TIENE ACTIVADO ESTE CURSO
                            $NewCurso['Existencia'] = 'ACTIVO';
                            $NewCurso['idAdmin'] = $admin->id;
                            $NewCurso['Ap_Paterno'] = $admin->Ap_Paterno;
                            $NewCurso['Ap_Materno'] = $admin->Ap_Materno;
                            $NewCurso['Nombre'] = $admin->Nombre;
                            break;
                        }
                        else{
                            $NewCurso['Existencia'] = 'INACTIVO';
                            $NewCurso['idAdmin'] = '';
                            $NewCurso['Ap_Paterno'] = '';
                            $NewCurso['Ap_Materno'] = '';
                            $NewCurso['Nombre'] = '';
                        }
                    }


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

    public function EstadisticasInscripciones(Request $request)
    {

        $Anio_id = $request->input('idGestion');  // Otra forma: $request->idGestion;
        $NivelListar = $request->input('NivelListar');

        // $Anio_id=$request->Anio_id; //ANIO DE GESTION DE
        // $Anio_id=$idGestion; //ANIO DE GESTION DE

        //OBTENIENDO ANIO EN TXT
        $Aniodata = Anio::where('id', $Anio_id)->first(); // Usar first() para obtener el primer registro
        $Aniotxt = $Aniodata->Anio; // Acceder a la propiedad Anio del modelo
        if(strpos($Aniotxt, '/') !== false){
            $RegimenEstudio='SEMESTRALIZADO';
        }else{
            $RegimenEstudio='ANUALIZADO';
        }

        // $cursosGestion = Curso::where('Anio_id',4)->get();
        $dataXcurso= array();
        switch ($NivelListar) {
            case 'TECNICO SUPERIOR':
                $cursosGestion = Curso::where('Anio_id',$Anio_id)->where('NivelCurso', 'like', '%SUPERIOR%')->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);
                break;
            case 'TECNICO MEDIO':
                $cursosGestion = Curso::where('Anio_id',$Anio_id)->where('NivelCurso', 'like', '%MEDIO%')->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);
                break;
            case 'TECNICO MEDIO Y SUPERIOR':
                $cursosGestion = Curso::where('Anio_id',$Anio_id)->where('NivelCurso', 'like', '%SUPERIOR%')->orWhere('NivelCurso', 'like', '%MEDIO%')->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);
                break;
            case 'CAPACITACION':
                $cursosGestion = Curso::where('Anio_id',$Anio_id)->where('NivelCurso', 'not like', '%SUPERIOR%')->where('NivelCurso', 'not like', '%MEDIO%')->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);
                break;
            default:
                $cursosGestion = Curso::where('Anio_id',$Anio_id)->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);
                break;
        }
        foreach ($cursosGestion as $s) {
            $NivelCurso= $s->NivelCurso;
            try {
                //obtengo la fila del curso deseado pero solo por su Id  del curso
                $CursoData = Curso::where('NivelCurso','=', $NivelCurso)->where('Anio_id','=',$Anio_id)->get();
                //obtener la lista de los estudiantes pero solo por su estudiante_id ...
                //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
                $Lista = array();
                foreach ($CursoData as $k ) {
                    $CalificacionesData = Calificaciones::where('curso_id','=', $k->id)->where('Anio_id','=',$Anio_id)->get();
                    $CalificacionesData = $CalificacionesData->unique('estudiante_id');

                    //PARA SACAR MAS COMUNES DE NIVEL Y CARRERA
                    $niveles = [];
                    $carreras = [];
                    foreach ($CalificacionesData as $C) {
                        // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                        $EstudiantesData = DB::select("SELECT anios.Anio ,calificaciones.Arrastre, cursos.NivelCurso, `estudiantes`.CI, estudiantes.Carrera,estudiantes.Nivel,
                        estudiantes.Categoria,estudiantes.Sexo, administrativos.Ap_Paterno as Ap_PAdmin,administrativos.Ap_Materno as Ap_MAdmin,
                        administrativos.Nombre as NombreAdmin
                        FROM `estudiantes`
                            LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                            LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
                            LEFT JOIN `anios` ON `anios`.`id` = `cursos`.`Anio_id`
                            LEFT JOIN `administrativos` ON `administrativos`.`id` = `estudiantes`.`Admin_id`
                            WHERE estudiantes.id=$C->estudiante_id and calificaciones.anio_id=$Anio_id and cursos.NivelCurso='$NivelCurso'");

                        $Lista[] = $EstudiantesData[0];
                        //PARA PONER EN LISTA DEL COMPACT IDIVIDUALMENTE

                        // Recopilar niveles y carreras
                        $niveles[] = $EstudiantesData[0]->Nivel;
                        $carreras[] = $EstudiantesData[0]->Carrera;
                    }


                    // Contar la frecuencia de cada nivel
                    // Filtrar el array para eliminar valores no válidos
                    $niveles = array_filter($niveles, function ($valor) {
                        return is_string($valor) || is_int($valor);
                    });
                    $frecuenciaNiveles = array_count_values($niveles);
                    arsort($frecuenciaNiveles); // Ordenar de mayor a menor
                    $NivelLista = key($frecuenciaNiveles); // Obtener el nivel más común

                    // Contar la frecuencia de cada carrera
                    // Filtrar el array para eliminar valores no válidos
                    $niveles = array_filter($carreras, function ($valor) {
                        return is_string($valor) || is_int($valor);
                    });
                    $frecuenciaCarreras = array_count_values($carreras);
                    arsort($frecuenciaCarreras); // Ordenar de mayor a menor
                    $CarreraLista = key($frecuenciaCarreras); // Obtener la carrera más común
                    //SELECCIONAR SU
                    $SeConfirmoRegular=false;
                    foreach ($Lista as $h ) {
                        if ($SeConfirmoRegular==false) {
                            //no se confirmo
                            //HACER Q SE LLENE LOS CAMPOS
                            // $CarreraLista = $h->Carrera;
                            // $NivelLista=$h->Nivel;

                            switch ($h->Nivel) {
                                case 'TECNICO SUPERIOR':
                                    $NivelLista = 'TECNICO SUPERIOR';
                                    break;
                                case 'TECNICO MEDIO':
                                    $NivelLista = 'TECNICO MEDIO';
                                    break;

                                default:
                                    $NivelLista = 'CAPACITACION';
                                    break;
                            }
                            $ArrastreLista = $h->Arrastre;
                            if ($h->Arrastre=='REGULAR') {
                                $SeConfirmoRegular=true;
                                break;
                            }
                        }else{
                            break;
                        }

                    }


                    $CursoNivelLista = $k->NivelCurso; //NIVEL DE CURSO PARA PONER EN LA LISTA DEL RETURN
                }
                //result.filter(a => (a.CI.indexOf(elemento.CI)) > -1).length==0

                //ELIMINAR ESTUDIANTES REPETIDOS
                $compact=array();
                // $data=array();
                foreach($Lista as $w){
                    if(count(collect($compact)->where('CI', $w->CI)->all())==0){
                        $compact[] = $w;
                    }
                }
                $data=Array(
                    // "Arrastre"=>$ArrastreLista,
                    "Carrera"=>$CarreraLista,
                    "Nivel"=>$NivelLista,
                    "Regimen"=>$RegimenEstudio,
                    "NivelCurso"=>$CursoNivelLista,
                    "Nuevos_M"=>count(collect($compact)->where('Categoria', 'NUEVO')->where('Sexo', 'MASCULINO')->where('Arrastre','REGULAR')->all()),
                    "Nuevos_F"=>count(collect($compact)->where('Categoria', 'NUEVO')->where('Sexo', 'FEMENINO')->where('Arrastre','REGULAR')->all()),
                    "Total_Nuevos"=>count(collect($compact)->where('Categoria', 'NUEVO')->where('Arrastre','REGULAR')->all()),
                    "Antiguos_M"=>count(collect($compact)->where('Categoria', 'ANTIGUO')->where('Sexo', 'MASCULINO')->where('Arrastre','REGULAR')->all()),
                    "Antiguos_F"=>count(collect($compact)->where('Categoria', 'ANTIGUO')->where('Sexo', 'FEMENINO')->where('Arrastre','REGULAR')->all()),
                    "Total_Antiguos"=>count(collect($compact)->where('Categoria', 'ANTIGUO')->where('Arrastre','REGULAR')->all()),
                    "Arrastre_M"=>count(collect($compact)->where('Sexo', 'MASCULINO')->where('Arrastre','ARRASTRE')->all()),
                    "Arrastre_F"=>count(collect($compact)->where('Sexo', 'FEMENINO')->where('Arrastre','ARRASTRE')->all()),
                    "Total_Arrastres"=>count(collect($compact)->where('Arrastre', 'ARRASTRE')->all()),
                    "Total_M"=>count(collect($compact)->where('Sexo', 'MASCULINO')->where('Arrastre', 'REGULAR')->all()),
                    "Total_F"=>count(collect($compact)->where('Sexo', 'FEMENINO')->where('Arrastre', 'REGULAR')->all()),
                    "Total_Gral_Regulares"=>count($compact), //CONTAR TODOS CON IRREGULARES
                    "Total_Gral"=>count(collect($compact)->where('Arrastre', 'REGULAR')->all()),
                );

                $dataXcurso[]= $data;
            } catch (Exception $e) {
                return $e;
            }
        }
        // Filtrar $dataXcurso para eliminar elementos con Total_Gral = 0
        $dataXcurso = array_filter($dataXcurso, function ($item) {
            return $item['Total_Gral'] != 0; // Mantener solo los elementos donde Total_Gral no sea 0
        });
        return $dataXcurso;
    }
    public function ListaEstudiantes(Request $request)
    {
        $NivelCurso= $request->NivelCurso;
        $idMateria=$request->idMateria;
        $Materia=$request->Materia;
        $Anio_id=$request->Anio_id;


        if ($idMateria=='' || $idMateria ==null) {
            try {
                //obtengo la fila del curso deseado pero solo por su Id  del curso
                $CursoData = Curso::where('NivelCurso','=', $NivelCurso)->where('Anio_id','=',$Anio_id)->get();
                //obtener la lista de los estudiantes pero solo por su estudiante_id ...
                //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
                $Lista = array();
                foreach ($CursoData as $k ) {
                    $CalificacionesData = Calificaciones::where('curso_id','=', $k->id)->where('Anio_id','=',$Anio_id)->get();
                    $CalificacionesData = $CalificacionesData->unique('estudiante_id');

                    $cont=0;
                    foreach ($CalificacionesData as $C) {
                        // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                        $EstudiantesData = DB::select("SELECT calificaciones.Docente_Especialidad, calificaciones.Docente_Practica, calificaciones.Especialidad_Estudiante,calificaciones.Categoria as Categoria_Estudiante, calificaciones.Observacion_Estudiante, calificaciones.Arrastre, cursos.NivelCurso, `estudiantes`.*, a.Ap_Paterno as Ap_PAdmin,a.Ap_Materno as Ap_MAdmin,a.Nombre as NombreAdmin, a.CelularTrabajo, a2.Ap_Paterno as Ap_PAdminPC, a2.Ap_Materno as Ap_MAdminPC, a2.Nombre as NombreAdminPC, a2.CelularTrabajo as CelularTrabajoPC
                        FROM `estudiantes`
                            LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                            LEFT JOIN `cursos` ON `calificaciones`.`curso_id` = `cursos`.`id`
                            LEFT JOIN `administrativos` AS a ON a.id = estudiantes.Admin_id
                            LEFT JOIN `administrativos` AS a2 ON a2.id = estudiantes.Admin_idPC
                            WHERE estudiantes.id=$C->estudiante_id and calificaciones.anio_id=$Anio_id and cursos.NivelCurso='$NivelCurso'");

                        $Lista[] = $EstudiantesData[0];
                    }
                }
                return $Lista;
            } catch (Exception $e) {
                return 'EL CURSO NO TIENE ESTUDIANTES';
            }
        }else{
            try {
                //obtengo la fila del curso deseado pero solo por su Id  del curso
                // $CursoData = Curso::where('NivelCurso','=', $NivelCurso)->where('Anio_id','=',$Anio_id)->first(); //ANTES SE USABA
                //obtener la lista de los estudiantes pero solo por su estudiante_id ...
                //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
                // $CalificacionesData = Calificaciones::where('curso_id','=', $CursoData->id)->where('Anio_id','=',$Anio_id)->get(); //ANTES SE USABA
                $CalificacionesData = Calificaciones::where('curso_id','=', $idMateria)->where('Anio_id','=',$Anio_id)->get(); //AHORA ESTE ES
                //ELIMINAR VALORES DUPLICADOS POR estudiante_id
                $CalificacionesData = $CalificacionesData->unique('estudiante_id');
                $Lista = array();
                foreach ($CalificacionesData as $C) {
                    // $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
                    $EstudiantesData = DB::select("SELECT calificaciones.Docente_Especialidad, calificaciones.Docente_Practica, calificaciones.Especialidad_Estudiante, calificaciones.Categoria as Categoria_Estudiante, calificaciones.Observacion_Estudiante, `estudiantes`.*, calificaciones.Arrastre, a.Ap_Paterno as Ap_PAdmin,a.Ap_Materno as Ap_MAdmin,a.Nombre as NombreAdmin, a.CelularTrabajo, a2.Ap_Paterno as Ap_PAdminPC, a2.Ap_Materno as Ap_MAdminPC, a2.Nombre as NombreAdminPC, a2.CelularTrabajo as CelularTrabajoPC
                    FROM `estudiantes`
                        LEFT JOIN `calificaciones` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
                        LEFT JOIN `administrativos` AS a ON a.id = estudiantes.Admin_id
                        LEFT JOIN `administrativos` AS a2 ON a2.id = estudiantes.Admin_idPC
                        WHERE estudiantes.id=$C->estudiante_id and calificaciones.anio_id=$Anio_id and calificaciones.curso_id=$idMateria");
                    $Lista[] = $EstudiantesData[0];
                }
                return $Lista;
            } catch (Exception $e) {
                return 'EL CURSO NO TIENE ESTUDIANTES';
            }
        }




    }
    public function BuscarNivelCurso($id)
    {

        // $requestData = $request->all();
        // $CursoData = Curso::firstOrFail($id);
        $CursoData = Curso::where('id','=', $id)->first();
        $NivelCursoObtenido = $CursoData->NivelCurso;
        return $NivelCursoObtenido;
    }
    public function CargarCursosUnique(Request $request)
    {
        // $Curso = Curso::all();
        // $Cursos = $Curso->unique('NivelCurso');

        $Malla = $request->input('Malla');
        $Anio_id = $request->input('Anio_id');

            $data = Curso::where('Anio_id',$Anio_id)->where('Malla',$Malla)->distinct()->orderBy('NivelCurso','desc')->get(['NivelCurso']);



        // $Cursos = Curso::distinct()->get(['NivelCurso']);
        return $data;
    }
    public function CargarMalla()
    {
        $data = Curso::distinct()->get(['Malla']);
        return $data;
    }
    public function ModificarBimestres(Request $request)
    {
        $Bimestre = $request->BiTriEstado;
        $Malla = $request->Malla;
        $id_gestion = $request->id_gestion;

        DB::select("update cursos set BiTriEstado = '$Bimestre' where Malla='$Malla' and Anio_id='$id_gestion'");
    }
    public function CargarSiglaUnique()
    {
        $Cursos = Curso::distinct()->get(['Sigla']);
        return $Cursos;
    }
    public function MateriasxEstudianteAnio(Request $request)
    {
        //CARGAR MATERIAS DE UN ESTUDIANTE DE UNA GESTION
        // $Nivel="SUPERIORRRR";
        $Estudiante_id = $request->input('Estudiante_id');
        $Anio_id = $request->input('Anio_id');
        $data = DB::select("SELECT `calificaciones`.`id`,calificaciones.anio_id,calificaciones.curso_id,calificaciones.Arrastre,calificaciones.estudiante_id,calificaciones.Promedio,calificaciones.PruebaRecuperacion, anios.Anio, cursos.NombreCurso,cursos.NivelCurso,cursos.Sigla,cursos.SiglaRespaldo,cursos.Malla
        FROM `calificaciones`
            LEFT JOIN `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
            LEFT JOIN `cursos` ON calificaciones.curso_id = `cursos`.`id`
            LEFT JOIN `anios` ON `calificaciones`.`anio_id` = `anios`.`id` where estudiantes.id=$Estudiante_id and anios.id=$Anio_id");
        // $curso = Curso::where('NivelCurso','=',$Nivel)->get();
                //  Curso::where('id','=',$id)->update($requestData);

        return $data;
    }
    public function HistorialAcademico(Request $request)
    {
        //CARGAR MATERIAS DE UN ESTUDIANTE DE UNA GESTION
        // $Nivel="SUPERIORRRR";
        $Estudiante_id = $request->input('Estudiante_id');
        // $Anio_id = $request->input('Anio_id');
        $datasql = DB::select("SELECT calificaciones.*, anios.Anio,cursos.Rango, cursos.NombreCurso,cursos.NivelCurso,cursos.Sigla,cursos.SiglaRespaldo,cursos.Malla
        FROM `calificaciones`
            LEFT JOIN `estudiantes` ON `calificaciones`.`estudiante_id` = `estudiantes`.`id`
            LEFT JOIN `cursos` ON calificaciones.curso_id = `cursos`.`id`
            LEFT JOIN `anios` ON `calificaciones`.`anio_id` = `anios`.`id` where estudiantes.id=$Estudiante_id order by anios.Anio,cursos.Rango");
        // $curso = Curso::where('NivelCurso','=',$Nivel)->get();
                //  Curso::where('id','=',$id)->update($requestData);

            foreach ($datasql as $cdata) {

                // $materiaid = $cdata->id; //SI FUNCIONABA; PERO CUANDO SE TRATABA DE PARALELO B, NO DETECTABA PRERREQUISITO A CAUSA DE LOS PK
                $materiaid = $cdata->Sigla;
                // $ArrayMats[] = $cdata->NombreCurso;
                // $ArraySiglaP[] = $cdata->Sigla;
                // $ArrayHoras[] = $cdata->Horas;
                $textMats='';
                $textSiglas='';
                //SACANDO PRERREQUISITOS DE MATERIAS POR SIGLA
                $prerreqs = DB::select("select p.id,p.id_materia_p,p.id_materia_s,
                m.NombreCurso as 'mat_prin',m.Sigla as 'cod_prin', m.Horas,m.NombreCurso,m.NivelCurso,
                m2.NombreCurso as 'materia_sec',m2.Sigla as 'cod_sec'
                from prerrequisitos p LEFT JOIN
                cursos m ON m.id=p.id_materia_p LEFT JOIN
                cursos m2 ON m2.id=p.id_materia_s
                WHERE m.Sigla='$materiaid'");

                //ELIMINAR REPETIDOS BASANDOME EN SIGLA
                $codSecUnicos = array_unique(array_column($prerreqs, 'cod_sec'));
                $datosFiltrados = [];
                foreach ($prerreqs as $fila) {
                    if (in_array($fila->cod_sec, $codSecUnicos)) {
                        $datosFiltrados[] = $fila;
                        $codSecUnicos = array_diff($codSecUnicos, [$fila->cod_sec]);
                    }
                }

                $prerreqs = $datosFiltrados;
                foreach ($prerreqs as $p) {
                    $textMats=$p->materia_sec.'/'.$textMats;
                    // $textMats=$p->cod_sec.'/'.$textSiglas;
                }

                //  QUITAR EL ULTIMO SIMBOLO "/"
                if (!empty($textMats)) {
                    // Quita la última letra de la cadena
                    $textMats = substr($textMats, 0, -1);

                    // Asigna el valor modificado a la propiedad Prerrequisitos de $cdata
                    $cdata->Prerrequisitos = $textMats;
                } else {
                    // Haz algo si la cadena está vacía, si es necesario
                    if (strpos($cdata->NivelCurso, "PRIMERO") !== false) {
                        // La palabra "PRIMERO" está presente en la cadena
                        $cdata->Prerrequisitos = 'PRUEBA DE ADMISIÓN';

                        // Puedes realizar acciones adicionales aquí si es necesario
                    } else {
                        // La palabra "PRIMERO" no está presente en la cadena
                        $cdata->Prerrequisitos = 'NINGUNA';

                        // Puedes realizar acciones adicionales aquí si es necesario
                    }
                }
                if ($cdata->Promedio < 61) {
                    $cdata->AprobReprob = 'REPROBADO';
                    $cdata->Obs = 'NO';
                }else{
                    $cdata->AprobReprob = 'APROBADO';
                    $cdata->Obs = 'SI';
                }
            }

        return $datasql;
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
        $requestData = $request->all();

        Curso::insert($requestData);

        return 'curso creado';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Curso  $curso
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Curso::where('id','=',$id)->firstOrFail();
        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Curso  $curso
     * @return \Illuminate\Http\Response
     */
    public function edit(Curso $curso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Curso  $curso
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $requestData = $request->all();

        Curso::where('id','=',$id)->update($requestData);

        return $requestData;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Curso  $curso
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Curso::destroy($id);
        return 'curso eliminado';
        // return redirect('curso')->with('flash_message', 'Curso deleted!');
    }
    public function cursoYPrerreq($id)
    {

        DB::select("delete from prerrequisitos where id_materia_s='$id'");
        Curso::destroy($id);
        return 'curso eliminado';
        // return redirect('curso')->with('flash_message', 'Curso deleted!');
    }
    //PARA GESTION DE CURSOS, PERO VARIOS EN UNO SOLO
    public function materiasPorCurso(Request $request)
    {
        //OBTENER MATERIAS POR NIVEL DE CURSO
        $NivelCurso = $request->input('NivelCurso');
        $idAnio = $request->input('Anio_id');
        $MallaCurso = $request->input('Malla');
        $materias = Curso::where('NivelCurso', $NivelCurso)->where('Anio_id', $idAnio)->where('Malla', $MallaCurso)->get();
        return response()->json($materias);
    }

    public function clonarMaterias(Request $request)
    {
        $paralelo = $request->input('paralelo');

        // Busca todas las materias del curso seleccionado
        $NivelCurso = $request->input('NivelCurso');
        $idAnio = $request->input('Anio_id');
        $MallaCurso = $request->input('Malla');
        $materiasAClonar = Curso::where('NivelCurso', $NivelCurso)->where('Anio_id', $idAnio)->where('Malla', $MallaCurso)->get();

        $NivelCursoPeroEnNewParalelo = substr($NivelCurso, 0, -1) . $paralelo; //ENTONCES HACER LA VERIFICACION CON EL PARALELO A por defecto
        // Clona las materias asignandoles el nuevo paralelo
        foreach ($materiasAClonar as $materia) {
            $nuevaMateria = new Curso();
            $nuevaMateria->NombreCurso = $materia->NombreCurso;
            $nuevaMateria->NivelCurso = $NivelCursoPeroEnNewParalelo;
            $nuevaMateria->Sigla = $materia->Sigla;
            $nuevaMateria->SiglaRespaldo = $materia->SiglaRespaldo;
            $nuevaMateria->Tipo = $materia->Tipo;
            $nuevaMateria->BiTriEstado = $materia->BiTriEstado;
            $nuevaMateria->Horas = $materia->Horas;
            $nuevaMateria->Malla = $materia->Malla;
            $nuevaMateria->Anio_id = $materia->Anio_id;
            $nuevaMateria->Rango = $materia->Rango;
            $nuevaMateria->save();
        }
        return 'SE CREÓ EL NUEVO CURSO '. $NivelCursoPeroEnNewParalelo;
    }
    public function editarParaleloMaterias(Request $request)
    {
        $nuevoParalelo = $request->input('NuevoParalelo');
        $NivelCurso = $request->input('NivelCurso');
        $idAnio = $request->input('Anio_id');
        $MallaCurso = $request->input('Malla');

        $NivelCursoPeroEnNewParalelo = substr($NivelCurso, 0, -1) . $nuevoParalelo; //ENTONCES HACER LA VERIFICACION CON EL PARALELO A por defecto
        // Actualiza el paralelo de todas las materias del curso seleccionado
        Curso::where('NivelCurso', $NivelCurso)->where('Anio_id', $idAnio)->where('Malla', $MallaCurso)
            ->update(['NivelCurso' => DB::raw("'" . $NivelCursoPeroEnNewParalelo . "'")]);


        return 'SE MODIFICARON LOS PARALELOS';
    }
    public function eliminarMateriasPorNivelCurso(Request $request)
    {
        $NivelCurso = $request->input('NivelCurso');
        $idAnio = $request->input('Anio_id');
        $MallaCurso = $request->input('Malla');

        // Elimina todas las materias del NivelCurso especificado
        Curso::where('NivelCurso', $NivelCurso)->where('Anio_id', $idAnio)->where('Malla', $MallaCurso)->delete();

        return response()->json(['message' => 'Todas las materias del NivelCurso han sido eliminadas exitosamente']);
    }
}
