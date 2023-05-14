<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <div class="d-flex align-items-center justify-content-between h-100">
                <a class="brand-logo d-flex h-100 align-items-center justify-content-center" href="/">
                    {{-- <img src="{{ asset(get_brand_setting('logo.colored')) }}" alt="logo" class="logo-default"/ style="height: 65px;"> --}}
                    <img src="{{ setting('logo') }}" alt="logo" class="logo-default" style="height: 65px;">
                </a>
                <div class="menu-toggler-close d-flex h-100 align-items-center justify-content-center">
                    <div class="menu-toggler sidebar-toggler">
                        <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
                    </div>
                </div>
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"></a>
        
        <div class="page-top">
            <div class="top-menu top-menu-left page-title-outer">
                <h1 class="page_title">
                    @if (get_brand_setting('brand_product_name', false))
                        <strong>{{ get_brand_setting('brand_product_name') }}</strong>{{ get_brand_setting('brand_product_name_part2') }} - 
                    @endif
                    {{$title}}
                </h1>
            </div>
            <div class="top-menu">
                <ul class="nav navbar-nav pull-right">
                    @if(Auth::user()->isSuperAdmin())
                    
                    <li class="dropdown dropdown-extended dropdown-notification">
                        <a href="javascript:;" class="dropdown-toggle js-notification-list">
                            <i class="icon-bell"></i>
                            <span class="badge badge-default js-notification-count notification-count">{{ $notification_details['unReadNotificationCount'] > 0 ? $notification_details['unReadNotificationCount'] : '' }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title font-weight-700">Notifications</h4>
                                <a class="js-close-notification font-red-rubine" data-dismiss="modal" aria-label="Close">
                                <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <li class="notification-wrapper">
                                <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 250px;">
                                    <ul class="dropdown-menu-list scroller notifications" style="height: 250px; overflow: hidden; width: auto;" data-handle-color="#637283" data-initialized="1">
                                        @foreach($notification_details['allNotification'] as $notification)
                                        <li data-id="{{ $notification->id }}" id="notification_id_{{ $notification->id }}" class="@if($notification->is_read != true) unread-notification @endif">
                                            {{-- <a href="javascript:void(0);"> --}}
                                                <div class="row details details-icon">
                                                    <div class="col-md-12">
                                                        <div class="d-flex">
                                                            <div>
                                                                <div class="label label-sm label-icon label-success d-flex align-items-center justify-content-center notification-icon">
                                                                    <i class="fa fa-info"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="font-red-rubine pull-right js-delete-notification cursor-pointer" id="">
                                                                    <i class="jv-icon jv-close"></i>
                                                                </div>
                                                                <div class="notification-message-color @if($notification->is_read == true) notification-text-color @endif">
                                                                    <a href="{{ url('defects/' .$notification->defect_id) }}"><strong>{{ $notification->message }}</strong></a>
                                                                </div>
                                                                <div class="row read-unread-button">
                                                                    <div class="col-md-6">
                                                                        <span class="time pull-left">{{ Carbon\Carbon::parse($notification->created_at)->diffForHumans()  }}</span>
                                                                    </div>
                                                                     <div class="col-md-6">
                                                                        <div class="r-checker pull-right">
                                                                            <input type="checkbox" class="js-notification-status" @if($notification->is_read != true) checked @endif data-toggle="toggle" data-on="Read" data-off="Unread">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {{-- </a> --}}
                                        </li>
                                        @endforeach
                                    </ul>
                                    <div class="slimScrollBar" style="background: rgb(99, 114, 131); width: 7px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 121.359px;">
                                    </div>
                                    <div class="slimScrollRail" style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(234, 234, 234); opacity: 0.2; z-index: 90; right: 1px;">
                                    </div>
                                </div>
                            </li>
                            <li class="notification-footer text-center">
                            </li>
                        </ul>                        
                    </li>
                    @endif

                    <li class="dropdown dropdown-user">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <span><i class="img-circle jv-icon jv-user"></i></span>
                            <span class="username username-hide-on-mobile">{{ Auth::user()->first_name }}</span>
                            <span><i class="img-circle jv-icon jv-downarrow"></i></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                            @if(Auth::user()->is_lanes_account != 1)
                            <li class="{{ Auth::user()->is_lanes_account }}">
                                <a href="#changePassword" id="changepasswordLink" data-toggle="modal"><i class="icon-lock-open"></i> Change password </a>
                            </li>
                           @endif
                           <li>
                                <a href="{{ url('auth/logout') }}">
                                <i class="icon-key"></i> Log out </a>
                           </li>
                          </ul>
                    </li>
                    <!-- <li class="dropdown">
                        <img src="" class="weather-icon" height="23">
                        <div class="weather-temperature"></div>
                    </li> -->
                    <li class="dropdown padding-left-20 weather-temperature-div">
                        <div class="nav_div">
                            <img src="" class="weather-icon" height="23">
                            <p class="weather-temperature"></p>
                        </div>
                    </li>
                    <li class="dropdown" id="header_task_bar">
                        <i class="jv-icon jv-clock"></i><div id="timer"></div>
                    </li>
                    <li class="dropdown">
                        @can('fleet.planning')<i class="jv-icon jv-calendar js-event-planner"></i>@endcan
                        <div id="date"></div>
                    </li>
                    <!-- <li class="dropdown" id="header_task_bar"> 
                        <div id="weather-title">                       
                            <img id="temperatureIcon" data-toggle="modal" class="weather-icon" style="width: 15px; margin-left: 10px;">
                            <a href="#portlet-geolocation" id="temperatureDiv" data-toggle="modal" class="weather-temperature font-blue"></a>
                        </div>
                    </li> -->
                    <li class="dropdown bar-logo">
                        <a class="navbar-brand d-flex align-items-center justify-content-center h-100" href="/">
                            {{-- <img src="{{ asset(get_brand_setting('logo.colored')) }}" alt="logo" style="height: 65px;"/> --}}
                            <img src="{{ setting('logo') }}" alt="logo" class="logo-default" style="height: 65px;">
                        </a>
                    </li>
                </ul>
            </div>
            <!-- END TOP NAVIGATION MENU -->
        </div>
        <!-- END PAGE TOP -->
    </div>
    <!-- END HEADER INNER -->
</div>
