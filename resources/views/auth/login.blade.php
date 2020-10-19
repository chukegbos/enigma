<!DOCTYPE html>
<html>
    <head>
        <title>Login - {{ $setting->sitename }}</title>
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">    
    </head>

    <body class="login-page login-1">
        <div id="app" class="login-wrapper">
            <div class="login-box">
                <div class="logo-main">
                    <h4 style="color: #fff">{{ $setting->sitename }}</h1>
                </div>
                
                <form  id="loginForm" method="POST" action="{{ route('login') }}" class="mb-3">
                    @csrf
                    <div class="form-group">
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter email" required="">

                        @if ($errors->has('email'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="Enter Password" required="">

                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                    <button class="btn btn-info btn-full">Login</button>           
                </form>

                <div class="page-copyright">
                    <!--<div class="pull-left">
                        <p>Powered by <a href="#" target="_blank">Zallasoft Solutions</a></p>
                    </div>-->
                   
                        <p>{{ $setting->sitename }} © {{ date('Y') }}</p>
                   
                </div>
            </div>
        </div>
    </body>
</html>
