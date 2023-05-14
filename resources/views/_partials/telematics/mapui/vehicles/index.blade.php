<div class="end-border-bottom" id="eebLiveTabVehicleListFrontTab" style="display:{{$showHideBlock}};"></div>
<div class="journey-timeline-wrapper-sidebar-body padding-0" id="liveTabVehicleListFrontTab" style="display:{{$showHideBlock}};">
    <ul class="list-unstyled sidebar-lists ul-tab-list">
        @if($data->count()>0)
        @foreach($data as $d)
        <li class="sidebar-lists-item">
            <a class="sidebar-lists-item-link space-x-3 _vehiclelist" href="javascript:void(0);" vehicleId="{{$d->id}}">
                <div id="mappingDivStatusIcon{{$d->registration}}" class="flex-shrink-0 sidebar-lists-item-status {{$d->telematics_ns_label}}">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate title">
                        <span class="">{{$d->nominatedDriverName}}</span>
                    </div>
                    <div class="info">
                        <span>{{$d->vehicle_type}}</span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <div class="vehichle-number-plate"><span>{{$d->registration}}</span>
                    </div>
                </div>
            </a>
            @if(isset($singleRowFetch) && $singleRowFetch==true)
                        <button type="button" class="vehichle-number-plate-close-btn divLiveTabVehicleListCloseBtn" style="top:12px;right:12px;">
                            <span class="sr-only">Close panel</span>
                            <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        @endif
        </li>
        @endforeach
        @else
        <li><div class="text-center margin-top-5">No results found. To view more results please adjust your filters.</div></li>
        @endif
      </ul>
  </div>
  