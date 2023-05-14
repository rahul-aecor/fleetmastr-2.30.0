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
                  <a class="btn btn-primary pull-right privacy-cookie-login"  href="{{ url('/login') }}">Login</a>
                  <h2><b>Privacy Policy</b></h2>
                  <p class="mb-10">This policy was updated on the 2nd January 2018.</p>
                  <p class="mb-10">For the purposes of the Data Protection Act 1998, <strong>i</strong>mastr of Barley Mow Centre, 
                     10 Barley Mow Passage, Chiswick, London W4 4PH is the Data Controller and owner and operator of 
                     this website (as set out in our website terms of use).
                  </p>
                  <p class="mb-10 privacy-cookie-link">By visiting <a href="{{ url('/privacypolicy') }}" class="text-primary">{{ url('/privacypolicy') }},</a> contacting us and submitting personal data to us you are
                     accepting and consenting to the practices described in this policy and giving your 
                     consent that all personal data that you submit may be processed by us in the manner and for 
                     the purposes described below. If any time you wish to withdraw your consent please contact us as 
                     set out below.
                  </p>
                  <h4 class="font-weight-700">1. What information is collected on our website and how we use your information?</h4>
                  <p class="mb-10 privacy-cookie-link">When you visit our website our web server automatically records your IP 
                     address for security and audit purposes and cookie data for a variety of purposes. 
                     Cookie data, its use and how to control your cookie settings is covered in our
                     <a href="{{ url('/cookiepolicy') }}" class="text-primary">Cookie Policy.</a>
                  </p>
                  <p class="mb-10">We may also collect and process the following information volunteered by you for the purposes set out below:</p>
                  <ul>
                     <li><b> Customer survey information </b> to help us improve our products and services. Data collected and stored includes market research, interests, preferences and opinions.</li>
                     <li><b> Website registration information </b> so that users can set up a user account. Data collected and stored includes email address and password.</li>
                  </ul>
                  <h4 class="font-weight-700">2. Who do we share data with and under what circumstances?</h4>
                  <p class="mb-10">The information you provide may be shared with third-party suppliers such as contractors, 
                     agents and professional advisors to assist us in the use of the data for the purposes described in 
                     this policy.
                  </p>
                  <p class="mb-10">The information we collect may be disclosed when legally required to do so, at the request of 
                     governmental authorities conducting an investigation, to verify or enforce compliance with the 
                     policies governing our website and applicable laws or to protect against misuse or unauthorised use 
                     of our website.
                  </p>
                  <p class="mb-10">You agree and consent to us disclosing relevant information about you and/or personal data 
                     that you provide or is reasonably obtained in the course of any enquiry, to any person to whom we 
                     consider that it would be expedient to do so.
                  </p>
                  <p class="mb-10">Disclosures may be made for the purposes of preventing, detecting or discouraging crime and/or for 
                     the apprehension or prosecution of offenders and/or for the purpose of obtaining legal advice and/or
                     is otherwise necessary for the purposes of establishing, exercising or defending legal rights.
                  </p>
                  <p class="mb-10">In the event that the ownership of <strong>i</strong>mastr or the ownership or operation of this website is 
                     transferred to another party, the ownership and access to any data collected, processed or stored
                     will transfer to the new owner.
                  </p>
                  <h4 class="font-weight-700">3. Will we ever contact you?</h4>
                  <p class="mb-10">As part of our commitment to the highest levels of customer service, we may contact you using any 
                     personal information you have submitted to our website to assist you with your interaction with our 
                     service. In addition, we may from time to time contact you to provide useful information or offers on our products and services.
                     We will only do this if you have opted to receive communication from us.
                  </p>
                  <h4 class="font-weight-700">4. What if you don't want to be contacted?</h4>
                  <p class="mb-10 privacy-cookie-link">If you do not want to receive email from us in the future, please let us know by contacting us by
                     email <a href="mailto:info@imastr.com" class="text-primary">info@imastr.com</a> or telephone (020 3887 3955).
                  </p>
                  <p class="mb-10">Alternatively, if you have received a previous email from us, you will find a personalised 
                     unsubscribe link in the footer of the email, where you can remove yourself from our list.
                  </p>
                  <h4 class="font-weight-700">5. What about cookies?</h4>
                  <p class="mb-10 privacy-cookie-link">Our website uses cookies, tracking pixels and related technologies. 
                     Please refer to our <a href="{{ url('/cookiepolicy') }}" class="text-primary">Cookie Policy</a>
                     for more information.
                  </p>
                  <h4 class="font-weight-700">6. Changes to this privacy notice</h4>
                  <p class="mb-10">We keep our privacy notice under regular review. Any changes we make to our privacy policy in the 
                     future will be posted on this page and, where appropriate, notified to you by email. Please check 
                     back frequently to see any updates or changes to our privacy policy.
                  </p>
                  <h4 class="font-weight-700">7. How to contact us</h4>
                  <p class="mb-10">If you want to request any further information about our privacy policy or would like to submit any comments or 
                     requests you can email us or write to at the address below.
                  </p>
                  <span><strong>i</strong>mastr</span><br/>
                  <span>Barley Mow Centre</span><br/>
                  <span>10 Barley Mow Passage</span><br/>
                  <span>Chiswick</span><br/>
                  <span>London</span><br/>
                  <span>W4 4PH</span><br/>
                  <p class="mt-2 privacy-cookie-link">Email: <a href="mailto:info@imastr.com" class="text-primary"> info@imastr.com</a> <br>Telephone: 020 3887 3955</p>
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