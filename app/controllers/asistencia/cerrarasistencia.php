<?php
include('../../../config/conexion.php');

// Arreglo para almacenar los resultados
$cerrar = array();


//VERIFICAR SI HAY CICLO Y TURNO PARA LA HORA
$sql_ciclo = "SELECT COUNT(c.nombre_ci) AS total_ciclo 
            FROM ciclo c
            INNER JOIN detalle_ciclo_turno dt ON c.id_ci = dt.id_ci
            INNER JOIN turno t ON dt.id_tu = t.id_tu 
            WHERE CURRENT_DATE BETWEEN c.fini_ci AND c.ffin_ci
            AND NOW() BETWEEN CONCAT(CURRENT_DATE, ' ', t.hent_tu) AND CONCAT(CURRENT_DATE, ' ', t.hsal_tu)
            AND c.estado_ci = 'ACTIVO'
            AND t.estado_tu = 'ACTIVO'";

$f_ciclo = mysqli_query($cn, $sql_ciclo);
$r_ciclo = mysqli_fetch_assoc($f_ciclo);
$numero_ciclo = $r_ciclo['total_ciclo'];

if($numero_ciclo > 0){
    $sql_existe = "SELECT *
                FROM  turno t 
                where now() BETWEEN CONCAT(CURRENT_DATE, ' ', t.hent_tu) AND CONCAT(CURRENT_DATE, ' ', t.hsal_tu)";

    $f_existe = mysqli_query($cn, $sql_existe);
    $r_existe = mysqli_fetch_assoc($f_existe);

    $temp = $f_existe;

    if(mysqli_num_rows($temp) > 0){

        //TOTAL DE ALUMNOS
        $sql_alumnos = "SELECT 
                            COUNT(m.id_ma) AS total_alu, 
                            COALESCE(COUNT(a.id_ma), 0) AS total_asi
                        FROM 
                            matricula m
                            INNER JOIN ciclo c ON m.id_ci = c.id_ci 
                            INNER JOIN detalle_ciclo_turno dt ON c.id_ci = dt.id_ci
                            INNER JOIN turno t ON dt.id_tu = t.id_tu
                            LEFT JOIN asistencia a ON m.id_ma = a.id_ma
                                                    AND DATE(a.fecha_as) = CURRENT_DATE
                                                    AND a.fecha_as > CONCAT(CURRENT_DATE, ' ', t.hent_tu)
                        WHERE 
                            NOW() BETWEEN CONCAT(CURRENT_DATE, ' ', t.hent_tu) AND CONCAT(CURRENT_DATE, ' ', t.hsal_tu)
                            AND m.estado_ma = 'ACTIVO'";
        $f_alumnos = mysqli_query($cn, $sql_alumnos);
        $r_alumnos = mysqli_fetch_assoc($f_alumnos);

        $total_alu = $r_alumnos['total_alu'];
        $total_asi = $r_alumnos['total_asi'];

        if($total_alu > $total_asi){
            date_default_timezone_set('America/Lima');
            $toleranciaMinutos = $r_existe['tolerancia_tu'];
            $horaActual = date('H:i:s'); // Cambia esta línea según la hora que desees probar


            // Convertir la hora de inicio y fin del turno a formato de hora
            $hentTuHoraFormato = date('H:i:s', strtotime($r_existe['hent_tu']));
            $hsalTuHoraFormato = date('H:i:s', strtotime($r_existe['hsal_tu']));


            $HoraTolerancia = date('H:i:s', strtotime($hentTuHoraFormato . " +$toleranciaMinutos minutes"));
            $HoraLimite= date('H:i:s', strtotime($HoraTolerancia . " +45 minutes"));

            if($horaActual > $HoraLimite){

                $sql_ina = "SELECT 
                                m.id_ma, 
                                COALESCE(COUNT(a.id_ma), 0) AS asist
                            FROM 
                                matricula m
                                INNER JOIN ciclo c ON m.id_ci = c.id_ci 
                                INNER JOIN detalle_ciclo_turno dt ON c.id_ci = dt.id_ci
                                INNER JOIN turno t ON dt.id_tu = t.id_tu
                                LEFT JOIN asistencia a ON m.id_ma = a.id_ma 
                                                                AND DATE(a.fecha_as) = CURRENT_DATE
                                                                AND a.fecha_as > CONCAT(CURRENT_DATE, ' ', t.hent_tu)
                            WHERE NOW() BETWEEN CONCAT(CURRENT_DATE, ' ', t.hent_tu) AND CONCAT(CURRENT_DATE, ' ', t.hsal_tu)
                                AND m.estado_ma = 'ACTIVO'
                            GROUP BY m.id_ma";

                $f_ina = mysqli_query($cn, $sql_ina);

                while($r_ina = mysqli_fetch_assoc($f_ina)){

                    if($r_ina['asist'] == 0){
                        $id_ina = $r_ina['id_ma'];
                        $sql_eject = "INSERT INTO asistencia (id_ma, estado_as) VALUES ($id_ina, 'FALTA')";
                        mysqli_query($cn, $sql_eject);
                    }
                }

                $cerrar[] = array(
                    'escenario' => 2,
                    'mensaje' => "LAS FALTAS HAN SIDO REGISTRADAS",
                    'texto' => "Se han registrado las inasistencias"
                );

            }else{
                $cerrar[] = array(
                    'escenario' => 1,
                    'mensaje' => "CIERRE DENEGADO",
                    'texto' => "Aun se pueden registrar asistencias (Hora de cierre: ".$HoraLimite.")"
                );
            }
        }else{
            $cerrar[] = array(
                'escenario' => 1,
                'mensaje' => "¡YA ESTAN REGISTRADOS TODOS LOS ALUMNOS!",
                'texto' => "Ya se genero todas las asistencias"
            );
        }
    }else{
        $cerrar[] = array(
            'escenario' => 1,
            'mensaje' => "HORARIO DENEGADO",
            'texto' => "No existe turno de asistencia"
        );
    }
}else{
    $cerrar[] = array(
        'escenario' => 1,
        'mensaje' => "NO HAY CICLO ACTIVO",
        'texto' => "No existe turno de asistencia"
    );
}





// Convertir el arreglo a formato JSON y enviarlo como respuesta
echo json_encode($cerrar);
?>
