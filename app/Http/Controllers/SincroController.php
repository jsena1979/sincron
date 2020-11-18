<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositorios\TangoApi;
class SincroController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TangoApi $api)
    {
        $this->tangoApi = $api; 
        dd($api);
        $this->middleware('auth');
         
        
       
    }
    public function panel()
    {
        return dd($this->tangoApi());
        //return view('panel');
    }
    public function procesos($nombre = null)
    {
        $proceso = ['Sincronizar Stock', 'Sincronizar Precios', 'Sincronizar Articulos'];
        //return view('procesos',['proceso'=>$procesos,'nombre'=>$nombre]);
        return view('procesos', compact('proceso', 'nombre'));
    }

    public function sincronizarStock()
    {
        $this->tangoApi->getArticulos();
    }
}