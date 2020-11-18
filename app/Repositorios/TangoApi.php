<?php


namespace App\Repositorios;

class TangoApi extends GuzzleHttpRequest
{
    protected $urlApi;
    const API_VERSION = 'v1';
    /**
    * @return mixed
    */
    public function getUrlApi()
    {
        return $this->urlApi;
    }

    /**
     * @param mixed $urlApi
     */
    public function setUrlApi($urlApi): void
    {
        $this->urlApi = $urlApi . self::API_VERSION;
    }


    public function getPrecio($id)
    {
        $url = $this->getUrlApi() . '/Price/'.$id;
        return $this->get($url);
    }

    public function getPrecios()
    {
        $url = $this->getUrlApi() . '/Price';
        return $this->get($url);
    }
    /*
     *
     * */
    public function getArticulos()
    {
        $url = $this->getUrlApi() . '/Products';
        return $this->get($url);
    }

 

    /*public function postOrden($orden=array())
    {
        $url = $this->getUrlApi() . '/visitantes';

        $datos = array(
            'contacto' =>
                array(
                    'tipo' => 'email',
                    'valor' => $visita['email'],
                ),
            'datos_personales' =>
                array(
                    'nombre' => $visita['nombre'],
                    'apellido' => $visita['apellido'],
                    'sexo' => 'M',
                    'legajo' => 0000,
                    'documentos' =>
                        array(
                            0 =>
                                array(
                                    'tipo' => 'DNI',
                                    'numero' => $visita['dni'],
                                ),
                        ),
                ),
            'emails' =>
                array(
                    0 =>
                        array(
                            'tipo' => 'PERSONAL',
                            'valor' => $visita['email'],
                        ),
                ),
            'domicilios' =>
                array(
                    0 =>
                        array(
                            'tipo' => 'PERSONAL',
                            'valor' => $visita['direccion'],
                        ),
                ),
            'telefonos' =>
                array(
                    0 =>
                        array(
                            'tipo' => 'CELULAR',
                            'uso' => 'PERSONAL',
                            'numero' => $visita['telefono'],
                        ),
                ),
            'organizacion' => $visita['organizacion'],
        );
        $datos = ['body' => json_encode($datos)];

        $visita = $this->post($url, $datos);
        return $visita;
    }*/
}
