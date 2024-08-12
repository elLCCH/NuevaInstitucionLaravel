<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FileUploadController;

/*HORARIO */
Route::resource('Horario', 'App\Http\Controllers\HorariosController');
Route::post('updateHorario/{id}', 'App\Http\Controllers\HorariosController@updateHorario');
/*PLANES */
Route::resource('Plan', 'App\Http\Controllers\PlanesController');
Route::post('updatePlan/{id}', 'App\Http\Controllers\PlanesController@updatePlan');
/*AÑO*/
Route::get('DeterminarInstituto', 'App\Http\Controllers\AnioController@DeterminarInstituto');
Route::get('ObtenerLogo', 'App\Http\Controllers\AnioController@ObtenerLogo');
Route::get('ObtenerMinisterio', 'App\Http\Controllers\AnioController@ObtenerMinisterio');
Route::get('indexAll', 'App\Http\Controllers\AnioController@indexAll');
Route::get('ObtenerGestionPredeterminada', 'App\Http\Controllers\AnioController@ObtenerGestionPredeterminada');
Route::resource('Anio', 'App\Http\Controllers\AnioController');
/*CURSO*/
Route::resource('curso', 'App\Http\Controllers\CursoController');
Route::post('cursoUnique', 'App\Http\Controllers\CursoController@CargarCursosUnique');
Route::post('ListaEstudiantes', 'App\Http\Controllers\CursoController@ListaEstudiantes');
Route::get('BuscarNivelCurso/{idCurso}', 'App\Http\Controllers\CursoController@BuscarNivelCurso');
Route::post('MateriasxEstudianteAnio', 'App\Http\Controllers\CursoController@MateriasxEstudianteAnio');
Route::post('HistorialAcademico', 'App\Http\Controllers\CursoController@HistorialAcademico');
Route::post('MateriasxAnioMallaNivelCurso', 'App\Http\Controllers\CursoController@MateriasxAnioMallaNivelCurso');
Route::post('MateriasxAnioNivel', 'App\Http\Controllers\CursoController@MateriasxAnioNivel');
Route::post('MateriasxAnioMalla', 'App\Http\Controllers\CursoController@MateriasxAnioMalla');
Route::post('ListaAgrupacionMateriasXCursos/{idAdmin}', 'App\Http\Controllers\CursoController@ListaAgrupacionMateriasXCursos');
Route::get('CargarSiglaUnique', 'App\Http\Controllers\CursoController@CargarSiglaUnique');
Route::post('ModificarBimestres', 'App\Http\Controllers\CursoController@ModificarBimestres');
Route::get('CursosUniqueSigla', 'App\Http\Controllers\CursoController@CursosUniqueSigla');
Route::get('CargarMalla', 'App\Http\Controllers\CursoController@CargarMalla');
Route::post('ClonarGestion', 'App\Http\Controllers\CursoController@ClonarGestion');
Route::post('RespaldarSiglas', 'App\Http\Controllers\CursoController@RespaldarSiglas');
Route::delete('cursoYPrerreq/{idCurso}', 'App\Http\Controllers\CursoController@cursoYPrerreq');
Route::get('EstadisticasInscripciones/{idGestion}', 'App\Http\Controllers\CursoController@EstadisticasInscripciones');
Route::post('materiasPorCurso', 'App\Http\Controllers\CursoController@materiasPorCurso');
Route::post('clonarMaterias', 'App\Http\Controllers\CursoController@clonarMaterias');
Route::post('editarParaleloMaterias', 'App\Http\Controllers\CursoController@editarParaleloMaterias');
Route::post('eliminarMateriasPorNivelCurso', 'App\Http\Controllers\CursoController@eliminarMateriasPorNivelCurso');
/*CALIFICACIONES*/
Route::resource('calificacion', 'App\Http\Controllers\CalificacionesController');
Route::post('EncontrarNivelCurso/{idEst}', 'App\Http\Controllers\CalificacionesController@EncontrarNivelCurso');
Route::post('EliminarEstudianteDelCurso', 'App\Http\Controllers\CalificacionesController@EliminarEstudianteDelCurso');
Route::get('ListarXCursoCalif/{idCurso}', 'App\Http\Controllers\CalificacionesController@ListarXCursoCalif');
Route::post('ListarForCentralizadorFinal', 'App\Http\Controllers\CalificacionesController@ListarForCentralizadorFinal');
Route::post('ListarForHeaderFinal', 'App\Http\Controllers\CalificacionesController@ListarForHeaderFinal');
Route::post('ObtenerFechaRetiro', 'App\Http\Controllers\CalificacionesController@ObtenerFechaRetiro');
Route::post('VerificarSegundaInstancia', 'App\Http\Controllers\CalificacionesController@VerificarSegundaInstancia');
Route::get('ListarEstadisticasCentralizadorFinal', 'App\Http\Controllers\CalificacionesController@ListarEstadisticasCentralizadorFinal');
Route::post('ActualizarCalificacionesCurso', 'App\Http\Controllers\CalificacionesController@ActualizarCalificacionesCurso');
Route::post('CulminarInscripciones', 'App\Http\Controllers\CalificacionesController@CulminarInscripciones');
Route::post('DeshacerCulminarInscripciones', 'App\Http\Controllers\CalificacionesController@DeshacerCulminarInscripciones');

