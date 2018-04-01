@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-right">
                <a href="{{ url('logout') }}" class="btn btn-lg btn-danger">SALIR</a>
            </div>
            <div class="col-sm-12">
                <pre>
                    {{ json_encode($data, JSON_PRETTY_PRINT) }}
                </pre>
            </div>
        </div>
    </div>
@endsection