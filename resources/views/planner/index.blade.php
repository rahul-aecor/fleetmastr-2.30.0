
@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/fullcalendar.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/fullcalendar.min.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <!-- <div class="row">
        <div class="col-md-12">
            <div class="clearfix">
                <form class="form planner-form">
                    <div class="col-lg-3 col-md-12 col-sm-12 planner--section-calender-dropdown">
                        <div class="form-group">
                        {!! Form::select('event', $eventsForFilter, null, ['id' => 'event', 'class' => 'form-control select2me js-event-filter', 'data-placeholder' => 'All events']) !!}
                        </div>  
                    </div>
                </form>
            </div>
        </div>
    </div> -->
    {{-- <div class="row">
        <div class="col-md-12">
            <div class="clearfix">
                <div class="col-md-3 planner--section-calender-dropdown">
                    <div class="form-group">
                        {!! Form::select('event', $eventsForFilter, null, ['id' => 'event', 'class' => 'form-control select2me js-event-filter', 'data-placeholder' => 'All events']) !!}
                    </div>      
                </div>
            </div>
        </div>
    </div> --}}
    <div class="row planner--section">
        <div class="col-md-12">
            <div class="portlet box marginbottom0 planner-detail-card">
                <div class="portlet-title">
                    <div class="caption" style="min-width: 350px;">
                        Planner
                    </div>
                    <div class="actions new_btn">
                        
                    </div>  
                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-6 planner--section-calender">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="clearfix">
                                        <form class="form planner-form">
                                            <div class="col-lg-7 col-md-12 col-sm-12 planner--section-calender-dropdown">
                                                <div class="form-group">
                                                    {!! Form::select('event', $eventsForFilter, null, ['id' => 'event', 'class' => 'form-control select2me js-event-filter', 'data-placeholder' => 'All events']) !!}
                                                </div>  
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 planner--section-calender-area">
                                    <div id='calendar'></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 planner--section-description" id="daily-events">
                        </div>
                    </div> 
                </div>
            </div>
        </div>
        
    </div>    
 
@endsection

@push('scripts')
    <script src="{{ elixir('js/planner.js') }}" type="text/javascript"></script>
@endpush