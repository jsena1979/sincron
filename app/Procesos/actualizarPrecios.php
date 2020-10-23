<?php
include_once "include/db/MysqliDb.php";
include_once "include/log.php";
include_once "include/config.php";
include_once "include/tangoTiendas_rest.php";
error_reporting(E_ALL);
ini_set('display_errors', '1');
$conf = new Config();
$log = new log("log", "./logs/");
$parametros = $conf->get_parametros();
$prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
//Me conecto a la Base de Datos de la Tienda
$db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
$api = new tango_tiendas_rest();
$pageNumber = "1";
$msgLog = "";
$listaPrecio = "1";
if (isset($listaPrecio)) {
    $filter = "&filter=" . $listaPrecio;
}

$fechaUltimaConsulta = "";
if (isset($ultimaConsulta)) {
    $filter .= '&lastUpdate=' . $fechaUltimaConsulta;
}
/*echo $filter;
exit;*/
try {
    $response = $api->get('Price', '500', $pageNumber, $filter);
    if (isset($response['Data'])) {
        do {
            $msglog = "<br>\n/*************** ******************************************************/\n";
            $msglog .= "               Comienza proceso de Pagina Nro:" . $pageNumber."\n";
            $msglog .= "***********************************************************************.\n";
            echo $msglog . "\n";
            $log->insert($msgLog, false, false, true);
            /*Aqui comienza la actualización de la Tabla de Stock de Virtuemart*/
            foreach ($response['Data'] as $datos) {
                $texto = 'Lista de precios:' . $datos['PriceListNumber'] . ' SKU:' . $datos['SKUCode'] . ' Precio: ' . $datos['Price'];
                echo $texto;
                $data['product_price'] = $datos['Price'];
                //Se procede a actualizar la tabla de Precios
                $columnas = Array("product_sku", "virtuemart_product_id");
                $datos['SKUCode'] = intval(preg_replace('/[^0-9]+/', '', $datos['SKUCode']), 10);
                //$datos['SKUCode'] = "'".$datos['SKUCode']."'";
                $db->Where('product_sku', $datos['SKUCode']);
                $results = $db->get($prefijo . constantes::get('TABLA_STOCK_SHOP'), null, $columnas);

                if (isset($results[0]['product_sku'])) {
                    $db->Where('virtuemart_product_id', $results[0]["virtuemart_product_id"]);
                    if ($db->update($prefijo . constantes::get('TABLA_PRECIOS_SHOP'), $data)) {
                        echo "Producto <b>" . $datos['SKUCode'] . "</b> Precio actualizado" . '<br />\n';
                    } else {
                        echo 'Error al actualizar el PRECIOS de la Tienda: ' . $db->getLastError();
                    }
                }
            }
            $pageNumber = $pageNumber + 1;
            $response = $api->get('Price', '500', $pageNumber, $filter);
        } while (isset($response['Paging']['MoreData']));
    }
    //Porceso ultima pagina
    if (isset($response['Data'])) {
        echo '<b>comienza proceso última pagina ' . $pageNumber . '</b><br />';
        foreach ($response['Data'] as $datos) {
            $texto = 'Lista de precios:' . $datos['PriceListNumber'] . ' SKU:' . $datos['SKUCode'] . ' Precio: ' . $datos['Price'];
            echo $texto . '<br />';
            $data = Array(
                'product_price' => $datos['Price']
            );
            //Se procede a actualizar la tabla de Precios
            $columnas = Array("virtuemart_product_id");
            $datos['SKUCode'] = intval(preg_replace('/[^0-9]+/', '', $datos['SKUCode']), 10);
            //$datos['SKUCode'] = "'".$datos['SKUCode']."'";    
            $db->where('product_sku', $datos['SKUCode']);
            $results = $db->get($prefijo . constantes::get('TABLA_STOCK_SHOP'), null, $columnas);
            $db->where('virtuemart_product_id', $results[0]['virtuemart_product_id']);
            if ($db->update($prefijo . constantes::get('TABLA_PRECIOS_SHOP'), $data)) {
                echo "Producto <b>" . $datos['SKUCode'] . "</b> Precio actualizado" . '<br />\n';
            } else {
                echo 'Error al actualizar el PRECIOS de la Tienda: ' . $db->getLastError();
            }
        }
    }
    $db->disconnect();
    echo "/***********************************************<br>\n
                    Proceso  finalizado
    <br>\n*************************************************/<br>\n";
} catch (Exception $e) {
    echo 'Hubo un error: ', $e->getMessage(), "\n";
}
