<div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse">
        <ul class="page-sidebar-menu page-sidebar-menu-hover-submenu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
            <?php if (!Auth::user()->isWorkshopManager() && !Auth::user()->isUserInformationOnly()): ?>
            @if(Auth::user()->can('dashboard.manage') || Auth::user()->can('dashboard.cost.manage'))
            <li class="start active ">
                <a href="/">
                    <span class="title">Dashboard</span>
                    <span class="jv-icon jv-home"></span>
                    <!-- <img src="/img/sidebar/home.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endif
            <?php endif ?>

            <!-- @can('planner.manage')
            <li>
                <a href="{{ url('/planner') }}">
                    <span class="title">Planner</span>
                    <span class="jv-icon jv-calendar"></span>
                </a>
            </li>
            @endcan -->

           @can('fleet.planning')
            <li>
                <a href="{{ url('/fleet_planning')}}">
                    <span class="title">Fleet Planning</span>
                    <span class="jv-icon jv-calendar"></span>
                </a>
            </li>
            @endcan

            @can('check.manage')
            <li>
                <a href="{{ url('/checks') }}">
                    <span class="title">Vehicle Checks</span>
                    <span class="jv-icon jv-checklist"></span>
                    <!-- <img src="/img/sidebar/check.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endcan

            @can('defect.manage')
            <li>
                <a href="{{ url('/defects') }}">
                    <span class="title">Vehicle Defects</span>
                    <!-- <span class="jv-icon icon-warning-sign"></span> -->
                    <!-- <span class="sidebar-img"><img src="/img/sidebar/error-1.svg" class="sidebar-icon"></span> -->
                    <span class="jv-icon jv-vehicle-defect"></span>
                </a>
            </li>
            @endcan

            @can('incident.manage')
                @if(setting('is_incident_reports_enabled') == 1)
                    <li>
                        <a href="{{ url('/incidents') }}">
                            <span class="title">Reported Incidents</span>
                            <!-- <span class="jv-icon icon-warning-sign"></span> -->
                            <!-- <span class="sidebar-img"><img src="/img/sidebar/error-1.svg" class="sidebar-icon"></span> -->
                            <span class="jv-icon jv-vehicle-crash"></span>
                        </a>
                    </li>
                @endif
            @endcan

            <!--<li>
             <?php //if(Auth::user()->isWorkshopManager()) { ?>
            <?php if (Auth::user()->isWorkshopManager()): ?>
                <li>
                    <a href="{{ url('/defects') }}">
                        <span class="title">Defects</span>
                        <span class="jv-icon icon-warning-sign"></span>

                        <span class="jv-icon jv-error"></span>
                    </a>
                </li>
            <?php endif ?>
            <li> -->

            <!-- @can('search.manage')
            <li>
                <a href="{{ url('/vehicles/planning')}}">
                    <span class="title">Vehicle Planning</span>
                    <span class="jv-icon jv-calendar-time"></span>
                </a>
            </li>
            @endcan -->

            @can('profiles.manage')
            <li>
                <a href="{{ url('/profiles')}}">
                    <span class="title">Vehicle Profiles</span>
                    <span class="jv-icon jv-truck-info"></span>
                </a>
            </li>
            @endcan
            
            @can('search.manage')
            <li>
                <a href="{{ url('/vehicles')}}">
                    <span class="title">Vehicle Search</span>
                    <span class="jv-icon jv-car"></span>
                    <!-- <img src="/img/sidebar/vehicle.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endcan

            @can('workshopuser.manage')
            <li>
              <a href="{{ url('/workshops') }}">
                    <span class="title">Workshops</span>
                    <!-- <span class="jv-icon icon-warning-sign"></span> -->
                    <!-- <span class="sidebar-img"><img src="/img/sidebar/error-1.svg" class="sidebar-icon"></span> -->
                    <span class="jv-icon jv-tools">
                       <!-- <img class="icon-image" src="{{ asset('img/infographics.svg')}}">-->
                    </span>
                </a>
            </li>
            @endcan

            @can('messaging.manage')
            <li>
                <a href="{{ url('/messages') }}">
                    <span class="title">Messaging</span>
                    <span class="jv-icon jv-chats"></span>
                    <!-- <img src="/img/sidebar/reports.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endcan

            @can('eanrnedrecognition.manage')
                @if(setting('is_dvsa_enabled') == 1)
                    <li>
                        <a href="{{ url('/dvsa') }}">
                            <span class="title">Earned Recognition</span>
                            <span class="jv-icon jv-crown"></span>
                        </a>
                    </li>
                @endif
            @endcan

            @can('report.manage')
            {{-- <li>
                <a href="{{ url('/reports') }}">
                    <span class="title">Reports</span>
                    <span class="jv-icon jv-doc"></span>
                    <!-- <img src="/img/sidebar/reports.png" class="sidebar-icon"> -->
                </a>
            </li> --}}
            <li>
                <a href="{{ url('/reports') }}">
                    <span class="title">Reports</span>
                    <span class="jv-icon jv-doc"></span>
                </a>
            </li>
            @endcan
            
            @can('alertcentre.manage')
                @if(setting('is_alertcentre_enabled') == 1)
                    <li>
                        <a href="{{ url('/alert_centres')}}">
                            <span class="title">Alert Centre</span>
                            <span class="jv-icon jv-error"></span>
                        </a>
                    </li>
                @endif
            @endcan
        
            @can('user.manage')
            <li>
                <a href="{{ url('/users') }}">
                    <span class="title">User Management</span>
                    <span class="jv-icon jv-user"></span>
                    <!-- <img src="/img/sidebar/user.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endcan

            @can('settings.manage')
            <li>
                <a href="{{ url('/settings') }}">
                    <span class="title">Settings</span>
                    <span class="jv-icon jv-cog">{{-- <i class="icon-settings" style="font-size: 30px;"></i> --}}</span>
                    <!-- <img src="/img/sidebar/user.png" class="sidebar-icon"> -->
                </a>
            </li>
            @endcan

            @can('telematics.manage')
                @if(setting('is_telematics_enabled') == 1)
                    <li>
                        <a href="{{ url('/telematics')}}">
                            <span class="title">Telematics</span>
                            <span class="jv-icon jv-route"></span>
                        </a>
                    </li>
                @endif
            @endcan
            
        </ul>
    </div>
</div>
