<!DOCTYPE html>
<html>
<head>
    <title>{{ get_brand_setting('title') }}</title>
    <!-- common css starts -->
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 
        {{-- <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/> --}}
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
        <link href="{{ elixir('css/simple-line-icons.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ elixir('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ elixir('css/uniform.default.css') }}" rel="stylesheet" type="text/css"/>

        <link href="{{ elixir('css/login.css') }}" rel="stylesheet" type="text/css"/>

        <link href="{{ elixir('css/components-md.css') }}" id="style_components" rel="stylesheet" type="text/css"/>
        <link href="{{ elixir('css/plugins-md.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ elixir('css/layout.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ elixir('css/grey.css') }}" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="{{ elixir('css/theme-overrides.css') }}" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="{{ elixir('css/custom.css') }}" rel="stylesheet" type="text/css"/>

        {{-- <link href="{{ asset('css/branding.css') }}" rel="stylesheet" type="text/css"/>  --}}
        {{-- <link href="{{ asset(get_brand_setting('stylesheet')) }}" rel="stylesheet" type="text/css"/> --}}
        <link href="{{ elixir('css/brand/main.css') . '?' . http_build_query(['v' => setting('primary_colour')]) }}" rel="stylesheet" type="text/css"/>
        <style>
            .form-group.form-md-line-input {
                padding-top: 0;
            }
        </style>
</head>

<body class="page-md login">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                <div class="center">
                    <img src="{{ setting('logo') }}" class="logo" style="padding-top:20px;"/>
                    {{-- @if (get_brand_setting('brand_product_name', false))
                        <h2 class="brand-product-name"><img src="{{ asset(get_brand_setting('logo.fleet_logo')) }}" class="fleet_logo" /></h2>
                    @endif --}}
                </div>

                <div class="content">

                    @if($message)
                        <p class="login_title text-center">{{ $message }}</p>

                        <div class="form-group form-md-line-input" style="text-align: center; padding-top: 20px;">
                            <a href="/login" id="back-btn" class="forget-password">Go to login page</a>
                        </div>
                    @else
                    {!! Form::open(['route' => ['user.password'], 'id' => 'login-form','class' => 'js-password-activation set-password-frm', 'method' => 'POST']) !!}
                        {{  csrf_field() }}
                        <input type="hidden" name="key" value="{{ $usersPasswords[0]['key'] }}">

                        <div class="">
                            <p class="login_title">Set your password for your {!! get_brand_setting('account_name') !!} account</p>
                        </div>

                        <div class="clearfix"></div>
                        @if($errors->has())
                            <input type="hidden" value="1" name="lanes-error" class="lanes-error">
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-success">
                                    <button class="close" data-close="alert"></button>
                                    {{ $error }}
                                </div>
                            @endforeach
                        @endif

                        <div class="form-group form-md-line-input has-error form-md-floating-label">
                            <div class="input-group right-addon">
                                {!! Form::password('password',['class'=>'form-control', 'placeholder'=>'Password', 'id' => 'password'])!!}
                            </div>
                        </div>

                        <div class="form-group form-md-line-input has-error form-md-floating-label padding-btm">
                            <div class="input-group right-addon">
                                {!! Form::password('password_confirmation',[  'class'=>'form-control','placeholder'=>'Confirm password'])!!}
                            </div>
                        </div>

                        <div class="form-actions center">
                            <button type="submit" id="reset-submit-btn" class="btn red-rubine">Set password</button>
                        </div>
                        <div class="form-group form-md-line-input" style="text-align: center; padding-top: 20px; margin-bottom: 0">
                            <a href="/login" id="back-btn" class="forget-password">Go to login page</a>
                        </div>
                    {!! Form::close() !!}
                    @endif
                </div>

                <div class="copyright">
                    <div class="col-md-12 text-center page-footer">
                        <div class="footer-inner">
                            <span>
                                © <?php echo date("Y"); ?> {{ get_brand_setting('footer_text') }} <a href="http://www.aecordigital.com/" target="_blank" rel="noopener">{!! get_brand_setting('footer_text_part2') !!}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--[if lt IE 9]>
<script src="../../assets/global/plugins/respond.min.js"></script>
<script src="../../assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="{{ elixir('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jquery-migrate.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jquery.blockui.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jquery.uniform.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jquery.cokie.min.js') }}" type="text/javascript"></script>

<script src="{{ elixir('js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script src="{{ elixir('js/metronic.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/layout.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/demo.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/login.js') }}" type="text/javascript"></script>


<script>
jQuery(document).ready(function() {
    Metronic.init(); // init metronic core components
    Layout.init(); // init current layout
    Login.init();
    Demo.init();
    if ($(".lanes-error").length) {
        $('#register-btn').click();
    }
    $("input").each(function(){
        if ($(this).val() != "") {
            $(this).addClass('edited');
        } else {
            $(this).removeClass('edited');
        }
    });
});
</script>
</body>
</html>
