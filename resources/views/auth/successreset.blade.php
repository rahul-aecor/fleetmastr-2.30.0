<!DOCTYPE html>
<html>
<head>
    <title>{{ get_brand_setting('title') }}</title>
    <!-- common css starts -->
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 
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
</head>

<body class="page-md login">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                <div class="center">
                    <img src="{{ setting('logo') }}" class="logo" style="padding-top:20px;"/>
                </div>

                <div class="content">
                    @if (session('message'))
                        <div class="alert alert-success js-password-success">
                            <button class="close" data-close="alert"></button>
                            {{ session('message') }}
                        </div>
                    @endif
                    <div class="app-alert-wrapper">
                        <div class="app-alert-infoimg">
                            <img src="/img/padlock-tick.png" />
                        </div>
                        <div class="app-alert-infotext">Your password has been updated.</div>
                    </div>
                </div>

                <div class="copyright">
                  © <?php echo date("Y"); ?> developed by <a href="https://www.imastr.com" target="_blank" rel="noopener"><font color="#fff"><font color="#2196f3"><u><b>i</b>mastr</u></font></a>
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