<ul class="journey-items">
    <?php $flag=0; ?>
    @foreach($finalJourneyData as $key => $point)
      <li class="journey-item" id="{{$point['id']}}_journeyItem">
          <div class="journey-timeline">
            <span class="journey-timeline-bar" aria-hidden="true"></span>
            @if($point['is_incident'])
                <div class="journey-timeline-wrapper has-incident js-incident" data-incident-key="{{ $flag }}" id="{{$point['id']}}_jd_timeline_wrapper" data-point-lat="{{ $point['lat'] }}" data-point-lon="{{ $point['lon'] }}">
                <?php $flag++; ?>
            @else
                <div class="journey-timeline-wrapper {{$point['class']}}" data-point-lat="{{ $point['lat'] }}" data-point-lon="{{ $point['lon'] }}" id="{{$point['id']}}_jd_timeline_wrapper">
            @endif
                <div class="journey-timeline-wrapper-datetime">
                  <div class="journey-date">{{\Carbon\Carbon::parse($point['time'])->format('d/m/y')}}</div>
                  <div class="date-time">{{\Carbon\Carbon::parse($point['time'])->format('H:i')}}</div>
                </div>
              <div class="journey-timeline-wrapper-checkpoint">
                <div class="number-area {{in_array($point['label'],['Idle End']) ? 'number-area-red-border':''}}"></div>
              </div>
              <div class="journey-timeline-wrapper-info">
		            @if($point['post_code'] != '')
                  <div class="journey-location" id="{{$point['id']}}_jd_address">{{$point['street']}},&nbsp; {{$point['post_code']}}</div>
                @else
                  <div class="journey-location" id="{{$point['id']}}_jd_address">Location ({{$point['lat']}},&nbsp; {{$point['lon']}})</div>
                @endif                
                <label for="" id="{{$point['id']}}_jd_point_label" class="{{in_array($point['label'],['Idle Start','Idle End']) ? 'label-results label-danger':''}}">{{$point['label']}}</label>
                <ul class="list-unstyled list-inline">
                  <li><strong id="{{$point['id']}}_jd_miles">{{$point['miles']}} miles</strong></li>
                  <li>Driving: <strong id="{{$point['id']}}_jd_driving_min">{{$point['driving']}} min</strong></li>
                  @if($point['ns'] == 'tm8.gps.idle.start' || $point['ns'] == 'tm8.gps.idle.end')
                    <li>Idling: <strong id="{{$point['id']}}_jd_idling">{{$point['idling']}}</strong></li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
      </li>
    @endforeach
</ul>