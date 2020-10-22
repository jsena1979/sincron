<?php


namespace App\Repositorios;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
class GuzzleHttpRequest
{
    protected $client;
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    public function get($url,$auth)
    {

        $response = $this->client->request('GET', $url,$auth);
        return json_decode($response->getBody(),true);
    }

    public function post($url,$datos)
    {
        $response = $this->client->request('POST', $url,$datos);
        return json_decode($response->getBody(),true);
    }

     public function put($url,$datos)
    {
        $response = $this->client->request('PUT', $url,$datos);
        return json_decode($response->getBody(),true);

    }

    public function  getFiles($url,$auth)    {
        $response =  $this->client->request('GET', $url, [
            'auth' => [$auth['auth'][0], $auth['auth'][1], 'basic'],
            'headers' => [                
                'Content-Type' => 'application/json', 
                'Accept' => 'application/pdf'
            ]
        ]);
      
        return $response->getBody()->getContents();
    }

}
