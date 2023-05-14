<div class="tab-pane" id="planner">
    <div class="portlet box marginbottom0 planner-detail-card">
        <div class="portlet-body pt-0">
            <div class="row">
                <div class="col-md-6 planner--section-calender">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="clearfix">
                                <form class="form planner-form row">
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-plain btn-block btn-h-45" id="today">Today</button>
                                    </div>
                                    <div class="col-md-4">

                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control select2me" id="month_year_selector"
                                            name="month_year_selector">
                                            <option value="month" selected>Month</option>
                                            <option value="year">Year</option>
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-7 col-md-12 col-sm-12 planner--section-calender-dropdown">
                                        <div class="form-group">
                                            {!! Form::select('event', $eventsForFilter, null, ['id' => 'event', 'class' => 'form-control select2me js-event-filter', 'data-placeholder' => 'All events']) !!}
                                        </div>  
                                    </div> --}}
                                </form>
                            </div>
                        </div>
                        <div class="col-md-12 planner--section-calender-area">
                            <div id='calendar'></div>
                            <div id="calendar12Months">
                                @include('_partials.fleet_planning.12monthsCalendar')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 planner--section-description" id="daily-events">
                </div>
            </div>
        </div>
    </div>
</div>