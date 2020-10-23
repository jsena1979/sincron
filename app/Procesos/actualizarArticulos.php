<?php
/**************************************************
 * Permite actualizar el estado de los articulos
 * Lee edsde la API de tango tiendas y actualiza
 * la tabla de virtuemart_products
 ************************************************/

include_once "include/db/MysqliDb.php";
include_once "include/log.php";
include_once "include/config.php";
include_once "include/tangoTiendas_rest.php";
$conf = new Config();
$log = new log("log", "../logs/");
$parametros = $conf->get_parametros();
$prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
//Me conecto a la Base de Datos de la Tienda
$db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
$api = new tango_tiendas_rest();
$pageNumber = "1";
$filter = '';
echo 'Comienza proceso: '.date("d-m-Y (H:i:s)");
try {
    $response = $api->get('Product', '500', $pageNumber, $filter);
    if (isset($response['Data'])) {
        do {
            $msgLog =  'Comienza proceso pagina ' . $pageNumber ;
            $log->insert( $msgLog, false, true, true);
            /*Aqui comienza la importacion de la Tabla de Articulos de Tango*/
            foreach ($response['Data'] as $datos) {
                $texto = 'ID:' . $datos['SKUCode'] . ' Artículo:' . $datos['Description'] . '  Descripcion adicional: ' . $datos['AdditionalDescription'] . ' Barcode: ' . $datos['BarCode'];
                echo $texto . '<br />';
               /* $data = Array(
                    'id_art'     => null,
                   'SKUCode' => $datos['SKUCode'],
                  'Description'             => $datos['Description'],
                  'AdditionalDescription'  => $datos['AdditionalDescription'],
                  'AlternativeCode'         => $datos['AlternativeCode'],
                  'BarCode'                => $datos['BarCode'],
                  'Commission'             => $datos['Commission'],
                  'Discount'               => $datos['Discount'],
                  'MeasureUnitCode'        => $datos['MeasureUnitCode'],
                  'MaximumStock'           => $datos['MaximumStock'],
                  'MinimumStock'           => $datos['MinimumStock'],
                  'RestockPoint'           => $datos['RestockPoint'],
                  'Observations'           => $datos['Observations'],
                  'Kit'                    => $datos['Kit'],
                  'KitValidityDateSince'    => $datos['KitValidityDateSince'],
                  'KitValidityDateUntil'    => $datos['KitValidityDateUntil'],
                  'UseScale'               => $datos['UseScale'],
                  'Scale1'                 => $datos['Scale1'],
                  'Scale2'                 => $datos['Scale2'],
                  'BaseArticle'             => 0,
                  'ScaleValue1'            => 0,
                  'ScaleValue2'             => 0,
                  'DescriptionScale1'       => '',
                  'DescriptionScale2'       => '',
                  'DescriptionValueScale1'  => '',
                   'DescriptionValueScale2'  => '',
                   'Disabled'  => $datos['Disabled']
                );*/
                $data = Array(
                    'published'  => $datos['Disabled']
                );
                //Se procede a actualizar la tabla de Articulos de la tienda
                $db->where('product_sku', $datos['SKUCode']);
                $id = $db->update ($prefijo.constantes::get('TABLA_STOCK_SHOP'), $data);
                if($id){
                    echo "Producto <b>" . $datos['SKUCode'] . "</b> ha sido actualizado" . "<br /> \n";
                } else {
                    echo 'Error al actualizar la tabla de Artículos de la Tienda Online: ' . $db->getLastError();
                }
            }
            $pageNumber = $pageNumber + 1;
            $response = $api->get('Product', '500', $pageNumber, $filter);
        } while (isset($response['Paging']['MoreData']));
    }
    //Proceso última pagina
    if (isset($response['Data'])) {
        echo '<b>comienza proceso última pagina ' . $pageNumber . '</b><br />';
        foreach ($response['Data'] as $datos) {
            $texto = 'ID:' . $datos['SKUCode'] . ' Artículo:' . $datos['Description'] . '  Descripcion adicional: ' . $datos['AdditionalDescription'] . ' Barcode: ' . $datos['BarCode'];
            echo $texto . '<br />';
            $data = array(
                'Disabled' => $datos['Disabled']
            );
            //Se procede a actualizar la tabla de Articulos de la tienda
            $db->where('product_sku', $datos[0]['SKUCode']);
            $id = $db->update($prefijo . constantes::get('TABLA_STOCK_SHOP'), $data);
            if ($id) {
                echo "Producto <b>" . $datos['SKUCode'] . "</b> ha sido actualizado" . '<br />';
            } else {
                echo 'Error al actualizar la tabla de Artículos de la Tienda Online: ' . $db->getLastError();
            }
        }
    }
    echo 'Finalización del proceso: '.date("d-m-Y (H:i:s)");
    $db->disconnect();
} catch (Exception $e) {
    echo 'Hubo un error: ', $e->getMessage(), "\n";
}