/*ESTUDIANTES*/
Route::resource('Estudiante', 'App\Http\Controllers\EstudiantesController');
Route::post('ReiniciarContrasenias', 'App\Http\Controllers\EstudiantesController@ReiniciarContrasenias');
Route::post('EstudianteUpdate/{idest}', 'App\Http\Controllers\EstudiantesController@actualizar');
Route::post('EstudianteUpdatePlano/{idest}', 'App\Http\Controllers\EstudiantesController@EstudianteUpdatePlano');
Route::get('EstudianteCuadro', 'App\Http\Controllers\EstudiantesController@EstudianteCuadro');
Route::post('AbrirPDF', 'App\Http\Controllers\EstudiantesController@AbrirPDF');
Route::post('EliminarInactivos', 'App\Http\Controllers\EstudiantesController@EliminarInactivos');
Route::post('EstudianteAUTH', 'App\Http\Controllers\EstudiantesController@autentificar');
Route::get('ObtenerNombreCompleto/{idEst}', 'App\Http\Controllers\EstudiantesController@ObtenerNombreCompleto');
Route::post('OrdenarLista', 'App\Http\Controllers\EstudiantesController@OrdenarLista');
Route::get('indexSelection/{idest}', 'App\Http\Controllers\EstudiantesController@indexSelection');
Route::post('SeleccionarPorCI', 'App\Http\Controllers\EstudiantesController@SeleccionarPorCI');
Route::post('DetectarCantidadEstudiantesInscritos', 'App\Http\Controllers\EstudiantesController@DetectarCantidadEstudiantesInscritos');
Route::post('VerificarCursoParalelo', 'App\Http\Controllers\EstudiantesController@VerificarCursoParalelo');
Route::get('EstadisticasAsigEstudiantes/{idAnio}', 'App\Http\Controllers\EstudiantesController@EstadisticasAsigEstudiantes');
Route::get('EstudiantesAsignacionesInscritos/{idAnio}', 'App\Http\Controllers\EstudiantesController@EstudiantesAsignacionesInscritos');
Route::get('EstudiantesAsignacionesNoInscritos/{idAnio}', 'App\Http\Controllers\EstudiantesController@EstudiantesAsignacionesNoInscritos');
/*ADMINISTRATIVOS_CURSOS*/
Route::resource('AdminCursos', 'App\Http\Controllers\AdministrativosCursosController');
Route::post('EliminarAdminCursos', 'App\Http\Controllers\AdministrativosCursosController@EliminarAdminCursos');
Route::post('PruebaExistencia', 'App\Http\Controllers\AdministrativosCursosController@PruebaExistencia');
/*ADMINISTRATIVOS*/
Route::resource('Administrativo', 'App\Http\Controllers\AdministrativosController');
Route::post('AdministrativoAUTH', 'App\Http\Controllers\AdministrativosController@autentificar');
Route::get('DiferenciadorIndex', 'App\Http\Controllers\AdministrativosController@DiferenciadorIndex');
Route::post('EncontrarDocenteEspecialidad/{admin}', 'App\Http\Controllers\AdministrativosController@EncontrarDocenteEspecialidad');
Route::post('AdministrativoUpdate/{admin}', 'App\Http\Controllers\AdministrativosController@actualizar');
/*PUBLICACIONES*/
Route::resource('Publicacion', 'App\Http\Controllers\PublicacionesController');
Route::post('PublicacionUpdate/{admin}', 'App\Http\Controllers\PublicacionesController@actualizar');

