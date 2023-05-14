<!DOCTYPE html>
<html lang="en">
<head>
   {{-- <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/flatly/bootstrap.min.css" rel="stylesheet"
        integrity="sha256-sHwgyDk4CGNYom267UJX364ewnY4Bh55d53pxP5WDug= sha512-mkkeSf+MM3dyMWg3k9hcAttl7IVHe2BA1o/5xKLl4kBaP0bih7Mzz/DBy4y6cNZCHtE2tPgYBYH/KtEjOQYKxA==" 
        crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>--}}
     {{--<link href="{{ asset(get_brand_setting('pdf_stylesheet')) }}" rel="stylesheet" type="text/css"/>--}}
     <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
     <link href="{{ asset('css/pdf-font.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/brand/pdf.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        ul.defect_note_ul li.list-group-item
        {
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-xs-6">
            <!-- <img src="{{ asset('img/lanes-login-small.png') }}" alt="logo img"/> -->
            <img src="{{ setting('logo') }}" alt="logo img" class="logo-default" />
            @yield('pdf_title')
        </div>
    </div>    
    @yield('content')
</body>
</html>