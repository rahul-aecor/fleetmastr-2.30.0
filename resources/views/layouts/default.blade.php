<!DOCTYPE html>
<html>
<head>
    <title>{{ get_brand_setting('title') }}</title>
    <meta name=viewport content="width=device-width,initial-scale=1">
    <meta name="_token" content="{!! csrf_token() !!}"/>

    <style>
        :root {
            --primary-color: #{{setting('primary_colour')}};
            --primary-dark: #{{setting('primary_colour')}}e6;
            --primary-light: #{{setting('primary_colour')}}26;
            --primary-light-hover: #{{setting('primary_colour')}}40;
        }
    </style>
    <!-- <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>     -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href="{{ elixir('css/simple-line-icons.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/uniform.default.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-switch.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/datepicker/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jqgrid/ui.jqgrid-bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/select2/select2.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/datetimepicker/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/colorpicker/jquery.colorpicker.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css">
    <link href="{{ elixir('css/components-md.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/plugins-md.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/layout.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/grey.css') }}" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="{{ elixir('css/timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/weather.css') }}" rel="stylesheet" type="text/css"/> 
    <link href="{{ elixir('css/datepicker/bootstrap-datepicker.standalone.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/datepicker/bootstrap-datepicker3.standalone.min.css') }}" rel="stylesheet" type="text/css"/>
    {{-- <link href="{{ asset('css/datetimepicker/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/> --}}
    <link href="{{ elixir('css/new_color.css') }}" rel="stylesheet" type="text/css"/> 
    <link href="{{ elixir('css/jobviewer-font.css') }}" rel="stylesheet" type="text/css"/> 
    <!--[if lt IE 8]><!-->
    <link rel="stylesheet" href="{{ elixir('css/ie7/ie7.css') }}">
    <link href="{{ elixir('css/ie.css') }}" rel="stylesheet" type="text/css"/>
    <!--<![endif]-->
    <link href="{{ elixir('css/new_font/style.css') }}" rel="stylesheet" type="text/css"/> 

    @yield('plugin-styles')
    <link href="{{ elixir('css/theme-overrides.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-ui-custom.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/custom.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jqgrid-overrides.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('js/jcrop/css/jquery.Jcrop.css') }}" rel="stylesheet" type="text/css">
    {{-- <link href="{{ asset(get_brand_setting('stylesheet')) }}" rel="stylesheet" type="text/css"/> --}}
    <link href="{{ elixir('css/brand/main.css') . '?' . http_build_query(['v' => setting('primary_colour')]) }}" rel="stylesheet" type="text/css"/>
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link href="{{ elixir('css/chart.css') }}" rel="stylesheet" type="text/css"/>
    @yield('styles')
</head>
<style id="antiClickjack">
    body{display:none !important;}
</style>
<body class="page-md page-boxed page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid page-sidebar-closed-hide-logo">
    @include('_partials.header')
    <div class="page-container">
        @include('_partials.sidebar')
        <div class="page-content-wrapper">
            <div class="page-content clearfix">
                <section class="main-content">
                    @include('flash::message')
                    @yield('content')
                </section>
            </div>
        </div>
    </div>
    @include('_partials.footer')
    <script>
        var siteSettings = <?php echo json_encode([
            'primary_colour' => setting('primary_colour'),
        ]); ?>;
    </script>
    <!--[if lt IE 9]>
    <script src="{{ asset('/assets/global/plugins/respond.min.js') }}"></script>
    <script src="{{ asset('/assets/global/plugins/excanvas.min.js') }}"></script>
    <![endif]-->
    <script src="{{ elixir('js/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-migrate.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-ui.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-hover-dropdown.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.blockui.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.cokie.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.uniform.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/moment.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-switch.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/datepicker/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js" integrity="sha512-tlmsbYa/wD9/w++n4nY5im2NEhotYXO3k7WP9/ds91gJk3IqkIXy9S0rdMTsU4n7BvxCR3G4LW2fQYdZedudmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ elixir('js/toastr/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGrid.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/plugins/grid.setcolumns.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/select2/select2.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/colorpicker/jquery.colorpicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jcrop/js/jquery.Jcrop.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/datepicker/bootstrap-datepicker-1.9.0.min.js') }}" type="text/javascript"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    @yield('plugin-scripts')

    <script type="text/javascript">
        if (self === top) {
            var antiClickjack = document.getElementById("antiClickjack");
            antiClickjack.parentNode.removeChild(antiClickjack);
        } else {
            top.location = self.location;
        }
    </script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
        });
        $.jgrid.defaults.responsive = true;
        $.jgrid.defaults.styleUI = 'Bootstrap';        
    </script>
    <script src="{{ elixir('js/jqgrid/i18n/grid.locale-en.js') }}"></script>
    <script src="{{ elixir('js/metronic.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/layout.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/demo.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/chart.js') }}" type="text/javascript"></script>
    <!-- Date-Time Picker -->
    <script src="{{ elixir('js/datetimepicker/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/datetimepicker/quick-sidebar.js') }}" type="text/javascript"></script>

    <!-- UI Alert Box Script -->
    <script src="{{ elixir('js/bootbox/bootbox.min.js') }}" type="text/javascript"></script>
    <!-- form validation -->
    <script src="{{ elixir('js/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-validation/additional-methods.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery.extend(jQuery.validator.messages, {
            required: "This field is required"
        });

        var API_URL = "<?php echo config('api.url'); ?>";
    </script>
    <script src="{{ elixir('js/weather/openWeather.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/weather/weather.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/own-custom.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/custom-datepicker-auto-format.js') }}" type="text/javascript"></script>
    <!-- Start of imastr Zendesk Widget script -->
    {{-- <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=c1140e11-3540-4f27-9746-3e1ebb375340"> </script> --}}
    <!-- End of imastr Zendesk Widget script -->
    <script type="text/javascript">
        if ($.cookie('sidebar_closed') == undefined){
            $.cookie('sidebar_closed', '1');
        }
        Metronic.init();
        Layout.init();
    </script>

    <script type="text/javascript">
        {{-- @if(Auth::check())
            zE('webWidget', 'identify', {
                name: "{{ Auth::user()->first_name }}",
                email: "{{ Auth::user()->email }}",
            });
            window.zESettings = {
                webWidget: {
                  color: {
                    theme: siteSettings.primary_colour,
                    launcher: siteSettings.primary_colour,
                    launcherText: '#FFFFFF',
                    button: siteSettings.primary_colour,
                    header: siteSettings.primary_colour,
                  }
                },
                contactForm: {
                    title: {
                      '*': 'Report a problem'
                    },
                    subject: true,
                }
            };
        @endif --}}
        var brandName = "<?php echo env('BRAND_NAME') ?>";
    </script>
    @if(env('ENABLE_GOOGLE_ANALYTICS', 1))
        @include('_partials.googleanalytics')
    @endif
    @if(env('ENABLE_HOTJAR', 1))
        @include('_partials.hotjar')
    @endif

    @include('_partials.weather')
    @include('_partials.loading')
    @include('_partials.videoprocessing') 
    @include('_partials.changepassword') 

    @yield('scripts')
         
</body>
</html>