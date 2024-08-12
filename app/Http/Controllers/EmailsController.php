<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\RegistroCompletadoExitosamente;
use Illuminate\Support\Facades\Mail;

class EmailsController extends Controller
{
    public function Mails(Request $request){
        // MANDAMOS TODO LO QUE RECIBIMOS HACIA REGISTROCOMPLETADO... ESO DEL MAIL 
        $CorreoMensaje = new RegistroCompletadoExitosamente($request->all());
        // Mail::to('luischoque.98oruro@gmail.com')->send($CorreoMensaje);
        Mail::to($request->input('Correo'))->send($CorreoMensaje);
        return 'MENSAJE ENVIADOS';
    }
}
