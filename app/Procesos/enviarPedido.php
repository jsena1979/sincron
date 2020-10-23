<?php
include_once "include/db/MysqliDb.php";
include_once "include/log.php";
include_once "include/config.php";
include_once "include/tangoTiendas_rest.php";

class enviarPedido
{
    public $respuesta;

    /*
     * Formato del JSON para enviar pedidos: https://github.com/TangoSoftware/ApiTiendas#ejemplojson
     *
     *
     *
             $orden = array(
               'Date' => '0001-01-01T00:00:00',
               'Total' => 0.0,
               'TotalDiscount' => 0.0,
               'PaidTotal' => 0.0,
               'FinancialSurcharge' => 0.0,
               'OrderID' => '',
               'OrderNumber' => '',
               'Customer' =>
                   array(
                       'CustomerID' => 0,
                       'Code' => NULL,
                       'DocumentType' => '',
                       'DocumentNumber' => '',
                       'IVACategoryCode' => '',
                       'User' => '',
                       'Email' => '',
                       'FirstName' => '',
                       'LastName' => '',
                       'BusinessName' => '',
                       'Street' => '',
                       'HouseNumber' => '',
                       'Floor' => '',
                       'Apartment' => '',
                       'City' => '',
                       'ProvinceCode' => '',
                       'PostalCode' => '',
                       'PhoneNumber1' => '',
                       'PhoneNumber2' => '',
                       'Bonus' => 0.0,
                       'MobilePhoneNumber' => '',
                       'WebPage' => NULL,
                       'BusinessAddress' => '',
                       'Comments' => '',
                       'NumberListPrice' => 0,
                       'Removed' => false,
                       'DateUpdate' => '0001-01-01T00:00:00',
                       'Disable' => '0001-01-01T00:00:00',
                   ),
               'CancelOrder' => false,
               'OrderItems' =>
                   array(
                       0 =>
                           array(
                               'ProductCode' => NULL,
                               'SKUCode' => NULL,
                               'VariantCode' => NULL,
                               'Description' => NULL,
                               'VariantDescription' => NULL,
                               'Quantity' => 0.0,
                               'UnitPrice' => 0.0,
                               'DiscountPercentage' => 0.0,
                           ),
                   ),
               'Shipping' =>
                   array(
                       'ShippingID' => 0,
                       'Street' => '',
                       'HouseNumber' => '',
                       'Floor' => '',
                       'Apartment' => '',
                       'City' => '',
                       'ProvinceCode' => '',
                       'PostalCode' => '',
                       'PhoneNumber1' => '',
                       'PhoneNumber2' => '',
                       'ShippingCost' => 0.0,
                       'DeliversMonday' => '',
                       'DeliversTuesday' => '',
                       'DeliversWednesday' => '',
                       'DeliversThursday' => '',
                       'DeliversFriday' => '',
                       'DeliversSaturday' => '',
                       'DeliversSunday' => '',
                       'DeliveryHours' => '',
                   ),
               'CashPayment' => NULL,
               'Payments' =>
                   array(
                       0 =>
                           array(
                               'PaymentId' => 0,
                               'TransactionDate' => NULL,
                               'AuthorizationCode' => '',
                               'TransactionNumber' => '',
                               'Installments' => 0,
                               'InstallmentAmount' => 0.0,
                               'Total' => 0.0,
                               'CardCode' => NULL,
                               'CardPlanCode' => NULL,
                               'VoucherNo' => 0,
                               'CardPromotionCode' => NULL,
                           ),
                   ),
           );
     *
     *
     * */
    function __construct($id_orden = null)
    {
        try {
            $conf = new Config();
            $log = new log("log", "../logs/");
            $parametros = $conf->get_parametros();
            //Me conecto a la Base de Datos de la Tienda
            $db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
            $prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
            /**
             *
             * AQUI COMIENZA LA LECTURA DE LOS DATOS DE LA ORDEN DIRECTAMENTE DE LA BASE DE DATOS DE LA TIENDA
             *
             **/

            /*********************************************************
             *
             * Obtengo los datos de la orden del pedido WEB
             *
             ********************************************************/

            $db->where('virtuemart_order_id', $id_orden);
            $datos = $db->get($prefijo . '_virtuemart_orders');
            //var_dump($datos);
            $orden['Date'] = isset($datos[0]['created_on']) ? $datos[0]['created_on'] : null;
            $orden['Total'] = isset($datos[0]['order_total']) ? $datos[0]['order_total'] : 0.0;
            $orden['TotalDiscount'] = $datos[0]['order_discount'];
            $orden['PaidTotal'] = isset($datos[0]['order_total']) ? $datos[0]['order_total'] : 0.0;
            $orden['FinancialSurcharge'] = isset($orden['FinancialSurcharge']) ? $orden['FinancialSurcharge'] : 0.0;
            $orden['OrderID'] = $datos[0]['virtuemart_order_id'];
            $orden['OrderNumber'] = $datos[0]['order_number'];

            /*******************************************
             *
             * Obtengo los datos del comprador
             *
             ******************************************/
            $db->where('virtuemart_user_id', $datos[0]['virtuemart_user_id']);
            $cliente = $db->get($prefijo . '_virtuemart_userinfos');
            //var_dump($cliente);
            $db->where('virtuemart_order_id', $datos[0]['virtuemart_order_id']);
            $datosAdic = $db->get($prefijo . '_virtuemart_order_userinfos');
            // var_dump($datosAdic);
            /***
             *
             * Datos de usuario
             *
             */
            $db->where('id', $datos[0]['virtuemart_user_id']);
            $user = $db->get($prefijo . '_users');
            //var_dump($user);
            //Obtengo datos del cliente
            $orden['Customer']['CustomerID'] = $cliente[0]['virtuemart_user_id'];
            $orden['Customer']['DocumentType'] = $cliente[0]['DocumentType'];
            $orden['Customer']['DocumentNumber'] = isset($cliente[0]['DocumentNumber']) ? $cliente[0]['DocumentNumber'] : 0;
            $orden['Customer']['IVACategoryCode'] = isset($cliente[0]['IvaCategoryCode']) ? $cliente[0]['IvaCategoryCode'] : 'CF';
            $orden['Customer']['User'] = isset($user[0]['username']) ? $user[0]['username'] : "";
            $orden['Customer']['Email'] = isset($user[0]['email']) ? $user[0]['email'] : "";
            $orden['Customer']['FirstName'] = $cliente[0]['first_name'];
            $orden['Customer']['LastName'] = $cliente[0]['last_name'];
            $orden['Customer']['BusinessName'] = $cliente[0]['company'];
            $orden['Customer']['Street'] = $cliente[0]['address_1'] . " " . $cliente[0]['address_2'];
            $orden['Customer']['HouseNumber'] = isset($cliente[0]['HouseNumber']) ? $cliente[0]['HouseNumber'] : "";
            $orden['Customer']['Floor'] = isset($cliente[0]['floor']) ? $cliente[0]['floor'] : "";
            $orden['Customer']['Apartment'] = isset($cliente[0]['Apartment']) ? $cliente[0]['Apartment'] : null;
            $orden['Customer']['City'] = $cliente[0]['city'];
            $orden['Customer']['ProvinceCode'] = isset($cliente[0]['ProvinceCode']) ? $cliente[0]['ProvinceCode'] : null;
            $orden['Customer']['PostalCode'] = $cliente[0]['zip'];
            $orden['Customer']['PhoneNumber1'] = $cliente[0]['phone_1'];
            $orden['Customer']['PhoneNumber2'] = $cliente[0]['phone_2'];
            $orden['Customer']['Bonus'] = isset($orden['Customer']['Bonus']) ? $orden['Customer']['Bonus'] : 0.0;//$cliente[0]['Bonus'];
            $orden['Customer']['MobilePhoneNumber'] = isset($cliente[0]['mobile_phone_number']) ? $cliente[0]['mobile_phone_number'] : null;
            $orden['Customer']['WebPage'] = isset($cliente[0]['web_page']) ? $cliente[0]['web_page'] : null;
            $orden['Customer']['BusinessAddress'] = isset($cliente[0]['business_address']) ? $cliente[0]['business_address'] : null;
            $orden['Customer']['Comments'] = $cliente[0]['customer_note'];
            $orden['Customer']['NumberListPrice'] = isset($cliente[0]['NumberListPrice']) ? $cliente[0]['NumberListPrice'] : 0.0;
            $orden['Customer']['Removed'] = isset($cliente[0]['removed']) ? $cliente[0]['removed'] : false;
            $orden['Customer']['DateUpdate'] = isset($cliente[0]['modified_on']) ? $cliente[0]['modified_on'] : '0001-01-01T00:00:00';
            $orden['Customer']['Disable'] = isset($cliente[0]['Disable']) ? $cliente[0]['Disable'] : '0001-01-01T00:00:00';
            $orden['CancelOrder'] = 'false';

            //Obtengo articulos (items) ordenados
            $db->where('virtuemart_order_id', $datos[0]['virtuemart_order_id']);
            $items = $db->get($prefijo . '_virtuemart_order_items');
            //var_dump($items);
            for ($i = 0; $i < count($items); $i++) {
                $orden['OrderItems'][$i]['ProductCode'] = isset($items[$i]['virtuemart_product_id']) ? $items[$i]['virtuemart_product_id'] : null;
                $orden['OrderItems'][$i]['SKUCode'] = isset($items[$i]['order_item_sku']) ? $items[$i]['order_item_sku'] : 0;
                $orden['OrderItems'][$i]['VariantCode'] = isset($items[$i]['VariantCode']) ? $items[$i]['VariantCode'] : null;
                $orden['OrderItems'][$i]['Description'] = isset($items[$i]['order_item_name']) ? $items[$i]['order_item_name'] : null;
                $orden['OrderItems'][$i]['VariantDescription'] = isset($items[$i]['VariantDescription']) ? $items[$i]['VariantDescription'] : null;
                $orden['OrderItems'][$i]['Quantity'] = isset($items[$i]['product_quantity']) ? $items[$i]['product_quantity'] : null;
                $orden['OrderItems'][$i]['UnitPrice'] = isset($items[$i]['product_item_price']) ? $items[$i]['product_item_price'] : null;
                $orden['OrderItems'][$i]['DiscountPercentage'] = isset($items[$i]['product_subtotal_discount']) ? $items[$i]['product_subtotal_discount'] : 0.0;
            }

            // Obtengo los Datos de Envio
            //Obtengo articulos (items) ordenados
            $db->where('virtuemart_order_id', $datos[0]['virtuemart_order_id']);
            $envio = $db->get($prefijo . '_virtuemart_order_userinfos');
            //var_dump($envio);
            $orden['Shipping']['ShippingID'] = $envio[0]['virtuemart_order_userinfo_id'];
            $orden['Shipping']['Street'] = $envio[0]['address_1'];
            $orden['Shipping']['HouseNumber'] = isset($envio[0]['HouseNumber']) ? $envio[0]['HouseNumber'] : null;
            $orden['Shipping']['Floor'] = isset($envio[0]['Floor']) ? $envio[0]['Floor'] : null;
            $orden['Shipping']['Apartment'] = isset($envio[0]['Apartment']) ? $envio[0]['Apartment'] : null;
            $orden['Shipping']['City'] = isset($envio[0]['city']) ? $envio[0]['city'] : null;
            $orden['Shipping']['ProvinceCode'] = $envio[0]['ProvinceCode'];
            $orden['Shipping']['PostalCode'] = isset($envio[0]['zip']) ? $envio[0]['zip'] : null;
            $orden['Shipping']['PhoneNumber1'] = isset($envio[0]['phone_1']) ? $envio[0]['phone_1'] : null;
            $orden['Shipping']['PhoneNumber2'] = isset($envio[0]['Phone_2']) ? $envio[0]['Phone_2'] : null;
            $orden['Shipping']['ShippingCost'] = isset($envio[0]['ShippingCost']) ? $envio[0]['ShippingCost'] : 0.0;
            $orden['Shipping']['DeliversMonday'] = isset($envio[0]['DeliversMonday']) ? $envio[0]['DeliversMonday'] : 'N';
            $orden['Shipping']['DeliversTuesday'] = isset($envio[0]['DeliversTuesday']) ? $envio[0]['DeliversTuesday'] : 'N';
            $orden['Shipping']['DeliversWednesday'] = isset($envio[0]['DeliversWednesday']) ? $envio[0]['DeliversWednesday'] : 'N';
            $orden['Shipping']['DeliversThursday'] = isset($envio[0]['DeliversThursday']) ? $envio[0]['DeliversThursday'] : 'N';
            $orden['Shipping']['DeliversFriday'] = isset($envio[0]['DeliversFriday']) ? $envio[0]['DeliversFriday'] : 'N';
            $orden['Shipping']['DeliversSaturday'] = isset($envio[0]['DeliversSaturday']) ? $envio[0]['DeliversSaturday'] : 'N';
            $orden['Shipping']['DeliversSunday'] = isset($envio[0]['DeliversSunday']) ? $envio[0]['DeliversSunday'] : 'N';
            $orden['Shipping']['DeliveryHours'] = isset($envio[0]['DeliveryHours']) ? $envio[0]['DeliveryHours'] : 'N';

            // Obtengo los datos de los metodos de Pagos
            $db->where('virtuemart_paymentmethod_id', $datos[0]['virtuemart_paymentmethod_id']);
            $pagos = $db->get($prefijo . '_virtuemart_paymentmethods_es_es');
            //var_dump($pagos);
            //Pagos en  efectivo.
            $orden['CashPayment']['PaymentID'] = isset($pagos[0]['virtuemart_paymentmethod_id']) ? $pagos[0]['virtuemart_paymentmethod_id'] : null;
            $orden['CashPayment']['PaymentMethod'] = isset($pagos[0]['slug']) ? $pagos[0]['slug'] : null;
            $orden['CashPayment']['PaymentTotal'] = isset($orden['Total']) ? $orden['Total'] : 0.0;
            //var_dump($pagos);
            //Pagos con tarjeta
           /*
            if(count($pagos)>0) {
                for ($j = 0; $j < count($pagos); $j++) {
                    $orden['Payments'][$j]['PaymentId'] = $pagos[$j]['virtuemart_paymentmethod_id'];
                    $orden['Payments'][$j]['TransactionDate'] = isset($datos[0]['created_on']) ? $datos[0]['created_on'] : null;
                    $orden['Payments'][$j]['AuthorizationCode'] = isset($pagos[$j]['AuthorizationCode']) ? $pagos[$j]['AuthorizationCode'] : 0;
                    $orden['Payments'][$j]['TransactionNumber'] = isset($pagos[$j]['TransactionNumber']) ? $pagos[$j]['TransactionNumber'] : 0;
                    $orden['Payments'][$j]['Installments'] = isset($pagos[$j]['Installments']) ? $pagos[$j]['Installments'] : 1;
                    $orden['Payments'][$j]['InstallmentAmount'] = isset($pagos[$j]['InstallmentAmount']) ? $pagos[$j]['InstallmentAmount'] : $orden['Total'];
                    $orden['Payments'][$j]['Total'] = isset($orden['Total']) ? $orden['Total'] : 0.0;
                    $orden['Payments'][$j]['CardCode'] = isset($pagos[$j]['CardCode']) ? $pagos[$j]['CardCode'] : 000;
                    $orden['Payments'][$j]['CardPlanCode'] = '1';
                    $orden['Payments'][$j]['VoucherNo'] = isset($pagos[$j]['VoucherNo']) ? $pagos[$j]['VoucherNo'] : 0;
                    $orden['Payments'][$j]['CardPromotionCode'] = isset($pagos[$j]['CardPromotionCode']) ? $pagos[$j]['CardPromotionCode'] : null;
                }
            }*/
            //print_r(json_encode($orden));
            $api = new tango_tiendas_rest();
            $datosPedido = json_encode($orden);


            $response = $api->post($datosPedido);

            $this->setRespuesta($response);
        } catch (Exception $e) {
            $this->setRespuesta('Hubo un error: ', $e->getMessage(), '\n');
        }
        return $this->getRespuesta();
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


}

/*Obtengo las nuevas ordenes a procesar*/
//Me conecto a la Base de Datos de la Tienda
$conf = new Config();
$log = new log("log", "../logs/");
$parametros = $conf->get_parametros();
$prefijo = $parametros['DB_SHOP']['DB_PREFIX_SHOP'];
$db = new MysqliDb($parametros['DB_SHOP']['DB_HOST_SHOP'], $parametros['DB_SHOP']['DB_USER_SHOP'], $parametros['DB_SHOP']['DB_PASSWORD_SHOP'], $parametros['DB_SHOP']['DB_NAME_SHOP']);
$db->where('cast(created_on as date)', date("Y-m-d"), ">=");
$db->where('order_status', 'U');
$estadoOrden = $db->get($prefijo . '_virtuemart_orders');
//Proceso cada orden obtenida
$pedido = new enviarPedido('10827');
$ok = json_decode($pedido->getRespuesta());
var_dump($ok);

if (count($estadoOrden) > 0) {

    for ($i = 0; $i < count($estadoOrden); $i++) {

        $db->where('virtuemart_order_id', $estadoOrden[$i]['virtuemart_order_id']);
        $datos = $db->get($prefijo . '_estado_envio_pedidos_tienda');
        if (count($datos) == 0) {//Se consulta si NO se ha enviado todavía
            $pedido = new enviarPedido($estadoOrden[$i]['virtuemart_order_id']);
            $data["virtuemart_order_id"] = $estadoOrden[$i]['virtuemart_order_id'];
            $time = time();
            $data["fecha_hora_envio"] = date("Y-m-d H:i:s", $time);
            $ok = json_decode($pedido->getRespuesta());
            if ($ok->{'Status'} == 0) {//Si se envio la orden con exito grabo en tanla de envio de pedidos
                $data["estado_envio"] = 'S';
                $id = $db->insert($prefijo . '_estado_envio_pedidos_tienda', $data);
                print 'Orden Nº' . $estadoOrden[$i]['virtuemart_order_id'] . ' enviada con exito';
            } else {
                $data["estado_envio"] = 'N';
                $id = $db->insert($prefijo . '_estado_envio_pedidos_tienda', $data);
                print 'Orden Nº' . $estadoOrden[$i]['virtuemart_order_id'] . ' No pudo ser enviada. Error: ' . $ok->{'Message'};
            }

        } else {
            print "Orden Nº" . $estadoOrden[$i]['virtuemart_order_id'] . " ya fue enviada con exito</br> \n";
        }
    }
} else {
    print 'No se encontraron ordenes para procesar';
}
//Actualizo el estado del tabla de envios