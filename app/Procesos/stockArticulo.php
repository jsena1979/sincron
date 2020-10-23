<?php
include_once "include/db/MysqliDb.php";
include_once "include/db/ADOdb/adodb.inc.php";
include_once "include/log.php";
include_once "include/config.php";
include_once "include/tangoTiendas_rest.php";

class stockArticulo
{
    public $respuesta;
    public $articulos;

    /*******************************************************************************************
     * Este móddulo consulta a la API de Tango cual es el saldo de Stock actual de un
     * Artículo en particular y luego actualiza dicho saldos en la base de datos de virtuemart
     *
     *******************************************************************************************/
    function getStockArticulo($skuCode = null)
    {
        $conf = new Config();
        $log = new log("log", "../logs/");
        $parametros = $conf->get_parametros();
        //Me conecto a la Base de Datos de la Tienda
        $db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
        $api = new tango_tiendas_rest();
        $prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
        $pageNumber = "1";
        try {
            echo 'Comienza proceso: ' . date("d-m-Y (H:i:s)") . '<br />';
            $articulo = $skuCode;
            $response = $api->get('Stock', '500', $pageNumber, $articulo);
            //print_r($response);
            //exit;
            if (isset($response['Data'])) {
                //$msgLog =  'Comienza proceso pagina ' . $pageNumber ;
                //$log->insert($msgLog, false, true, true);
                /*Se actualiza la Tabla de Stock de Virtuemart*/
                $datos = $response['Data'];
                //$texto = 'ID:' . $datos['StoreNumber'] . ' Almacen:' . $datos['WarehouseCode'] . ' Codigo: ' . $datos['SKUCode'] . ' Cantidad: ' . $datos['Quantity'];
                //echo $texto . '<br />';
                $datos['EngagedQuantity'] = isset($datos[0]['EngagedQuantity']) ? $datos[0]['EngagedQuantity'] : 0;
                $datos['EngagedQuantity'] = isset($datos[0]['EngagedQuantity']) ? $datos[0]['EngagedQuantity'] : 0;
                $datos['PendingQuantity'] = isset($datos[0]['PendingQuantity']) ? $datos[0]['PendingQuantity'] : 0;
                $stock['product_in_stock'] = $datos[0]['Quantity'] - $datos[0]['EngagedQuantity'];//Stock actual-Cantidad Comprometida
                //$stock['product_in_stock'] = ($datos['Quantity'] + $datos['PendingQuantity']) - $datos['EngagedQuantity'] ;//(Stock actual+ Cantidad pendiente  de Recepcion)- Cantidad Comprometida

                //Se procede a actualizar la tabla de STOCK
                //$datos['SKUCode'] = "'".intval(preg_replace('/[^0-9]+/', '', $datos['SKUCode']), 10)."'";
                $datos['SKUCode'] = (string)$datos[0]['SKUCode'];
                $db->Where('product_sku', $datos[0]['SKUCode']);
                if ($db->update($prefijo . constantes::get('TABLA_STOCK_SHOP'), $stock)) {
                    echo "Producto <b>" . $datos[0]['SKUCode'] . "</b> stock Articulo: " . $articulo . " actualizado" . '<br />';
                } else {
                    echo 'Error al actualizar el producto: ' . $datos[0]['SKUCode'] . ' ' . $db->getLastError();
                }
            }
            $db->disconnect();
            //echo '<br />Stock actualizado';
            //echo '<br />Fin de procesamiento: ' . date("d-m-Y (H:i:s)") . '<br />';
        } catch (Exception $e) {
            echo 'Hubo un error: ', $e->getMessage(), "\n";
        }
        return $this->respuesta;
    }

    /*Esta funcion permite obtener todos los articulos de l web de una determinada categoría o Marca*/
    function get_articulosCategoriaMarca($datos = array())
    {
        $conf = new Config();
        $where = "";
        $log = new log("log", "../logs/");
        $parametros = $conf->get_parametros();
        //Me conecto a la Base de Datos de la Tienda
        $prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
        $db = newADOConnection('mysqli');
        $db->connect($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);

        if (isset($datos['virtuemart_category_id'])) {
            $where .= 'and cp.virtuemart_category_id=' . $db->qStr($datos['virtuemart_category_id']);
        }
        if (isset($datos['category_parent_id'])) {
            $where .= 'and cc.category_parent_id=' .  $db->qStr($datos['category_parent_id']);
        }
        if (isset($datos['category_name'])) {
            $where .= 'and cn.category_name=' . $db->qStr($datos['category_name']);
        }
        $sql = " select 
					p.virtuemart_product_id,
                    cc.category_parent_id,
					p.product_parent_id
					,cn.category_name,
					p.product_sku,
                    cn.virtuemart_category_id
				from 
					{$prefijo}_virtuemart_categories_es_es cn,
					{$prefijo}_virtuemart_category_categories cc,
					{$prefijo}_virtuemart_product_categories cp,
                    {$prefijo}_virtuemart_products p

				where
				cp.virtuemart_category_id= cn.virtuemart_category_id
                and cc.id=cn.virtuemart_category_id    
				and cp.virtuemart_product_id=p.virtuemart_product_id
				{$where}
				";

        $results = $db->execute($sql);

        if ($results === false) die("Hubo un error al intentar obtener Articulo-categoria-marca");
        $this->setArticulos( $results->getRows());

    }

    /**
     * @return mixed
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * @param mixed $respuesta
     */
    public function setRespuesta($respuesta)
    {
        $this->respuesta = $respuesta;
    }

    /**
     * @return mixed
     */
    public function getArticulos()
    {
        return $this->articulos;
    }

    /**
     * @param mixed $articulos
     */
    public function setArticulos($articulos)
    {
        $this->articulos = $articulos;
    }
}
