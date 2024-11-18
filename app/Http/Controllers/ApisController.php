<?php

namespace App\Http\Controllers;
use App\Models\Administrativos;
use App\Models\Administrativos_Cursos;
use App\Models\Estudiantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\Else_;

class ApisController extends Controller
{
    public function DisponibilidadInscripciones()
    {
        //PAGINA DE INSCRIPCIONES GENERAL
        return false;
    }
    public function DisponibilidadInscripcionesNuevos()
    {
        //PAGINA DE INSCRIPCIONES PARA LOS QUE QUIEREN DAR EXAMEN DE ADMISION
        return false;
    }
    public function VideoApi()
    {
        //LISTA DE CURSOS
        // CAPACITACION Y SUPERIOR
        $data = Array (
            "0" => Array ("vidio" => "https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2FIFAMariaLuisa%2Fvideos%2F1038375650338333%2F&show_text=false&width=560&t=0",),
        );
        return $data;
    }
    // #region PARA LA NUEVA GESTION 2023 IFAS
    public function ListarTipoMateriaApi() //USADO EN EL MOMENTO DE CREAR MATERIAS PARA ESCOGER TIPO DE MATERIA
    {
        $data = Array (
            "0" => Array ("Tipo" => "TEORICA","Detalle" => "PERMITE 1 DOCENTE CON 1 MATERIA","Ifa"=>"TODOS","Estado"=>"ACTIVO"),
            "1" => Array ("Tipo" => "PRACTICA","Detalle" => "PERMITE MULTIPLES DOCENTES PARA 1 MATERIA","Ifa"=>"TODOS","Estado"=>"ACTIVO"),
            "2" => Array ("Tipo" => "PRACTICA.","Detalle" => "PERMITE 1 DOCENTE CON 1 MATERIA","Ifa"=>"TODOS","Estado"=>"ACTIVO"),
        );
        return $data;
    }
    public function ListarCursosApi() //USADO PARA CREAR MATERIAS COMO TAMBIEN ES EL CURSO_SOLICITADO EN ESTUDIANTES
    {
        //LISTA DE CURSOS
        $data = Array (
            "0" => Array ("NivelCurso" => "PRIMERO SUPERIOR","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "1" => Array ("NivelCurso" => "SEGUNDO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "2" => Array ("NivelCurso" => "TERCERO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "3" => Array ("NivelCurso" => "PRIMERO INTERMEDIO","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "4" => Array ("NivelCurso" => "SEGUNDO INTERMEDIO","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "5" => Array ("NivelCurso" => "TERCERO INTERMEDIO","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "6" => Array ("NivelCurso" => "PRIMERO BASICO","Para" => "NUEVOS o ANTIGUOS (a partir de los 12 años)","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "7" => Array ("NivelCurso" => "SEGUNDO BASICO","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "8" => Array ("NivelCurso" => "TERCERO BASICO","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "9" => Array ("NivelCurso" => "PRIMERO INICIACION","Para" => "NUEVOS (8 a 11 años)","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "10" => Array ("NivelCurso" => "SEGUNDO INICIACION","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "11" => Array ("NivelCurso" => "TERCERO INICIACION","Para" => "SOLO ANTIGUOS","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            //PARA DEL FOLKLORE ORURO  ...............
            "12" => Array ("NivelCurso" => "PRIMER AÑO","Para" => "NUEVO o ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "13" => Array ("NivelCurso" => "SEGUNDO AÑO","Para" => "SOLO ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "14" => Array ("NivelCurso" => "TERCER AÑO","Para" => "SOLO ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"INHABILITADO"),
            "15" => Array ("NivelCurso" => "PRIMERO BASICO","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "16" => Array ("NivelCurso" => "SEGUNDO BASICO","Para" => "SOLO ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "17" => Array ("NivelCurso" => "PRIMERO INICIAL","Para" => "NUEVOS o  ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "18" => Array ("NivelCurso" => "SEGUNDO INICIAL","Para" => "ANTIGUOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "19" => Array ("NivelCurso" => "CAPACITACION","Para" => "NUEVOS","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            //PARA BELLAS ARTES ORURO..................
            "20" => Array ("NivelCurso" => "PRIMERO SUPERIOR","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "21" => Array ("NivelCurso" => "SEGUNDO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "22" => Array ("NivelCurso" => "TERCERO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "23" => Array ("NivelCurso" => "CUARTO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "24" => Array ("NivelCurso" => "QUINTO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "25" => Array ("NivelCurso" => "SEXTO SUPERIOR","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "26" => Array ("NivelCurso" => "PRIMERO CAPACITACION","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "27" => Array ("NivelCurso" => "SEGUNDO CAPACITACION","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "28" => Array ("NivelCurso" => "TERCERO CAPACITACION","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "29" => Array ("NivelCurso" => "CAPACITACION I TEEN","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "30" => Array ("NivelCurso" => "CAPACITACION II TEEN","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "31" => Array ("NivelCurso" => "CAPACITACION III TEEN","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),

            "32" => Array ("NivelCurso" => "MODULO I","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "33" => Array ("NivelCurso" => "MODULO II","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "34" => Array ("NivelCurso" => "MODULO III","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "35" => Array ("NivelCurso" => "MODULO I TEEN","Para" => "NUEVOS o ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "36" => Array ("NivelCurso" => "MODULO II TEEN","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "37" => Array ("NivelCurso" => "MODULO III TEEN","Para" => "ANTIGUOS","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
        );
        return $data;
        // return ' ';
    }
    public function ListaAntiguaNuevaAutoCompletar() {
        $data = Array (

            // NUEVA MALLA
            "1" => Array ("Area" => "ARTES MUSICALES","Programa" => "MUSICA","Carrera" => "MUSICA CLASICA/ACADEMICA","Mension" => "NINGUNA","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO","Malla"=>"NUEVA"),
            "2" => Array ("Area" => "ARTES MUSICALES","Programa" => "MUSICA","Carrera" => "MUSICA BOLIVIANA","Mension" => "NINGUNA","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO","Malla"=>"NUEVA"),
            "3" => Array ("Area" => "ARTES VISUALES","Programa" => "ARTES PLASTICAS Y VISUALES","Carrera" => "ARTES PLASTICAS Y VISUALES","Mension" => "NINGUNA","Estado"=>"ACTIVO","Ifa"=>"BELLAS ARTES ORURO","Malla"=>"NUEVA"),
            //MALLA ANTIGUA
            "4" => Array ("Area" => "CLASICA","Programa" => "NINGUNA","Estado"=>"ACTIVO","Carrera" => "MUSICA","Mension" => "INSTRUMENTISTA","Ifa"=>"MARIA LUISA LUZIO","Malla"=>"ANTIGUA"),
            "5" => Array ("Area" => "BOLIVIANA","Programa" => "NINGUNA","Estado"=>"ACTIVO","Carrera" => "MUSICA","Mension" => "GUITARRA","Ifa"=>"DEL FOLKLORE ORURO","Malla"=>"ANTIGUA"),
            "6" => Array ("Area" => "ARTES PLASTICAS Y VISUALES","Programa" => "NINGUNA","Estado"=>"ACTIVO","Carrera" => "ARTES PLASTICAS Y VISUALES","Mension" => "PINTURA","Ifa"=>"BELLAS ARTES ORURO","Malla"=>"ANTIGUA"),
        );
        return $data;
    }
    public function ListarAreasApi() //USADO EN ESTUDIANTES, LISTA LAS MENCIONES
    {
        $data = Array (
            "0" => Array ("Area" => "CLASICA","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO","Malla"=>"ANTIGUA"),
            // // NUEVA MALLA....,"Malla"=>"NUEVA".,
            "1" => Array ("Area" => "ARTES ESCENICAS","Estado"=>"INHABILITADO","Ifa"=>"OTRO","Malla"=>"NUEVA"),
            "2" => Array ("Area" => "ARTES MUSICALES","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO o DEL FOLKLORE ORURO","Malla"=>"NUEVA"),
            "3" => Array ("Area" => "ARTES VISUALES","Estado"=>"ACTIVO","Ifa"=>"BELLAS ARTES ORURO","Malla"=>"NUEVA"),
            "4" => Array ("Area" => "ARTES AUDIOVISUALES","Estado"=>"INHABILITADO","Ifa"=>"OTRO","Malla"=>"NUEVA"),
            "5" => Array ("Area" => "BOLIVIANA","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO","Malla"=>"ANTIGUA"),
            "6" => Array ("Area" => "ARTES PLASTICAS Y VISUALES","Estado"=>"ACTIVO","Ifa"=>"BELLAS ARTES ORURO","Malla"=>"ANTIGUA"),
            "7" => Array ("Area" => "ACADÉMICA CLASICA","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO","Malla"=>"ANTIGUA"),
        );
        return $data;
    }
    public function ListarProgramasApi() //USADO EN ESTUDIANTES, LISTA LAS MENCIONES
    {
        $data = Array (
            //NUEVA MALLA
            "0" => Array ("Programa" => "DANZA","Pertenece"=>"ARTES ESCENICAS","Ifa"=>"OTRO","Malla"=>"NUEVA"),
            "1" => Array ("Programa" => "TEATRO","Pertenece"=>"ARTES ESCENICAS","Ifa"=>"OTRO","Malla"=>"NUEVA"),
            "2" => Array ("Programa" => "MUSICA","Pertenece"=>"ARTES MUSICALES","Ifa"=>"MARIA LUISA LUZIO o DEL FOLKLORE ORURO","Malla"=>"NUEVA"),
            "3" => Array ("Programa" => "ARTES PLASTICAS Y VISUALES","Pertenece"=>"ARTES VISUALES","Ifa"=>"BELLAS ARTES ORURO","Malla"=>"NUEVA"),
            "4" => Array ("Programa" => "CINEMATOGRAFIA Y ARTES AUDIOVISUALES","Pertenece"=>"ARTES AUDIOVISUALES","Ifa"=>"OTRO","Malla"=>"NUEVA"),
            "5" => Array ("Programa" => "NINGUNA","Pertenece"=>"TODOS","Ifa"=>"TODOS","Malla"=>"ANTIGUA"),
        );
        return $data;
    }
    public function ListarCarrerasApi()
    {
        //EN VEZ DE AREA LE PONDREMOS Carrera
        $data = Array (
            "0" => Array ("Carrera" => "MUSICA","Denominacion" => "NINGUNA","Malla"=>"ANTIGUA","Pertenece"=>"NINGUNA","Ifa"=>"MARIA LUISA LUZIO o DEL FOLKLORE ORURO"),
            //NUEVA MALLA 2023.....,"Ifa"=>"MARIA LUISA LUZIO"..
            "1" => Array ("Carrera" => "MUSICA CLASICA/ACADEMICA","Denominacion" => " EN MÚSICA CLÁSICA/ACADÉMICA","Malla"=>"NUEVA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "2" => Array ("Carrera" => "CANTO LIRICO","Denominacion" => "CANTO LÍRICO","Malla"=>"NUEVA","Pertenece"=>"MUSICA","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "3" => Array ("Carrera" => "MÚSICA MODERNA","Denominacion" => " EN MÚSICA MODERNA","Malla"=>"NUEVA","Pertenece"=>"MUSICA","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "4" => Array ("Carrera" => "MUSICA BOLIVIANA","Denominacion" => " EN MÚSICA BOLIVIANA","Malla"=>"NUEVA","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "5" => Array ("Carrera" => "ARTES PLASTICAS Y VISUALES","Denominacion" => " ANUALIZADA","Malla"=>"NUEVA o ANTIGUA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "6" => Array ("Carrera" => "REALIZACION DE CINEMATOGRAFIA Y ARTES AUDIOVISUALES","Denominacion" => " ","Malla"=>"NUEVA","Pertenece"=>"CINEMATOGRAFIA Y ARTES AUDIOVISUALES","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "7" => Array ("Carrera" => "DANZA CLASICA","Denominacion" => " ","Malla"=>"NUEVA","Pertenece"=>"DANZA","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "8" => Array ("Carrera" => "DANZA CONTEMPORANEA","Denominacion" => " ","Malla"=>"NUEVA","Pertenece"=>"DANZA","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "9" => Array ("Carrera" => "DANZAS BOLIVIANAS","Denominacion" => " ","Malla"=>"NUEVA","Pertenece"=>"DANZA","Ifa"=>"OTRO","Estado"=>"ACTIVO"),
            "10" => Array ("Carrera" => "TEATRO","Denominacion" => " ","Malla"=>"NUEVA","Pertenece"=>"TEATRO","Ifa"=>"OTRO","Estado"=>"ACTIVO"),

            "11" => Array ("Carrera" => "ARTES VISUALES","Denominacion" => " SEMESTRALIZADA","Malla"=>"NUEVA o ANTIGUA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO")
            // //para capacitacion NUEVA MALLA
            // "5" => Array ("Carrera" => "INICIACIÓN EN MÚSICA CLÁSICA/ACADÉMICA","Denominacion" => "NINGUNA","Malla"=>"NUEVA - CAPACITACION"),
            // "6" => Array ("Carrera" => "BÁSICO EN MÚSICA CLÁSICA/ACADÉMICA","Denominacion" => "NINGUNA","Malla"=>"NUEVA - CAPACITACION"),
            // "7" => Array ("Carrera" => "INTERMEDIO EN MÚSICA CLÁSICA/ACADÉMICA","Denominacion" => "NINGUNA","Malla"=>"NUEVA - CAPACITACION"),

        );
        return $data;
    }
    public function ListarMensionesApi() //USADO EN ESTUDIANTES, LISTA LAS MENCIONES
    {
        $data = Array (
            "0" => Array ("Mension" => "INSTRUMENTISTA","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            // // NUEVA MALLA
            //MARIA LUISA LUZIO Y FOLKLO,"Ifa"=>"MARIA LUISA LUZIO"RE
            "1" => Array ("Mension" => "CANTO LIRICO","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"INHABILITADO"),
            "2" => Array ("Mension" => "DIRECCION","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"INHABILITADO"),
            "3" => Array ("Mension" => "COMPOSICION","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"INHABILITADO"),
            "4" => Array ("Mension" => "INVESTIGACION MUSICAL","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"INHABILITADO"),
            "5" => Array ("Mension" => "COMPOSICION MUSICAL","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"INHABILITADO"),
            "6" => Array ("Mension" => "PRODUCCION MUSICAL","Malla"=>"NUEVA","Nivel"=>"LICENCIATURA","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"INHABILITADO"),
            //BELLAS ARTES ORURO ANTIGUA.............................,"Estado"=>"ACTIVO"..
            "7" => Array ("Mension" => "PINTURA","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "8" => Array ("Mension" => "ESCULTURA","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "9" => Array ("Mension" => "ARTES GRAFICAS","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "10" => Array ("Mension" => "CERAMICA","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "11" => Array ("Mension" => "DIBUJO","Malla"=>"ANTIGUA","Nivel"=>"NINGUNA","Pertenece"=>"ARTES PLASTICAS Y VISUALES","Ifa"=>"BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "12" => Array ("Mension" => "NINGUNA","Malla"=>"NUEVA","Nivel"=>"NINGUNA","Pertenece"=>"TODOS","Ifa"=>"TODOS","Estado"=>"ACTIVO"),
            //DEL FOLKLORE ORURO ANTIGUA
            "13" => Array ("Mension" => "CHARANGO","Pertenece"=>"MUSICA","Malla"=>"ANTIGUA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "14" => Array ("Mension" => "GUITARRA","Pertenece"=>"MUSICA","Malla"=>"ANTIGUA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "15" => Array ("Mension" => "QUENA","Pertenece"=>"MUSICA","Malla"=>"ANTIGUA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "16" => Array ("Mension" => "ZAMPOÑA","Pertenece"=>"MUSICA","Malla"=>"ANTIGUA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
        );
        return $data;
    }
    public function ListarInstrumentosApi() //USADO PARA INSTRUMENTOS DE ESPECIALIDAD
    {
        //ESTA PARTE DIFERENCIAR ENTRE IFAS
        $data = Array (
            "0" => Array ("InstEspecialidad" => "PIANO CLASICO","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "1" => Array ("InstEspecialidad" => "GUITARRA CLASICA","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "2" => Array ("InstEspecialidad" => "VIOLIN","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "3" => Array ("InstEspecialidad" => "VIOLA","Estado"=>"INHABILITADO","Ifa"=>"MARIA LUISA LUZIO"),
            "4" => Array ("InstEspecialidad" => "VIOLONCHELO","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "5" => Array ("InstEspecialidad" => "CONTRABAJO","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "6" => Array ("InstEspecialidad" => "CLARINETE","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "7" => Array ("InstEspecialidad" => "SAXOFON CLASICO","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "8" => Array ("InstEspecialidad" => "TROMPETA","Estado"=>"ACTIVO","Ifa"=>"MARIA LUISA LUZIO"),
            "9" => Array ("InstEspecialidad" => "TROMBON","Estado"=>"INHABILITADO","Ifa"=>"MARIA LUISA LUZIO"),
           "10" => Array ("InstEspecialidad" => "ACORDEON","Estado"=>"INHABILITADO","Ifa"=>"MARIA LUISA LUZIO"),
           "11" => Array ("InstEspecialidad" => "CHARANGO","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO"),
           "12" => Array ("InstEspecialidad" => "GUITARRA","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO"),
           "13" => Array ("InstEspecialidad" => "QUENA","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO"),
           "14" => Array ("InstEspecialidad" => "ZAMPOÑA","Estado"=>"ACTIVO","Ifa"=>"DEL FOLKLORE ORURO"),
        );
        return $data;
    }
    public function ListarNiveles()
    {
        $data = Array (
            //NUEVA MALLA
            "0" => Array ("Nivel" => "TECNICO MEDIO","Pertenece"=>"TODOS","Ifa"=>"TODOS","Estado"=>"ACTIVO"),
            "1" => Array ("Nivel" => "TECNICO SUPERIOR","Pertenece"=>"TODOS","Ifa"=>"MARIA LUISA LUZIO o BELLAS ARTES ORURO","Estado"=>"ACTIVO"),
            "2" => Array ("Nivel" => "LICENCIATURA","Pertenece"=>"TODOS","Ifa"=>"TODOS","Estado"=>"INHABILITADO"),
            "3" => Array ("Nivel" => "BASICO","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "4" => Array ("Nivel" => "INICIAL","Pertenece"=>"MUSICA","Ifa"=>"DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
            "5" => Array ("Nivel" => "INICIACION EN MUSICA CLASICA/ACADEMICA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "6" => Array ("Nivel" => "BASICO EN MUSICA CLASICA/ACADEMICA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "7" => Array ("Nivel" => "INTERMEDIO EN MUSICA CLASICA/ACADEMICA","Pertenece"=>"MUSICA","Ifa"=>"MARIA LUISA LUZIO","Estado"=>"ACTIVO"),
            "8" => Array ("Nivel" => "CAPACITACION","Pertenece"=>"BELLAS ARTES ORURO MUSICA","Ifa"=>"BELLAS ARTES ORURO o DEL FOLKLORE ORURO","Estado"=>"ACTIVO"),
        );
        return $data;
    }
    // #endregion

    public function ListarCursosPostulantesApi()
    {
        //LISTA DE CURSOS
        // CAPACITACION Y SUPERIOR
        $data = Array (
            "0" => Array ("NivelCurso" => "NIVEL SUPERIOR (Diploma de Bachiller)",) ,
            "1" => Array ("NivelCurso" => "NIVEL INTERMEDIO (15 a 17 años)",),
            "2" => Array ("NivelCurso" => "NIVEL BASICO (12 a 14 años)",),
            "3" => Array ("NivelCurso" => "NIVEL INICIACION (9 a 11 años)",)
        );
        return $data;

    }
    public function ListarAbreviacionDptosApi()
    {
        //LISTA DE CURSOS
        // CAPACITACION Y SUPERIOR
        $data = Array (
            "0" => Array ("Dpto" => "OR",) ,
            "1" => Array ("Dpto" => "LP",),
            "2" => Array ("Dpto" => "SC",),
            "3" => Array ("Dpto" => "PT",),
            "4" => Array ("Dpto" => "CH",),
            "5" => Array ("Dpto" => "CBBA",),
        );
        return $data;
    }

    public function ListarMensionesModernasApi()
    {
        $data = Array (
            // "0" => Array ("Mension" => "INSTRUMENTISTA",)
        );
        return $data;
    }
    public function ListarHorariosCapacitacionApi()
    {
        $data = Array (
            "0" => Array ("Turno" => "MAÑANA","Hora" => "(8:30hrs a 11:00hrs)"),
            "1" => Array ("Turno" => "TARDE","Hora" => "(17:00hrs a 19:40hrs)"),
            //"2" => Array ("Turno" => "NOCHE","Hora" => "ANTIGUOS")
        );
        return $data;
    }
    public function ListarHorariosSuperiorApi()
    {
        $data = Array (
            "0" => Array ("Turno" => "MAÑANA","Hora" => "(8:00hrs a 13:00hrs)"),
            "1" => Array ("Turno" => "TARDE","Hora" => "(14:00hrs a 18:30hrs)"),
            "2" => Array ("Turno" => "NOCHE","Hora" => "(18:00hrs a 22:30hrs")
        );
        return $data;
    }


    public function ListarInstrumentosModernosApi()
    {

        $data = Array (
            // "0" => Array ("InstEspecialidad" => "CANTO MODERNO",),
            // "1" => Array ("InstEspecialidad" => "BAJO ELECTRICO",),
            // "2" => Array ("InstEspecialidad" => "GUITARRA ACUSTICA MODERNA",),
            // "3" => Array ("InstEspecialidad" => "PIANO MODERNO",),
            // "4" => Array ("InstEspecialidad" => "SAXOFON MODERNO",)
        );
        return $data;
    }
    public function ListarCategoriasApi()
    {

        $data = Array (

            "0" => Array ("Category" => "NUEVO",),
            "1" => Array ("Category" => "ANTIGUO",)
            // "0" => Array ("Category" => "POSTULANTE",),//SERIA PARA AQUELLAS PERSONAS Q DARAN EXAMEN DE ADMISION
            // "1" => Array ("Category" => "OTROS",), //SERIA PARA AQUELLOS TALLERES
            // "2" => Array ("Category" => "NUEVO",),
            // "3" => Array ("Category" => "ANTIGUO",),
            // "4" => Array ("Category" => "NUEVO (resagado)",),
            // "5" => Array ("Category" => "ANTIGUO (resagado)",)
        );
        return $data;
    }

    public function ConsultarApi(Request $request)
    {

        if (str_contains($request->consultasql, 'encriptar')) {
            //CONSULTA QUE SITVE PARA ENCRIPTAR CONTRASEÑAS
            //INSTRUCCIONES:
            // paso 1: cambiar a los estudiantes su contraseña en grupo si desea
            // UPDATE estudiantes SET Password = 'gg123' WHERE CI = '12345';
            // paso 2: ver algunos datos del estudiante ( no olvidar id)
            // select * from estudiantes where CI = '12345'
            // paso 3: encriptar la contraseña de ese grupo
            // "select * from estudiantes where CI = '12345' -- encriptar"
            // Acciones a realizar si la palabra "contrasenia" está presente en el texto
            $data = DB::select($request->consultasql);
            foreach ($data as $row) {
                // Asegúrate de que el atributo "password" esté presente en la fila
                if (isset($row->Password)) {
                    // Obtén el ID del usuario y el hash de la nueva contraseña
                    $userId = $row->id;
                    $hashedPassword = Hash::make($row->Password);

                    // Actualiza la contraseña en la base de datos utilizando el modelo User
                    Estudiantes::where('id', $userId)->update(['Password' => $hashedPassword]);
                }
            }
            $data = DB::select($request->consultasql);
        } else {
            // Acciones a realizar si la palabra "contrasenia" NO está presente en el texto
            //CONSULTA NORMAL
            $data = DB::select($request->consultasql);
        }


        return $data;
    }
    public function ConsultarApiCursosEst(Request $request)
    {
        //ESTE CODIGO ES PARA HACER UNA VALIDACION TOTAL AL AÑADIR DOCENTE A UN CURSO
        $DataAdmin = Administrativos::where('id','=', 5)->get();
        $data = DB::select($request->consultasql);
            foreach ($data as $d) {
                // $Newdata = $d;
                $curso_admin = Administrativos_Cursos::where('Curso_id','=',$d->curso_id)->first();
                if ($curso_admin != "" || $curso_admin !=null) {
                    //SI EXISTE
                    $Newdata['Ap_Paterno_est'] = $d->Ap_Paterno;
                    $Newdata['Ap_Materno_est'] = $d->Ap_Materno;
                    $Newdata['Ap_Nombre_est'] = $d->Nombre;
                    $Newdata['curso_id'] = $d->curso_id;
                    $Newdata['estudiante_id'] = $d->estudiante_id;
                    $Newdata['NombreCurso'] = $d->NombreCurso;
                    $Newdata['NivelCurso'] = $d->NivelCurso;
                    $Newdata['Tipo'] = $d->Tipo;
                    $Newdata['Primero'] = $d->Primero;
                    $Newdata['Segundo'] = $d->Segundo;
                    $Newdata['Tercero'] = $d->Tercero;
                    $Newdata['Cuarto'] = $d->Cuarto;
                    $Newdata['Promedio'] = $d->Promedio;
                    $admin = Administrativos::where('id','=',$curso_admin->Admin_id)->first();
                    $Newdata['Foto'] = $admin->Foto;
                    $Newdata['Ap_Paterno'] = $admin->Ap_Paterno;
                    $Newdata['Ap_Materno'] = $admin->Ap_Materno;
                    $Newdata['Nombre'] = $admin->Nombre;
                }
                else {
                    //NO EXISTE
                    $Newdata['Ap_Paterno_est'] = '';
                    $Newdata['Ap_Materno_est'] = '';
                    $Newdata['Nombre_est'] = '';
                    $Newdata['curso_id'] = '';
                    $Newdata['estudiante_id'] = '';
                    $Newdata['NombreCurso'] = '';
                    $Newdata['NivelCurso'] = '';
                    $Newdata['Tipo'] = '';
                    $Newdata['Primero'] = '';
                    $Newdata['Segundo'] = '';
                    $Newdata['Tercero'] = '';
                    $Newdata['Cuarto'] = '';
                    $Newdata['Promedio'] = '';


                    //DATOS DEL DOCENTE
                    $Newdata['Foto'] = "";
                    $Newdata['Ap_Paterno'] = "";
                    $Newdata['Ap_Materno'] = "";
                    $Newdata['Nombre'] = "";

                }
                $Lista[] = $Newdata;
            }

        return $Lista;
    }
    public function ConsultarApiUniqueCI(Request $request)
    {

        // //obtengo la fila del curso deseado pero solo por su Id  del curso
        //     $CursoData = Curso::where('NivelCurso','=', $request->NivelCurso)->first();
        //     //obtener la lista de los estudiantes pero solo por su estudiante_id ...
        //     //DIGAMOS UN ESTUDIANTE ESTA EN SEGUNDO MEDIO ENTONCES HABRA 5 DEL MISMO YA Q EL CURSO TIENE 5 MATERIAS
        //     $CalificacionesData = Calificaciones::where('curso_id','=', $CursoData->id)->get();
        //     //ELIMINAR VALORES DUPLICADOS POR estudiante_id
        //     $CalificacionesData = $CalificacionesData->unique('estudiante_id');
        //     $Lista = array();
        //     foreach ($CalificacionesData as $C) {
        //         $EstudiantesData = Estudiantes::where('id','=', $C->estudiante_id)->first();
        //         $Lista[] = $EstudiantesData;
        //     }
        // //     return $Lista;
        // $dataEst = (DB::select($request->consultasql))->unique(estudiante_id);

        // return $dataEst;
    }
    public function FechasRetiroApi() //USADO EN ESTUDIANTES, LISTA LAS MENCIONES
    {
        $data = Array (
            "0" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"PRIMER","Fecha"=>"26/04/2024",),
            "1" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"SEGUNDO","Fecha"=>"02/07/2024",),
            "2" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"TERCER","Fecha"=>"01/10/2024",),
            "3" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"CUARTO","Fecha"=>"29/11/2024",),
            "4" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"SEMESTRALIZADO","Fecha"=>"29/11/2024",),
            "5" => Array ("Ifa" => "BELLAS ARTES ORURO","Bimestre"=>"DEFAULT","Fecha"=>"29/11/2024",),

            "6" => Array ("Ifa" => "MARIA LUISA LUZIO","Bimestre"=>"PRIMER","Fecha"=>"01/01/2024",),
            "7" => Array ("Ifa" => "MARIA LUISA LUZIO","Bimestre"=>"SEGUNDO","Fecha"=>"01/01/2024",),
            "8" => Array ("Ifa" => "MARIA LUISA LUZIO","Bimestre"=>"TERCER","Fecha"=>"01/01/2024",),
            "9" => Array ("Ifa" => "MARIA LUISA LUZIO","Bimestre"=>"CUARTO","Fecha"=>"01/01/2024",),
            "10" => Array ("Ifa" => "MARIA LUISA LUZIO","Bimestre"=>"DEFAULT","Fecha"=>"01/01/2024",),

            "11" => Array ("Ifa" => "DEL FOLKLORE ORURO","Bimestre"=>"PRIMER","Fecha"=>"28/04/2024",),
            "12" => Array ("Ifa" => "DEL FOLKLORE ORURO","Bimestre"=>"SEGUNDO","Fecha"=>"28/06/2024",),
            "13" => Array ("Ifa" => "DEL FOLKLORE ORURO","Bimestre"=>"TERCER","Fecha"=>"13/09/2024",),
            "14" => Array ("Ifa" => "DEL FOLKLORE ORURO","Bimestre"=>"CUARTO","Fecha"=>"13/11/2024",),
            "15" => Array ("Ifa" => "DEL FOLKLORE ORURO","Bimestre"=>"DEFAULT","Fecha"=>"14/11/2024",),
        );
        return $data;
    }
}
