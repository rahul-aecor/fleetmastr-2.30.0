@include('_partials.telematics.mapui.header.location_info_header')
<div class="end-border-bottom" id="eebDivLiveTabLocationInfoDetailsBlock"></div>
@if(isset($data))
<div class="journey-timeline-wrapper-sidebar-body padding-5 divLiveTabLocationInfoDetailsBlock" id="divLiveTabLocationInfo">
    <div class="d-flex justify-content-between">
        <ul class="list-unstyled flex-grow-1">
            @if(isset($data->name) && $data->name != '')
            <li>{{$data->name.','}}</li>
            @endif
            @if(isset($data->address1) && $data->address1 != '')
            <li>{{$data->address1.','}}</li>
            @endif
            @if(isset($data->address2) && $data->address2 != '')
            <li>{{$data->address2.','}}</li>
            @endif
            @if(isset($data->town_city) && $data->town_city != '')
            <li>{{$data->town_city.','}}</li>
            @endif
            @if(isset($data->postcode) && $data->postcode != '')
            <li>{{$data->postcode}}</li>
            @endif
        </ul>
        {{-- <div class="flex-shrink-0">
            <a href="#" id="" class="align-items-center btn btn-h-45 d-flex justify-content-center">
                <i class="jv-icon jv-edit"></i> Edit
            </a>
        </div> --}}
    </div>
</div>
@endif