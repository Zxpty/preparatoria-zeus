<?php
include_once("auth.php");
include_once("src/components/parte_superior.php");

include('./config/conexion.php');
$sql = "select * from periodo";
$f = mysqli_query($cn, $sql);
include('modales_periodo.php');
?>
<link rel="stylesheet" src="style.css" href="./bootstrap/bootstrap.css">
<link rel="stylesheet" src="style.css" href="./datatables/datatables.css">
<link rel="icon" href="src/assets/images/logo-zeus.png">
<div class="container-page">
    <div>
        <p>Zeus<span> / Periodo</span></p>
        <h3>Periodo</h3>
    </div>
    <button class="periodo btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModalPeriodo" data-bs-whatever="@mdo" style="cursor: pointer;">Registrar</button>
    <div class="container-table" style="background-color: #fff;">
        <div class="col-md-12">
            <table class="table table-striped" id="table_periodo">
                <thead align="center" class="" style="color: #fff; background-color:#010133;">
                    <tr>
                        <th class="text-center">Periodo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    while ($r = mysqli_fetch_assoc($f)) {
                    ?>
                        <?php
                        deleteModalPeriodo($r['id_pe']);
                        ?>
                        <tr>
                            <td align="center"><?php echo $r['nombre_pe'] ?></td>
                            <td align="center"><?php
                                                $estado = $r['estado_pe'];
                                                $button = '<button class="' . ($estado === "ACTIVO" ? 'active-button' : 'inactive-button') . '">' . $estado . '</button>';
                                                echo $button;
                                                ?>
                            </td>
                            <td>
                                <center>
                                    <a class="btn btn-sm btn-primary btn-circle" data-bs-toggle="modal" data-bs-target="#editarModalPeriodo" data-bs-whatever="@mdo" target="_parent" onclick="cargarInfoEdit({
                                                        'id': '<?php echo $r['id_pe'] ?? ''; ?>',
                                                        'nombre': '<?php echo $r['nombre_pe'] ?? ''; ?>',
                                                        'estado': '<?php echo $r['estado_pe'] ?? ''; ?>'
                                                    });">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn-danger btn-circle " target="_parent" data-bs-toggle="modal" data-bs-target="#DeleteModalPeriodo<?php echo $r['id_pe'] ?>" data-bs-whatever="@mdo">
                                        <i class=" fas fa-trash"> </i>
                                    </a>
                                </center>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php

    if (isset($_SESSION['deleted_cycle'])) {
        echo '<div id="alertDiv" class="alert alert-warning alert-dismissible fade show" role="alert" >
        <strong>¡Éxito!</strong> ' . $_SESSION['deleted_cycle'] . '
      </div>';
        unset($_SESSION['deleted_cycle']);
        echo '<script>
            const alertDiv = document.getElementById("alertDiv");
            setTimeout(() => {
                if (alertDiv) {
                    alertDiv.style.display = "none";
                }
            }, 2000);
          </script>';
    }

    if (isset($_SESSION['alert_message'])) {
        $alertMessage = $_SESSION['alert_message'];
        unset($_SESSION['alert_message']);
        echo "<script>
                    alert('$alertMessage');
                </script>";
    }

    ?>
</div>



<?php
include_once("src/components/parte_inferior.php")
?>
<script>
    const cargarInfoEdit = (data) => {
        document.getElementById('id_periodo').value = data.id;
        document.getElementById('edit_nombre_periodo').value = data.nombre;
        document.getElementById('estado').value = data.estado;
        console.log(data)
    }

    function createCellPos(n) {
        var ordA = 'A'.charCodeAt(0);
        var ordZ = 'Z'.charCodeAt(0);
        var len = ordZ - ordA + 1;
        var s = "";

        while (n >= 0) {
            s = String.fromCharCode(n % len + ordA) + s;
            n = Math.floor(n / len) - 1;
        }

        return s;
    }
</script>

<script src="src/assets/js/datatableIntegration.js"></script>

<script>
    initializeDataTable('#table_periodo');
</script>