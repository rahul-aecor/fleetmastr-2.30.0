@extends('layouts.pdf')

@section('pdf_title') 
  Vehicle Check Details
@endsection

@section('content')
    <div class="row" style="margin-top: 1px;">
      <!--<div class="panel panel-info">
     <div class="panel-heading">
        <h3 class="panel-title">Vehicle Summary</h3>
      </div> 
      <div class="panel-body">-->
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Vehicle Summary</div>
            </div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Registration:</td>
                        <td><a href="{{ url('/vehicles/' .  $check->vehicle->id) }}">{{ $check->vehicle->registration }}</a></td>
                    </tr>
                    <tr>
                        <td>Date added to fleet:</td>
                        <td>{{ $check->vehicle->dt_added_to_fleet }}</td>
                    </tr>                    
                    <tr>
                        <td>Category:</td>
                        <td>{{ $check->vehicle->type->present()->vehicle_category_to_display() }}</td>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td>{{ $check->vehicle->type->vehicle_type }}</td>
                    </tr>
                    <tr>
                        <td>Manufacturer:</td>
                        <td>{{ $check->vehicle->type->manufacturer }}</td>
                    </tr>
                    <tr>
                        <td>Model:</td>
                        <td>{{ $check->vehicle->type->model }}</td>
                    </tr>
                    <tr>
                        <td>Odometer:</td>
                        @if ($check->odometer_reading)
                            <td>{{ number_format($check->odometer_reading)  . ' ' . $check->vehicle->type->odometer_setting }}</td>
                        @else
                            <td>{{ number_format($check->vehicle->last_odometer_reading)  . ' ' . $check->vehicle->type->odometer_setting }}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Vehicle status:</td>
                        <td><span class="label vehicle-status-view {{ $check->vehicle->present()->label_class_for_status }} label-results" >
                        {{ $check->vehicle->status }}</span></td>
                    </tr>
                    <tr>
                        <td>Result:</td>
                        <td>                            
                            {{ $check_details->total_defect }} {{ str_plural('defect', $check_details->total_defect) }} &nbsp;
                            <span class='label label-results {{ $check->present()->label_class_for_status }}'>{{ $check->present()->status_to_display }}</span>
                        </td>
                    </tr> 
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Vehicle Check Data</div>
            </div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Created by:</td>
                        <td>{{ $check->creator->first_name }} {{ $check->creator->last_name }} (<a href="mailto:{{ $check->creator->email }}" class="font-blue">{{ $check->creator->email }}</a>)</td>
                    </tr>
                    <tr>
                        <td>Created date:</td>
                        <td>{{ $check->present()->formattedReportDatetime() }}</td>
                    </tr>
                    <tr>
                        <td>Last modified by:</td>
                        <td>{{ $check->updater->first_name }} {{ $check->updater->last_name }} (<a href="mailto:{{ $check->updater->email }}" class="font-blue">{{ $check->updater->email }}</a>)</td>
                    </tr>
                    <tr>
                        <td>Last modified date:</td>
                        <td>{{ $check->present()->formattedUpdatedAt() }}</td>
                    </tr>
                    <tr>
                        <td>Check:</td>
                        <td>{{ $check->present()->types_to_display }}</td>
                    </tr>
                    <tr>
                        <td>Check duration:</td>
                        <td>{{ $check->present()->duration_to_display }}</td>
                    </tr>
                    <tr>
                        <td>Trailer attached:</td>
                        <td>{{ $check->is_trailer_attached == 1 ? "Yes" : "No" }}</td>
                    </tr> 
                    <tr>
                        <td>Trailer ID:</td>
                        <td>{{ $check->is_trailer_attached == 1 ? $check->trailer_reference_number : "Not applicable" }}</td>
                    </tr>  
                </tbody>
            </table>
        </div>
      <!-- </div>
    </div> -->        
    </div>

    @if((isset($check->location)) && ($check->type == 'Vehicle Check On-call' || $check->type == 'Vehicle Check'))
        <div class="row margin-top-20">
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="portlet box">
                            <div class="portlet-title bg-red-rubine">
                                <div class="caption">Check Location ({{ $location[0] }}; {{ $location[1] }})</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 margin-top-20">
                <div id="checklocationMap" class="gmaps"></div>            
                <?php 
                    $markerIcon = asset('img/marker-pin-complete.png');
                ?>
                @if($locationImage != "")
                    {{-- <img style="width:100%" src="https://maps.googleapis.com/maps/api/staticmap?center:{{$location[0]}},{{$location[1]}}&format=png&zoom=15&scale=2&size=1000x250&maptype=roadmap&markers=color:red%7C{{$location[0]}},{{$location[1]}}&key={{ env('GOOGLE_MAP_KEY') }}" /> --}}
                    <img style="width:100%" src="{{ $locationImage }}">
                @else
                    <img style="width:100%" src="{{ asset('img/no-gps.png') }}">
                @endif 
            </div>
        </div>
    @endif

    <div class="row check_screens">
        <div class="col-xs-12">
            @foreach ($check_details->screens->screen as $screen)
                @if ($screen->_type === 'list') 
                    @foreach ($screen->options->optionList as $option)
                    @if(!isset($option->related_to) || $option->related_to !== 'trailer' || ($option->related_to === 'trailer' && $check->is_trailer_attached == 1))
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">{{ $option->text }}</div>
                    </div>
                    <table class="table table-striped">
                        <tbody>
                            @if(!$option->question_type || $option->question_type == 'yesno')
                                @foreach ($option->defects->defect as $defect)
                                    <tr>
                                        <td valign="middle">

                                            @if($defect->selected === 'yes')
                                                @if($defect->is_pre_existing_defect == 1)
                                                    <div class="alert {{($defect->prohibitional === 'yes') ? 'note-danger' : 'note-orange'}}" style="width: 100%">
                                                        <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                        <span>&cross; Existing defect</span>
                                                    </div>
                                                @elseif($defect->is_pre_existing_defect == 0)
                                                    <div class="alert {{($defect->prohibitional === 'yes') ? 'note-danger' : 'note-orange'}}" style="width: 100%">
                                                        <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                        <span>&cross; New defect</span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="alert note-green" style="width: 100%">
                                                    <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                    <span>&check; No defect</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            @if($option->question_type == 'media' || $option->question_type == 'media_based_on_selection' || $option->question_type == 'dropdown' || $option->question_type == 'multiinput')
                                <tr>
                                    <td valign="middle">
                                        <div class="alert note-green">&check; No defect</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    @endif
                    @endforeach
                @elseif ($screen->_type === 'multiselect') 
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">{{ $screen->title }}</div>
                    </div>
                    <table class="table table-striped">
                        <tbody>                                                     
                            @foreach ($screen->options->optionList as $defect)
                            <tr>
                                    <td valign="middle"><div class="alert alert-success  note-green">&check; {{ $defect->text }}</div></td>
                            </tr>
                            @endforeach                           
                        </tbody>
                    </table>
                @elseif ($screen->_type === 'yesno')
                    @if(!isset($screen->related_to) || $screen->related_to !== 'trailer' || ($screen->related_to === 'trailer' && $check->is_trailer_attached == 1)) 
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">{{ $screen->title }}</div>
                    </div>
                    <table class="table table-striped">
                        <tbody>
                            @foreach ($screen->defects->defect as $defect)
                            <tr>
                                <td width="100%">
                                    @if(isset($screen->answer) && $screen->answer === 'not_applicable')
                                        <div class="alert note-green" style="width: 100%">
                                            <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                            <span>Not applicable</span>
                                        </div>
                                    @elseif(isset($screen->answer) && $screen->answer !== 'not_applicable' && $defect->selected === 'yes')
                                        @if($defect->is_pre_existing_defect == 1)
                                            <div class="alert {{($defect->prohibitional === 'yes') ? 'note-danger' : 'note-orange'}}" style="width: 100%">
                                                <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                <span>&cross; Existing defect</span>
                                            </div>
                                        @elseif($defect->is_pre_existing_defect == 0)
                                            <div class="alert {{($defect->prohibitional === 'yes') ? 'note-danger' : 'note-orange'}}" style="width: 100%">
                                                <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                <span>&cross; New defect</span>
                                            </div>
                                        @elseif($defect->prohibitional === 'no')
                                            <div class="alert note-orange" style="width: 100%">
                                                <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                                <span>&cross; Defect</span>
                                            </div>
                                        @endif
                                    @elseif(isset($screen->answer) && $screen->answer !== 'not_applicable' && $defect->selected === 'no')
                                        <div class="alert note-green" style="width: 100%">
                                            <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                            <span>&check; No defect</span>
                                        </div>
                                    @endif 
                                </td>
                            </tr>
                            @endforeach                          
                        </tbody>
                    </table>
                    @endif
                @elseif ($screen->_type === 'media' || $screen->_type === 'media_based_on_selection' || $screen->_type === 'dropdown' || $screen->_type === 'multiinput')
                    @if(!isset($screen->related_to) || $screen->related_to !== 'trailer' || ($screen->related_to === 'trailer' && $check->is_trailer_attached == 1))
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">{{ $screen->title }}</div>
                    </div>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td width="100%">
                                    <div class="alert note-green" style="width: 100%">
                                        <span style="width:40%; display: inline-block;">{{ $defect->text }}</span>
                                        <span>&check; No defect</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                @elseif ($screen->_type === 'declaration')
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">{{ $screen->screen_title }} ({{ $screen->title }})</div>
                    </div>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td width="100%">
                                    <div class="alert note-green" style="width: 100%">
                                        <span style="width:40%; display: inline-block;">{{ $screen->title }}</span>
                                        <span>&check; Confirmed</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @endforeach
        </div>
    </div>
@endsection