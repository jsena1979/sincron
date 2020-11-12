<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositorios\TupaApi;
use Illuminate\Http\Request;
use App\Repositorios\TupaApi;


class SincroController extends Controller
{
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TangoApi $api)
    {
        $this->middleware('auth');
        $this->tangoApi = $api;
    }
    public function panel(){
        return view('panel');
    }
    public function procesos($nombre = null){
        $proceso = ['Sincronizar Stock', 'Sincronizar Precios', 'Sincronizar Articulos'];
        //return view('procesos',['proceso'=>$procesos,'nombre'=>$nombre]);
        return view('procesos', compact('proceso','nombre'));
    }

    public function sincronizarStock(){


    }
