<?php


namespace App\Repositorios;

class TangoApi extends GuzzleHttpRequest
{
    protected $credenciales;
    protected $user;
    protected $pass;
    protected $urlApi;

    const API_VERSION = 'v1';
    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param mixed $pass
     */
    public function setPass($pass): void
    {
        $this->pass = $pass;
    }

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

    public function getCredAPI()
    {
        $this->credenciales = json_decode(env('CREDENCIALES_API_BASIC_TUPA'));
        $this->setUser($this->credenciales[0][0]);
        $this->setPass($this->credenciales[0][1]);
        $this->setUrlApi($this->credenciales[0][2]);
    }

    public function getStock($id)
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . '/visitantes/'.$id;
        $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->get($url, $auth);
    }

    public function getPrecios($id_visitante){
       $this->getCredApi();
       $url = $this->getUrlApi() . '/pases/'.$id_visitante;
       $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->get($url, $auth);
    }
    /*
     *
     * */
    public function getArticulos()
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . '/terminos-condiciones?estado=es_igual_a%3BAC';
        $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->get($url, $auth);
    }

    public function getPaseQr($id_visitante)
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . "/pases/$id_visitante/qr";
        $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->get($url, $auth);
    }

    public function getTerminosVigentesPdf($id_terminos)
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . "/terminos-condiciones/{$id_terminos}/archivo";
        $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->getFiles($url, $auth);
    }

    public function getQrPdf($id_visitante)
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . "/pases/{$id_visitante}/pdf";
        $auth = ['auth' => [$this->getUser(), $this->getPass()]];
        return $this->getFiles($url, $auth);
    }

    public function putAceptarTerminos($id_visitante, $idTyCVigente)
    {
        $this->getCredApi();
        $url = $this->getUrlApi() . "/terminos-condiciones/$idTyCVigente/aceptar";

        $datos = array(
            'tipo_visitante' => 'EXTERNO',
            'visitante' => $id_visitante
         );

        $datos = ['body' => json_encode($datos), 'auth' => [$this->getUser(), $this->getPass()]];

        $visita = $this->put($url, $datos);
        return $visita;
    }


    public function postVisitante($visita=array())
    {
        $this->getCredApi();
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
        $datos = ['body' => json_encode($datos), 'auth' => [$this->getUser(), $this->getPass()]];

        $visita = $this->post($url, $datos);
        return $visita;
    }
}
