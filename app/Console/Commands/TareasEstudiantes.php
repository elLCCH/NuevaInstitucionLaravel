<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TareasEstudiantes extends Command
{
    //-------------- SE CREO GRACIAS A php artisan make:command TareasEstudiantes   .... y se ejecuta con este comando con: php artisan estudiantes:tareas

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estudiantes:tareas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SE EJECUTAN ALGUNAS TAREAS PARA TABLA ESTUDIANTES';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return 0;
        //ACTUALIZAR EDADES DE LOS ESTUDIANTES
        DB::select("UPDATE estudiantes e set e.Edad= TIMESTAMPDIFF(YEAR,e.FechNac,CURDATE())");
        $texto = "[".date("Y-m-d H:i:s")."]:SE ACTUALIZARON LAS EDADES";
        Storage::append("archivo.txt",$texto);
    }
}
