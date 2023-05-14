<div class="end-border-bottom" id="eebLiveTabUserListFrontTab"></div>
<div class="journey-timeline-wrapper-sidebar-body padding-0" id="liveTabUserListFrontTab">
    <ul class="list-unstyled sidebar-lists ul-tab-list">
        @if($data->count()>0)
            @foreach($data as $d)
                <li class="sidebar-lists-item">
                    <a class="sidebar-lists-item-link space-x-3 _userList" href="javascript:void(0);" vehicleId="{{$d['vehicle_id']}}">
                        <div class="flex-shrink-0 sidebar-lists-item-status {{$d['telematics_ns_label']}}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate title">
                                <span>{{$d['driver_name']}}</span>
                            </div>
                            <div class="info">
                                <span>{{$d['vehicle_model']}}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="vehichle-number-plate">{{$d['vehicle_registration']}}</div>
                        </div>
                    </a>
                </li>
            @endforeach
        @else
            <li><div class="text-center margin-top-5">No results found</div></li>
        @endif
    </ul>
  </div>

  