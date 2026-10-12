<?php
session_start();
$fechaIni = $_GET['fini'] . ' 00:00:00';
$fechaFin = $_GET['ffin'] . ' 23:59:00';
$idalmacen = $_GET['idalmacen'];

// echo $fechaFin;
$_SESSION["frchaini"] = $fechaIni;
$_SESSION["fechafin"] = $fechaFin;
$_SESSION["idalmacen"] = $idalmacen;
include_once("../../modelos/empleado.modelo.php");
include_once("../../modelos/almacen.modelo.php");
include_once("../../modelos/venta.modelo.php");
include_once("../../modelos/cuotaVenta.modelo.php");

if ($idalmacen == 0) {
  $nombreVendedor = 'Todos';
} else {

  $objemep = new Almacen();
  $resultEmp = $objemep->mostarUnAlmacen($idalmacen);
  $filaemp = mysqli_fetch_object($resultEmp);
  $nombreEmp = $filaemp->nombre_almacen;
  $nombreVendedor = $nombreEmp;
}
?>
<!-- <label>Almacen seleccionado: <?php echo $nombreVendedor; ?> </label> -->
<table class="table table-bordered table-striped ">
  <thead>
    <tr>

      <th>Cod Venta</th>
      <th>Fecha</th>
      <th>Cliente</th>
      <th>Venta credito</th>
      <th>Cancelado</th>
      <th>Usuario</th>
      <th>Venta Total</th>
      <th>Ver</th>
      <!-- <th>Borrar</th> -->
    </tr>
  </thead>

  <tbody>
    <?php

    $contador = 1;
    $ganacia = 0;
    $sub_costo = 0;
    $GananciasTotales = 0;
    $totalMontoVentas = 0;
    $alertaColorfila = '';
    $colorTexto = '';
    $sumatodasVentas = 0;
    $montoaunenInversion = 0;
    $sumaMontoaunenInversion = 0;
    $saldoPorCancelar =0;
    $sumaCuotaVenta=0;
    $obj = new Venta();
    $objCuotaVenta = new CuotaVenta();
    $resultado = $obj->ReporteVentasActivas($fechaIni, $fechaFin);
    
    while ($fila = mysqli_fetch_object($resultado)) {

    if($fila->venta_credito == 1){
      $resultCuota = $objCuotaVenta->sumatoriaDeCuotaDeVenta($fila->idventa);
        $filaCuota = mysqli_fetch_object($resultCuota);
        $sumaCuotaVenta = $filaCuota->sumaCuotas;
        $saldoPorCancelar1 = $fila->monto_venta - $sumaCuotaVenta;
        $saldoPorCancelar = number_format((float)$saldoPorCancelar1, 2, '.', '');
    }
      
    ?>
      <tr style="<?php echo ($fila->venta_credito == 1) ? 'font-style: italic; background-color: #88828288;' : ''; ?>">

        <td><a target="_blank" href="impresiones/tcpdf/pdf/nota_venta.php?codventa=<?php echo $fila->idventa; ?>"> <?php echo $fila->idventa; ?></a></td>
        <td><?php echo $fila->fecha_venta; ?></td>

        <td><?php echo $fila->nameCliente; ?></td>


        <td>
          <?php if ($fila->venta_credito == 1): ?>
            <i class="fa fa-check-circle" style="color: green;"></i>
          <?php else: ?>
            <i class="fa fa-circle" style="color: gray;"></i>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($fila->cancelado == 1): ?>
            <i class="fa fa-check-circle" style="color: green;"></i>
          <?php else: ?>
            <button class="btn btn-warning btn-xs" style="cursor:pointer;" onclick="modalCuota(<?php echo $fila->idventa; ?>,<?php echo $fila->monto_venta; ?>,<?php echo $saldoPorCancelar; ?>)" data-toggle="modal" data-target="#modalCuota">Por cancelar</button>
          <?php endif; ?>
        </td>
        <!-- <td><?php echo number_format((float)$montoaunenInversion, 2, '.', ''); ?></td> -->
        <td><?php echo $fila->Usuario; ?></td>
        <td><?php echo $fila->monto_venta; ?></td>
        <!-- <td><?php echo $saldoPorCancelar; ?></td> -->
        <td><button class="btn btn-info"><a target="_blank" href="detalle-venta?codventa=<?php echo $fila->idventa; ?>"><i class="fa fa-eye"></i></a> </button> </td>
        <!-- <td>
          <div class="btn-group">
            <button class="btn btn-danger" data-toggle="modal" data-target="#modalElimVenta" onclick="CargarinfoVentaElim('<?php echo $fila->idventa; ?>')"><i class="fa fa-times"></i></button>
          </div>
        </td> -->
      </tr>

    <?php
      //Cuando es una venta a credito
      if($fila->venta_credito==0){
        $sumatodasVentas = $sumatodasVentas + $fila->monto_venta;
      }
       
    }//fin del while 
    ?>


    <?php
      // Consultamos o verificamos si esta venta tiene cuotas pagadas
      $montoCuotas=0;
      $cuotas = $objCuotaVenta->ReporteCuotaVentasActivas($fechaIni, $fechaFin);

      if (!empty($cuotas)) {
      ?>
    <!-- FILA SEPARADORA (Ajusta colspan="5" al total de columnas de tu tabla) -->
        <tr class="separador-cuotas">
          <td colspan="8">
            <i class="fa fa-level-down-right"></i> <strong> Historial de Cuotas Pagadas</strong>
          </td>
        </tr>
      <!-- FILAS DE LAS CUOTAS PAGADAS -->
        <?php while ($cuota= mysqli_fetch_object($cuotas)) { ?>
          <tr class="fila-cuota">
            <td><?php echo $cuota->idventa; ?></td> <!-- Espacio o sangría para indicar jerarquía -->
            <td><?php echo $cuota->fecha_cuota; ?></td>
            <td><?php echo $cuota->nameCliente; ?></td>
            <td>---</td>
            <td>---</td>
            <td><?php echo $cuota->Usuario; ?></td>
            <td>Bs. <?php echo number_format($cuota->monto_cuota, 2); ?></td>
            <td><span class="label label-success">Pagado</span></td>
          </tr>
        <?php 
         $montoCuotas = $montoCuotas + $cuota->monto_cuota;
        } 
      } // Fin del if cuotas


      ?>


  </tbody>
  <tfoot>
    <tr>
      <td colspan="6" style="text-align: center;"><b>Totales</b> </td>
      <td><b><?php echo number_format((float)$sumatodasVentas + $montoCuotas, 2, '.', ''); ?></b></td>
    </tr>
  </tfoot>
</table>

<script type="text/javascript">
  /*========================CARGAMOS LOS DATOS PARA ELIMINAR=========================*/
  function CargarinfoVentaElim(idVenta) {
    $('#idventa').val(idVenta);
    $('#labelcodigoelim').text(idVenta);

  }
</script>