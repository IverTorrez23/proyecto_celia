<?php
include_once("modelos/compraProducto.modelo.php");
include_once("modelos/almacen.modelo.php");
$datosad = $_SESSION["usuarioAdmin"];
if ($_SESSION["tipo_user"] != "admin") {
  echo '<script>
        window.location="inicio";
      </script>';
}

$idAlmacen = $_GET['alm'];

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Reportes
      <small>Inventario</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <!-- <li><a href="#">Examples</a></li> -->
      <li class="active">Reportes</li>
    </ol>
  </section>


  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Reportes De Inventario</h3>
        <button class="btn btn-danger btn-xs" onclick="window.open('impresiones/tcpdf/pdf/reporte_inventario.php?alm=<?php echo $idAlmacen; ?>')">PDF</button>
        <!--  <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                    title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div> -->

        <label>Almacen:</label>
        <select id="selectalmacen" name="selectalmacen" onchange="location.href='reporte-inventario?alm=' + this.value;">
          <option value="0" <?php echo (isset($_GET['alm']) && $_GET['alm'] == 0) ? 'selected' : ''; ?>>Todos</option>
          <?php
          $objemp = new Almacen();
          $result = $objemp->listarAlmacenActivos();
          while ($fila = mysqli_fetch_object($result)) {
            // Verificamos si este almacén está seleccionado en la URL
            $selected = (isset($_GET['alm']) && $_GET['alm'] == $fila->id_almacen) ? 'selected' : '';
          ?>
            <option value="<?php echo $fila->id_almacen; ?>" <?php echo $selected; ?>><?php echo $fila->nombre_almacen; ?></option>

          <?php
          }
          ?>

        </select>

      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped ">
          <thead>
            <tr>

              <th>Nº Lote</th>
              <th>Producto</th>
              <th>Codigo</th>
              <th>Almacen</th>
              <!--
                  <th>Marca</th>
                  <th>Categoria</th>-->
              <th>Stok</th>
              <th>Precio Venta</th>
              <!--<th>Precio Venta Fact.</th>-->

              <th>Costo Unitario Bs.</th>
              <th>Sub. Costo Bs.</th>


            </tr>
          </thead>

          <tbody>
            <?php
            $contador = 1;
            $totalSaldoInventario = 0;
            $obj = new Compra_Producto();
            if($idAlmacen>0){
              $resultado = $obj->reporteInventarioPorAlmacen($idAlmacen);
            }else{
              $resultado = $obj->reporteInventario();
            }
            
            while ($fila = mysqli_fetch_object($resultado)) {



            ?>
              <tr>

                <td><?php echo $fila->id_compra_producto; ?></td>
                <td><?php echo $fila->nombre_producto; ?></td>
                <td><?php echo $fila->codigo_producto; ?></td>
                <td><?php echo $fila->nombre_almacen; ?></td>
                 <!--<td><?php echo $fila->nombre_marca; ?></td>
                 <td><?php echo $fila->nombre_categoria; ?></td>-->
                <td><?php echo $fila->stock_actual; ?></td>

                <td><?php echo $fila->precio_venta_prod; ?></td>
                <!--<td><?php echo $fila->precio_venta_prod_Fact; ?></td>-->

                <td><?php echo $fila->precio_unit_compra; ?></td>

                <?php
                $costo_float = floatval($fila->precio_unit_compra);
                $montoInvent = $costo_float * $fila->stock_actual;
                $montoDecimal = number_format((float)$montoInvent, 2, '.', '');

                ?>
                <td><?php echo $montoDecimal; ?></td>




              </tr>
            <?php
              $contador++;
              $totalSaldoInventario = $totalSaldoInventario + $montoDecimal;
            }
            ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="7" style="text-align: center;"><b>Total</b> </td>
              <td><b><?php echo $totalSaldoInventario; ?></b></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <!-- /.box-body -->
      <div class="box-footer">
        Footer
      </div>
      <!-- /.box-footer-->
    </div>
    <!-- /.box -->

  </section>
  <!-- /.content -->



</div>
<!-- /.content-wrapper -->
<script type="text/javascript">
  $("#selectalmacen").change(function() {
    var idAlmacen = $(this).val();
    window.location.href = "reporte-inventario?alm=" + idAlmacen;
  });
</script>