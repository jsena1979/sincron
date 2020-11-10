@extends('plantilla')
@section('seccion')
    <h1>Procesos de Sincronización</h1>
    @foreach($proceso as $item)
        <a href="{{route('proceso',$item)}}" class="h4 text-danger">{{$item}}</a><br>
    @endforeach
    @if(!empty($nombre))
        @switch($nombre)
            @case($nombre=='Sincronizar Stock')
            <h2 class="mt-5">El proceso seleccionado es {{$nombre}}</h2>
            <p>{{$nombre}} Lorem ipsum dddd</p>
            @break
            @case($nombre=='Sincronizar Precios')
            <h2 class="mt-5">El proceso seleccionado es {{$nombre}}</h2>
            <p>{{$nombre}} Lorem ipsum dddd</p>
            @break
        @endswitch
    @endif
@endsection
