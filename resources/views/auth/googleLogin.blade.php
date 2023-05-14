<!DOCTYPE html>
<html>

<head>
    <title>{{ get_brand_setting('title') }}</title>
    <!-- common css starts -->
    <meta name="_token" content="{!! csrf_token() !!}" /> {{--
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" /> --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href="{{ elixir('css/simple-line-icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ elixir('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ elixir('css/uniform.default.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ elixir('css/login.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ elixir('css/components-md.css') }}" id="style_components" rel="stylesheet" type="text/css" />
    <link href="{{ elixir('css/plugins-md.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ elixir('css/layout.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ elixir('css/grey.css') }}" rel="stylesheet" type="text/css" id="style_color" />
    <link href="{{ elixir('css/theme-overrides.css') }}" rel="stylesheet" type="text/css" id="style_color" />
    <link href="{{ elixir('css/custom.css') }}" rel="stylesheet" type="text/css" /> 
    {{-- <link href="{{ asset(get_brand_setting('stylesheet')) }}" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ elixir('css/brand/main.css') . '?' . http_build_query(['v' => setting('primary_colour')]) }}" rel="stylesheet" type="text/css" />
</head>
<style id="antiClickjack">body{display:none !important;}</style>
<body class="page-md login">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                <div class="center">
                    <img src="{{ setting('logo') }}" class="logo" style="padding-top:20px;" /> {{-- <img src="{{ asset(get_brand_setting('logo.transparent')) }}" class="logo" style="padding-top:20px;" /> --}} {{--
                    <h2 class="brand-product-name">
                    <img src="{{ asset(get_brand_setting('logo.fleet_logo')) }}" class="fleet_logo" />
                </h2> --}}
                    <h2 class="brand-product-name"><img src="{{ asset(get_brand_setting('logo.fleet_logo')) }}" class="fleet_logo" /></h2>
                </div>
                <div class="content">

                    <form class="login-form" action="/auth/login" method="post">
                        {{  csrf_field() }}
                        <div class="alert alert-success display-hide">
                            <button class="close" data-close="alert"></button>
                            <span>
                        Enter your email and password. </span>
                        </div>
                        <!--  <ul class="title_heading">
                        <li><a href="javascript:;" id="" class="form-title title">Vehicle Check Account</a></li>
                        <li><a href="javascript:;" id="register-btn" class="form-title title">Lanes Account</a></li> 
                    </ul> -->
                        <div class="clearfix"></div>
                        @if (session('status'))
                        <br />
                        <div class="alert alert-success">
                            <button class="close" data-close="alert"></button>
                            {{ session('status') }}
                        </div>
                        @endif @if (session('message'))
                        <div class="alert alert-success js-password-success">
                            <button class="close" data-close="alert"></button>
                            {{ session('message') }}
                        </div>
                        @endif {{--
                        <div class="center">
                            <h3 class="form-title">Log into your Lanes Group Vehicle Check Account</h3>
                        </div> --}}

                        <div class="clearfix"></div>
                        @if($errors->has())
                        <input type="hidden" value="1" name="lanes-error" class="lanes-error"> @foreach ($errors->all() as $error)
                        <div class="alert alert-success">
                            <button class="close" data-close="alert"></button>
                            {{ $error }}
                        </div>
                        @endforeach @endif {{-- @include('flash::message') --}}
                        <!-- <p class="hint">
                         Enter your FieldViewer credentials below.
                    </p> -->
                        <div class="">
                            <p class="login_title">
                                Please login below:
                            </p>
                        </div>
                        <div class="form-group form-md-line-input has-error form-md-floating-label">
                            <div class="input-group right-addon">
                                <!-- <input type="text" class="form-control"> -->
                                <input class="form-control" type="text" placeholder="Username or Email" autocomplete="off" name="identity" value="{{ old('identity') }}" tabindex="1" /> {{--
                                <label for="form_control_1 visible-ie8 visible-ie9">Email</label> --}}
                            </div>
                        </div>

                        <div class="form-group form-md-line-input has-error form-md-floating-label">
                            <div class="input-group right-addon">
                                <!-- <input type="text" class="form-control"> -->
                                <input class="form-control" type="password" placeholder="Password" autocomplete="off" id="register_password" name="password" tabindex="2" /> {{--
                                <label for="form_control_1 visible-ie8 visible-ie9">Password</label> --}}
                            </div>
                        </div>

                        <!-- <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Email</label>
                        <input class="form-control placeholder-no-fix" type="email" autocomplete="off" placeholder="Email" name="email" value="{{ old('email') }}"/>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Password</label>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Password" name="password"/>
                    </div> -->
                        <div class="form-group form-md-line-input clearfix login_text">
                            <label>
                                <input type="checkbox" name="remember" tabindex="3"> Remember me
                            </label>
                            <a href="javascript:;" id="forget-password" class="forget-password"><u>Forgot password?</u></a>
                        </div>
                        <div class="form-actions center">
                            <!-- <a href="javascript:;" id="forget-password" class="forget-password">Forgot Password?</a>
                        <br><br> -->
                            <button type="submit" id="register-submit-btn" class="btn red-rubine" tabindex="4">Login</button>
                        </div>
                        <br>
                        <div class="form-group form-md-line-input clearfix center help_email">
                            <a href="mailto:support@imastr.com" id="help-email"><script>document.write("support"+"@imastr"+".com");</script></a> {{-- <a href="javascript:;" id="forget-password" class="forget-password">Forgot password?</a> --}}
                        </div>
                    </form>

                    {{-- BEGIN FORGOT PASSWORD FORM --}}
                    <form class="forget-form" action="/password/email" method="post">
                        <div class="alert alert-success display-hide">
                            <button class="close" data-close="alert"></button>
                            <span id="error-msg"></span>
                        </div>
                        {{  csrf_field() }}
                        <div class="">
                            <p class="login_title">Enter your email address and an email will be sent to you to reset your password.</p>
                        </div>

                        <div class="form-group form-md-line-input has-error form-md-floating-label">
                            <div class="input-group right-addon">
                                <input class="form-control" type="email" placeholder="Email" autocomplete="off" name="email" value="{{ old('email') }}" id="reset-email" /> {{--
                                <label for="form_control_1 visible-ie8 visible-ie9">Email</label> --}}
                            </div>
                        </div>

                        <div class="form-actions center">
                            <button type="submit" class="btn red-rubine">Request reset link</button>
                        </div>
                        <div class="form-group form-md-line-input" style="text-align: center;">
                            <a href="javascript:;" id="back-btn" class="forget-password">Go back to login page</a>
                        </div>
                    </form>
                    {{-- END FORGOT PASSWORD FORM --}}

                    <form method="POST" action="" class="register-form center">

                        <!-- <ul class="title_heading">
                        <li><a href="javascript:;" id="vehicle-btn" class="form-title title">Vehicle Check Account</a></li>
                        <li><a href="javascript:;" id="" class="form-title title">Lanes Account</a></li> 
                    </ul> -->
                        @include('flash::message')

                        <div class="center">
                            <img src="/img/logo/lanes-group-logo-header.png" class="logo">

                            <h3 class="form-title">Log Into your Lanes Group Vehicle Check Account</h3>
                        </div>

                        <div class="clearfix"></div>

                        <p>
                            Sign-in using your Lanes Google Apps account. You may be redirected to the Google authentication page.
                        </p>

                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span>
                        Enter any username and password. </span>
                        </div>

                        <div class="form-actions">
                            <a href="/googleLogin" class="btn red-rubine">Lanes Sign-in</a>
                        </div>
                    </form>
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
                                        &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/">{!! get_brand_setting('footer_text_part2') !!}</a>
                                    </span>
                                </span>
                                @else
                                <span>
                                    &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/" target="_blank" rel="noopener noreferrer">{!! get_brand_setting('footer_text_part2') !!}</a>
                                </span> @endif
                                <span>
                               <a href="/privacypolicy">Privacy policy</a>
                            </span>
                                <span>
                               <a href="/cookiepolicy">Cookie policy</a>
                            </span> @if(env('SHOW_APP_DOWNLOAD_PAGE') == 1)
                                <span>
                               <a href="/apps">Download app</a>
                            </span> @endif
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

    <script type="text/javascript" id="antiClickjackJS">
        if (self === top) {
            var antiClickjack = document.getElementById("antiClickjack");
            antiClickjack.parentNode.removeChild(antiClickjack);
        } else {
            top.location = self.location;
        }
    </script>
    <script>
        jQuery(document).ready(function() {
            Metronic.init(); // init metronic core components
            Layout.init(); // init current layout
            Login.init();
            Demo.init();
            if ($(".lanes-error").length) {
                $('#register-btn').click();
            }
            $("input").each(function() {
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