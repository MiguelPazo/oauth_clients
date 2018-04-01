@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-4 col-sm-offset-4 text-center">
                <h1>OAUTH CLIENT</h1>

                <p>Ingrese con cualquiera de las siguientes opciones:</p>

                <a href="{{ $loginGoogle }}" class="btn btn-primary btn-lg">Ingrese con Google</a>
                <br><br>
                <a href="{{ $loginTwitter }}" class="btn btn-primary btn-lg">Ingrese con Twitter</a>
                <br><br>
                <a href="{{ $loginLinkedin }}" class="btn btn-primary btn-lg">Ingrese con LinkedIn</a>
                <br><br>
                <a href="{{ $loginFacebook }}" class="btn btn-primary btn-lg">Ingrese con Facebook</a>
            </div>
        </div>
    </div>
@endsection