/*EVENTOS*/
Route::resource('AreaEventos', 'App\Http\Controllers\AreaeventosController');
/*materias_materiales*/
Route::resource('materias_materiales', 'App\Http\Controllers\MateriasMaterialesController');
/*MATERIALES*/
Route::resource('materiales', 'App\Http\Controllers\MaterialesController');
Route::get('CargarMaterialesCurso/{idCurso}', 'App\Http\Controllers\MaterialesController@CargarMaterialesCurso');
Route::post('ListaAgrupacionForVisibility/{idMaterial}', 'App\Http\Controllers\MaterialesController@ListaAgrupacionForVisibility');
Route::post('EliminarVisibilityMaterial', 'App\Http\Controllers\MaterialesController@EliminarVisibilityMaterial');
Route::post('updatemateriales/{idMaterial}', 'App\Http\Controllers\MaterialesController@updatemateriales');
/*ARCHIVOS*/
Route::resource('archivos', 'App\Http\Controllers\ArchivosController');
Route::post('/uploadFile', [FileUploadController::class, 'uploadFile']);
Route::post('/deleteFile', [FileUploadController::class, 'deleteFile']);
/*PRERREQUISITOS*/
Route::resource('Prerrequisito', 'App\Http\Controllers\PrerrequisitosController');
Route::post('indexListarxGestion', 'App\Http\Controllers\PrerrequisitosController@indexListarxGestion');
Route::post('ObtenerPrerrequisitoxGestionyMalla', 'App\Http\Controllers\PrerrequisitosController@ObtenerPrerrequisitoxGestionyMalla');
/*EVENTOS*/
Route::resource('Evento', 'App\Http\Controllers\EventosController');
Route::get('EventoActivo', 'App\Http\Controllers\EventosController@EventoActivo');
Route::post('updateEvento/{idEven}', 'App\Http\Controllers\EventosController@updateEvento');
/*INS EVENTOS*/
Route::resource('InsEvento', 'App\Http\Controllers\InsEventosController');
Route::post('updateInsEvento/{idIns}', 'App\Http\Controllers\InsEventosController@updateInsEvento');
Route::get('ObtenerPostulantesEvento/{idEvento}', 'App\Http\Controllers\InsEventosController@ObtenerPostulantesEvento');
Route::post('SeleccionarPorCIidEvento', 'App\Http\Controllers\InsEventosController@SeleccionarPorCIidEvento');
// ENVIAR MAIL
Route::post('EnviarConfirmacion', 'App\Http\Controllers\EmailsController@Mails');
// Route::post('EnviarConfirmacion', function () {
//     $Correo = new RegistroCompletadoExitosamente;
//     // localStorage.getItem("CorreoSend");
//     Mail::to('luischoque.98oruro@gmail.com')->send($Correo);
//     // Mail::to($correo)->send($MensajeCorreo);
//     return 'MENSAJE ENVIADOS';
// });

//LISTAS DE APIs - APIS CONTROLLER
Route::get('ListarInstrumentosModernosApi', 'App\Http\Controllers\ApisController@ListarInstrumentosModernosApi');
Route::get('ListarMensionesModernasApi', 'App\Http\Controllers\ApisController@ListarMensionesModernasApi');
Route::get('ListarCursosPostulantesApi', 'App\Http\Controllers\ApisController@ListarCursosPostulantesApi');
Route::get('ListarCategoriasApi', 'App\Http\Controllers\ApisController@ListarCategoriasApi');
Route::get('DisponibilidadInscripciones', 'App\Http\Controllers\ApisController@DisponibilidadInscripciones');
Route::get('DisponibilidadInscripcionesNuevos', 'App\Http\Controllers\ApisController@DisponibilidadInscripcionesNuevos');
Route::get('VideoApi', 'App\Http\Controllers\ApisController@VideoApi');
Route::post('ConsultarApi', 'App\Http\Controllers\ApisController@ConsultarApi');
Route::post('ConsultarApiUniqueCI', 'App\Http\Controllers\ApisController@ConsultarApiUniqueCI');
Route::get('ListarHorariosSuperiorApi', 'App\Http\Controllers\ApisController@ListarHorariosSuperiorApi');
Route::get('ListarHorariosCapacitacionApi', 'App\Http\Controllers\ApisController@ListarHorariosCapacitacionApi');
Route::post('ConsultarApiCursosEst', 'App\Http\Controllers\ApisController@ConsultarApiCursosEst');
Route::get('ListarAbreviacionDptosApi', 'App\Http\Controllers\ApisController@ListarAbreviacionDptosApi');

// NUEVA GESTION
Route::get('ListarTipoMateriaApi', 'App\Http\Controllers\ApisController@ListarTipoMateriaApi');
Route::get('ListarCursosApi', 'App\Http\Controllers\ApisController@ListarCursosApi');
Route::get('ListarAreasApi', 'App\Http\Controllers\ApisController@ListarAreasApi');
Route::get('ListaAntiguaNuevaAutoCompletar', 'App\Http\Controllers\ApisController@ListaAntiguaNuevaAutoCompletar');
Route::get('ListarProgramasApi', 'App\Http\Controllers\ApisController@ListarProgramasApi');
Route::get('ListarCarrerasApi', 'App\Http\Controllers\ApisController@ListarCarrerasApi');
Route::get('ListarMensionesApi', 'App\Http\Controllers\ApisController@ListarMensionesApi');
Route::get('ListarInstrumentosApi', 'App\Http\Controllers\ApisController@ListarInstrumentosApi');
Route::get('ListarNiveles', 'App\Http\Controllers\ApisController@ListarNiveles');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

