@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="@guest display: block;text-align: center;padding: 10px; @endif">
                    <a class="navbar-brand" href="https://www.allstars-web.com/" target="_blank">
                        <img src="/admin/images/allstarsweb.gif" style="width: 250px">
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" id="login_form" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus  onchange="loginWithQRCode()">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="button" class="btn btn-primary" onclick="submitForm()">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function submitForm(){
        
        let qrCodeString = $('#email').val();
        
        if ( ( qrCodeString.indexOf('|') > -1 ) || ( qrCodeString.indexOf('^') > -1 ) || ( qrCodeString.indexOf('$') > -1 ) ){
            loginWithQRCode();
        }else{
           $('#login_form').submit(); 
        }

    }
    
    function loginWithQRCode(){
        
        let qrCodeString = $('#email').val();
        
        if ( qrCodeString.indexOf('@') > -1 ){
            $('#login_form').submit();
        }else{
            
            let qrCodeArray = splitMulti(qrCodeString, ['||', '^^', '$$'])
            
            if(qrCodeArray.length < 2) qrCodeArray = splitMulti(qrCodeString, ['||', '^^', '$$'])

            $('#email').val(qrCodeArray[0] + '@all-stars-motorsport.com');
            $('#password').val(qrCodeArray[1]);
            
            $('#login_form').submit();
            
        }
        
    }

    function splitMulti(str, tokens){
        var tempChar = tokens[0]; 
        for(var i = 1; i < tokens.length; i++){
            str = str.split(tokens[i]).join(tempChar);
        }
        str = str.split(tempChar);
        return str;
    }
</script>

@endsection
