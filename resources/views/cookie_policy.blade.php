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
         <div class="col-xs-12 col-sm-10 col-md-10 col-lg-7">
            <div class="center">
               <img src="{{ setting('logo') }}" class="logo" style="padding-top:20px;"/>
               <!-- <img src="{{ asset(get_brand_setting('logo.transparent')) }}" class="logo" style="padding-top:20px;"/>
                  <h2 class="brand-product-name"><img src="{{ asset(get_brand_setting('logo.fleet_logo')) }}" class="fleet_logo" /></h2> -->
            </div>
            <div class="content policy_cookie">
               <form class="login-form">
                  <a class="btn btn-primary pull-right privacy-cookie-login"  href="{{ url('login') }}">Login</a>
                  <h2><b>Cookie Policy</b></h2>
                  <p class="mb-10">This policy was updated on the 2nd January 2018.</p>
                  <p class="mb-10"><strong>i</strong>mastr is the data controller and the operator of this website.</p>
                  <p class="mb-10">Our postal address for any correspondence or data requests is:</p>
                  <span><strong>i</strong>mastr</span><br/>
                  <span>Barley Mow Centre</span><br/>
                  <span>10 Barley Mow Passage</span><br/>
                  <span>Chiswick</span><br/>
                  <span>London</span><br/>
                  <span>W4 4PH</span><br/> 
                  <p class="mt-2 mb-2 privacy-cookie-link">Email: <a href="mailto:info@imastr.com" class="text-primary"> <script>document.write("info"+"@imastr"+".com");</script></a> 
                     <br>Telephone: 020 3887 3955
                  </p>
                  <dl>
                  <h4 class="font-weight-700">1. What is a cookie?</h4>
                  <p class="mb-10">Our website uses cookies, tracking pixels and related technologies. 
                     Cookies are small data files that are placed on your computer, laptop or mobile 
                     device by websites that you visit. They are widely used in order to make websites work,
                     or work more efficiently, as well as provide information to the owners of the site.
                  </p>
                  <h4 class="font-weight-700">2. What cookies do we use?</h4>
                  <p class="mb-10">Our site uses cookies placed by us or third parties to:</p>
                  <ul style="padding-left: 15px;">
                     <li>operate and personalise the website,</li>
                     <li>to improve your experience,</li>
                     <li>to show you relevant ads for our services when you visit other websites.</li>
                  </ul>
                  <p class="mb-10">We also use analytical cookies that allow us to recognise and count the number of visitors to
                     our website and to see how visitors move around the site when they're using it. 
                     We use the information to compile reports and to help us to improve the website.
                  </p>
                  <p class="mb-10">In addition cookies may be used to track how you used our site during your visit 
                     to then show you relevant ads for our services when you visit other websites.
                     No personal information is stored, saved or collected by these cookies.
                  </p>
                  <h4 class="font-weight-700">3. How can you opt out of cookies and can I change my cookie settings?</h4>
                  <p class="mb-10">You can use your browser settings to accept or reject new cookies and to delete existing 
                     cookies. You can also set your browser to notify you each time new cookies are
                     place on your computer or other device.
                  </p>
                  <p class="mb-10 privacy-cookie-link">To find out more about cookies, including how to see what cookies have been set and how to manage or delete them, visit <a href="http://www.aboutcookies.org.uk/" class="text-primary">www.aboutcookies.org.uk</a> or <a href="http://www.allaboutcookies.org/" class="text-primary">www.allaboutcookies.org</a>.</p>
                  <p class="mb-10 privacy-cookie-link">Please however be aware that if you disable any cookies this may stop our website from functioning properly.</p>
                  <p class="mb-10 privacy-cookie-link">You can opt out of receiving targeted advertising by visiting the <a href="http://www.youronlinechoices.com/uk/" class="text-primary">EDAA</a>. Or the <a href="http://optout.networkadvertising.org/#!/g" class="text-primary">Network Advertising Initiative.</a></p>
                  <h4 class="font-weight-700">4. Do cookies or their use ever change?</h4>
                  <p class="mb-10">From time to time, we may use customer information for new, unanticipated uses not previously disclosed in our privacy policy. If our information practices change at some time in the future we will post the policy changes to our website to notify you of these changes and provide you with the ability to opt out of these new uses.</p>
                  <h4 class="font-weight-700">5. What do we know about you and how can you find out more?</h4>
                  <p class="mb-10">Upon request, we can provide website visitors with access to any information that we maintain about them, including:</p>
                  <ul style="padding-left: 15px;">
                     <li>Unique identifier information (e.g. customer number or password)</li>
                     <li>Contact information (e.g. name, address, phone number)</li>
                     <li>Consumers can access and change any of the personal information we hold by contacting us by 
                        mail, email or telephone using the contact details at the top of this page.
                     </li>
                  </ul>
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
                        &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/">{{ get_brand_setting('footer_text_part2') }}</a>
                        </span>
                        </span>
                        @else
                        <span>
                        &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/" target="_blank" rel="noopener noreferrer">{!! get_brand_setting('footer_text_part2') !!}</a>
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
</body>
</html>    