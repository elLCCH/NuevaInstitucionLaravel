//PARA INICIAR ANIO, REESTABLECER LOS ATRIBUTOS DE TODOS
UPDATE estudiantes SET Estado = 'INACTIVO', Observacion = 'NO INSCRITO', Categoria = 'ANTIGUO',FechInsc = NULL,LastAcciones = NULL,Verificacion = NULL,Notas = NULL,Anotaciones = NULL,UltimoAdmin = NULL;

    EN CASO DE SEMESTRAL/II
    UPDATE estudiantes SET Estado = 'INACTIVO', Observacion = 'NO INSCRITO', Categoria = 'ANTIGUO',FechInsc = NULL,LastAcciones = NULL,Verificacion = NULL,Notas = NULL,Anotaciones = NULL,UltimoAdmin = NULL where Estado = 8;
