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

        {{-- <link href="{{ asset(get_brand_setting('stylesheet')) }}" rel="stylesheet" type="text/css"/> --}} 
        <link href="{{ elixir('css/brand/main.css') . '?' . http_build_query(['v' => setting('primary_colour')]) }}" rel="stylesheet" type="text/css"/>
</head>

<body class="page-md login">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                <div class="center">
                    <img src="{{ setting('logo') }}" class="logo" style="padding-top:20px;"/>
                    <h2 class="brand-product-name"><img src="{{ asset(get_brand_setting('logo.fleet_logo')) }}" class="fleet_logo" /></h2>
                </div>
                <div class="content">
                    @if($agent->isMobile() || $agent->isTablet())
                        <p class="login_title">Click on the icon to download the <b>fleet</b>mastr app:</p>
                    @else
                        <p class="login_title">To download the <b>fleet</b>mastr app please visit this page on your device.</p>
                    @endif
                    <div class="row text-center d-flex justify-content-center" style="margin: 60px 0px;">
                        {{-- <div class="col-md-2 col-xs-2"></div> --}}
                        <div class="col-md-4 col-xs-6">
                            @if($agent->isAndroidOS() && $apk_version_url!="")
                                <a href="{{ $apk_version_url }}"><i class="fa fa-android" style="font-size: 60px"></i></a>
                            @else
                                <i class="fa fa-android" style="font-size: 60px"></i>
                            @endif
                            <br><br>
                            <p class="login_title">Android <span class="small">(v{{ setting('android_version') }})</span></p>
                            <p class="login_title"><a href="/apps/android" style="text-decoration: underline;">Install instructions</a></p>
                        </div>
                        <div class="col-md-4 col-xs-6">
                            @if( ($agent->device() == 'iPhone' || $agent->device() == 'Macintosh' || $agent->device() == 'iPad') && $ios_version_url!="")
                                <a href="itms-services://?action=download-manifest&url={{ $ios_version_url }}" id="text"><i class="fa fa-apple" style="font-size: 60px"></i></a>
                            @else
                                <i class="fa fa-apple" style="font-size: 60px"></i>
                            @endif
                            <br><br>            
                            <p class="login_title">iOS <span class="small">(v{{ setting('ios_version') }})</span></p>
                            <p class="login_title"><a href="/apps/ios" style="text-decoration: underline;">Install instructions</a></p>
                        </div>
                        {{-- <div class="col-md-2 col-xs-2"></div> --}}
                    </div>
                    <div class="form-actions center">
                        <a href="/login" class="btn red-rubine">Go to login page</a>
                    </div>
                </div>
                <div class="footer-inner login-page-footer">
                    <div class="copyright">
                    <!--   Copyright {{ date('Y') }}@if(config('branding.name')=="lanes") {{ get_brand_setting('brand_name') }} @endif.&nbsp;All rights reserved. {{ get_brand_setting('footer_text') }} -->
                        <div class="col-md-12 text-center page-footer">
                            <div class="footer-inner">
                                @if(config('branding.name')=="lanes")
                                    <span class="with_img">            
                                        <img src="{{ get_brand_setting('logo.footer_logo') }}" alt="Lanes group logo" width="50">
                                        <span>
                                            &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="http://www.aecordigital.com/">{!! get_brand_setting('footer_text_part2') !!}</a>
                                        </span>
                                    </span>
                                @else
                                    <span>
                                        &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="http://www.aecordigital.com/">{!! get_brand_setting('footer_text_part2') !!}</a>
                                    </span>
                                @endif
                                <span>
                                   <a href="/privacypolicy">Privacy policy</a>
                                </span>
                                <span>
                                   <a href="/cookiepolicy">Cookie policy</a>
                                </span>
                            </div>
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
