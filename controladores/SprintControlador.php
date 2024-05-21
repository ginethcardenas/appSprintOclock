<?php
require_once('../modelos/SprintModelo.php');
class SprintControlador
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new SprintModelo();
    }
    
    //funcion para crear sprint
    public function manejarCreacionSprint()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnCrearSprint'])) {
            $nombreSprint = $_POST['nameNombreSprint'];
            $fechaInicioSprint = $_POST['namefechaInicioSprint'];
            $fechaFinSprint = $_POST['namefechafinSprint'];
            $valorDiaLaboral = $_POST['nameDiaLaboral'];

            //validacion fechas
            $fechaInicioSprintValidacion = new Datetime($fechaInicioSprint);
            $fechaFinSprintValidacion = new Datetime($fechaFinSprint);
            $fechaActual = new Datetime();           
            
            if($fechaInicioSprintValidacion >= $fechaActual) {                
                $this->mostrarAlerta("La fecha de inicio no puede ser una fecha pasada.");
            } elseif($fechaInicioSprintValidacion >= $fechaFinSprintValidacion) {                             
                $this->mostrarAlerta("La fecha de inicio debe ser menor que la fecha de fin."); 
            } else{
                //si las fechas son validas calculamos diasHabiles                                
                $diasHabiles = $this->calcularDiasHabiles($fechaInicioSprint, $fechaFinSprint);
                //echo "Hay {$diasHabiles} días hábiles entre {$fechaInicioSprint} y {$fechaFinSprint}";

                $horasHabiles = $this->calcularHorasHabiles($diasHabiles, $valorDiaLaboral);
                //echo "En {$diasHabiles} días hábiles hay {$horasHabiles} horas habiles.";

                //llamamos al metodo de nuestro modelo
                $resultado = $this->modelo->crearSprint($nombreSprint, $fechaInicioSprint, $fechaFinSprint, $valorDiaLaboral, $diasHabiles, $horasHabiles);
                if($resultado) {
                    $this->mostrarAlerta("Sprint creado correctamente.");
                } else{
                    $this->mostrarAlerta("Error al crear el sprint");
                }
            }
        }
    }

    //funcion para actualizar sprint
    public function actualizarSprint()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnActualizarSprint'])) {
            //traemos el id como campo oculto para enviarlo por POST
            $id = $_POST['editarId'];

            $nombreSprint = $_POST['nameNombreSprint'];
            $fechaInicioSprint = $_POST['namefechaInicioSprint'];
            $fechaFinSprint = $_POST['namefechafinSprint'];

            $resultado = $this->modelo->actualizarSprint($id, $nombreSprint, $fechaInicioSprint, $fechaFinSprint);

            // Verificamos si se actualizó correctamente y enviamos la respuesta adecuada
            if ($resultado) {
                echo '<div class="alert alert-success">Sprint actualizado correctamente</div>';
            } else {
                echo '<div class="alert alert-danger">Error al actualizar el sprint</div>';
            }
        }
    }

    //funcion para eliminar sprint

    public function eliminarSprint()
    {
        if (isset($_GET["eliminarId"])) {
            $id = $_GET["eliminarId"];
            $resultado = $this->modelo->eliminarSprint($id);
            // Verificamos si se eliminó correctamente y enviamos la respuesta adecuada
            if ($resultado) {
                echo '<div class="alert alert-success">Sprint eliminado correctamente</div>';
            } else {
                echo '<div class="alert alert-danger">Error al eliminar el sprint</div>';
            }
        }
    }

    //funcion para listar sprint
    public function obtenerSprints()
    {
        $sprints = $this->modelo->obtenerSprints();
        return $sprints;
    }

    //funcion para mostrar alerta
    public function mostrarAlerta($mensaje) {
        $alerta = '<div id="alertaCreacionSprint" data-notify="container" class="col-11 col-md-4 alert alert-danger alert-with-icon" role="alert" 
                    data-notify-position="top-right" style="display: inline-block; margin: 0px auto; position: fixed; transition: all 0.5s ease-in-out 0s; 
                    z-index: 1060; top: 100px; right: 20px;" bis_skin_checked="1"><span data-notify="icon" class="bi bi-bell"></span>
                    <span data-notify="title"></span> <span data-notify="message"><strong>' . $mensaje . '</strong></span>
                </div>';
        $alerta .= '<script>
                        window.onload = function() {
                            var alertaCreacionSprint = document.getElementById("alertaCreacionSprint");
                            if (alertaCreacionSprint) {
                                setTimeout(function() {
                                    alertaCreacionSprint.style.opacity = "0";
                                    setTimeout(function() {
                                        alertaCreacionSprint.style.display = "none";
                                    }, 500); // 500 milisegundos = 0.5 segundos
                                }, 3000); // 3000 milisegundos = 3 segundos
                            }
                        };
                    </script>';
        echo $alerta;
    }

    //funcion calcularDiasHabiles entre fechaInicio y fechaFin
    public function calcularDiasHabiles($fechaInicio, $fechaFin) {
        // Crear objetos DateTime a partir de las fechas de inicio y fin
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
    
        // Asegurarse de que la fecha de inicio sea menor que la fecha de fin
        if ($inicio > $fin) {
            return "La fecha de inicio debe ser menor que la fecha de fin";
        }
    
        // Contador para los días hábiles
        $diasHabiles = 0;
    
        // Recorrer cada día entre la fecha de inicio y la fecha de fin
        while ($inicio <= $fin) {
            // Si el día es un día de la semana (lunes = 1, domingo = 7)
            if ($inicio->format('N') < 6) {
                $diasHabiles++;
            }
    
            // Avanzar al siguiente día
            $inicio->modify('+1 day');
        }
    
        return $diasHabiles;
    }

    public function calcularHorasHabiles($diasHabiles, $valorDiaLaboral){
        $horasHabiles = $diasHabiles * $valorDiaLaboral;
        return $horasHabiles;
    }
    
}
