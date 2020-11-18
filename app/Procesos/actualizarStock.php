<?php
namespace Repositorios¡?
include_once "include/db/MysqliDb.php";
include_once "include/log.php";
include_once "include/config.php";
include_once "include/tangoTiendas_rest.php";
$conf = new Config();
$log = new log("log", "../logs/");
$parametros = $conf->get_parametros();
//Me conecto a la Base de Datos de la Tienda
$db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
$api = new tango_tiendas_rest();
$prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
$pageNumber = "1";
try {
    echo "Comienza proceso: " . date("d-m-Y (H:i:s)") . "<br />\n";
    $response = $api->get('Stock', '500', $pageNumber, '');
    if (isset($response['Data'])) {
        do {
            $msgLog = 'comienza proceso pagina ' . $pageNumber;
            $log->insert($msgLog, false, true, true);
            /*Aqui comienza la actualización de la Tabla de Stock de Virtuemart*/
            foreach ($response['Data'] as $datos) {
                $texto = 'ID:' . $datos['StoreNumber'] . ' Almacen:' . $datos['WarehouseCode'] . ' Codigo: ' . $datos['SKUCode'] . ' Cantidad: ' . $datos['Quantity'];
                echo $texto . "<br />\n";
                $stock['product_in_stock'] = $datos['Quantity'] - $datos['EngagedQuantity'];
                //Se procede a actualizar la tabla de STOCK
                //$datos['SKUCode'] = "'".intval(preg_replace('/[^0-9]+/', '', $datos['SKUCode']), 10)."'";
                $datos['SKUCode'] = (string)$datos['SKUCode'];
                $db->Where('product_sku', $datos['SKUCode']);
                if ($db->update($prefijo . constantes::get('TABLA_STOCK_SHOP'), $stock)) {
                    echo "Producto <b>" . $datos['SKUCode'] . "</b> stock actualizado" . "<br />\n";
                } else {
                    echo 'Error al actualizar la tabla de Stock de la tienda: ' . $db->getLastError();
                }
            }
            $pageNumber = $pageNumber + 1;
            $response = $api->get('Stock', '500', $pageNumber, '');
        } while (isset($response['Paging']['MoreData']));
    }
    //Porceso ultima pagina

    if (isset($response['Data'])) {
        echo '<b>comienza proceso última pagina ' . $pageNumber . '</b><br />\n';
        foreach ($response['Data'] as $datos) {
            $texto = 'ID:' . $datos['StoreNumber'] . ' Almacen:' . $datos['WarehouseCode'] . ' Codigo: ' . $datos['SKUCode'] . ' Cantidad: ' . $datos['Quantity'];
            echo $texto . '<br />\n';
            $stock['product_in_stock'] = $datos['Quantity'];
            //Se procede a actualizar la tabla de STOCK
            //$datos['SKUCode'] = intval(preg_replace('/[^0-9]+/', '', $datos['SKUCode']), 10);
            $datos['SKUCode'] = (string)$datos['SKUCode'];
            $db->where('product_sku', $datos['SKUCode']);
            if ($db->update($prefijo . constantes::get('TABLA_STOCK_SHOP'), $stock)) {
                echo "Producto <b>" . $datos['SKUCode'] . "</b> stock actualizado" . "<br />\n";
            } else {
                echo 'Error al actualizar la tabla de Stock de la tienda: ' . $db->getLastError();
            }
        }
    }
    $db->disconnect();
    echo '<br />Stock actualizado';
    echo '<br />Fin de procesamiento: ' . date("d-m-Y (H:i:s)") . "<br />\n";
} catch (Exception $e) {
    echo 'Hubo un error: ', $e->getMessage(), "<br />\n";
}