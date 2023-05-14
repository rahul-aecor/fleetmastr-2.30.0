@include('_partials.telematics.mapui.header.location_header')
<div class="end-border-bottom" id="ebbDivLiveTabLocationCategoryDetailsBlock"></div>
<div class="journey-timeline-wrapper-sidebar-body padding-0 divLiveTabLocationCategoryDetailsBlock" id="liveTabLocationListFrontTab">
    <ul class="list-unstyled sidebar-lists ul-tab-list">
        @if(isset($data) && !empty($data))
            @foreach($data as $d)
                <li class="sidebar-lists-item">
                        <a class="sidebar-lists-item-link space-x-3 _locationList" href="javascript:void(0);" locationIdByCategory="{{$d->id}}">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="pin-marker" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--primary-color)">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate title">
                                    <span>{{$d->name}}</span>
                                </div>
                                <div class="info">
                                    <span>{{$d->address}}</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="next-arrow-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </a>
                </li>
            @endforeach
        @endif
    </ul>
</div>
