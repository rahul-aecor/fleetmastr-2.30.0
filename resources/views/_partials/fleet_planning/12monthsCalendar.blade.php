<div class="fc-toolbar calendar-year-view js-calendar-year-view" style="display: none">
    <div class="fc-center">
        <div class="fc-button-group" style="display: inline">
            <button type="button" id="prevYear" class="fc-prev-button fc-button fc-state-default fc-corner-left"><span class="fc-icon fc-icon-left-single-arrow"></span></button>
            <button type="button" id="nextYear" class="fc-next-button fc-button fc-state-default fc-corner-right"><span class="fc-icon fc-icon-right-single-arrow"></span></button>
        </div>
        <h2 style="display: inline-block;" id="selectedYear">{{$dates['year']}}</h2>
    </div>
    <div class="fc-clear"></div>
</div>
<div class="js-calendar-year-view" style="display: none;">
    <div class="calendar-wrapper">
        @foreach($dates['months'] as $monthNumber => $monthName)
            <div class="calendar-section">
                <div class="calendar-item">
                    <div class="calendar-month-name">{{$monthName}}</div>
                    <div role="grid" aria-readonly="true" aria-label="January"
                         class="calendar-month-view">
                        <!--         <div class="calendar-month-name">January</div> -->
                        <div class="calendar-cell day">s</div>
                        <div class="calendar-cell day">m</div>
                        <div class="calendar-cell day">t</div>
                        <div class="calendar-cell day">w</div>
                        <div class="calendar-cell day">t</div>
                        <div class="calendar-cell day">f</div>
                        <div class="calendar-cell day">s</div>

                        @foreach($dates['dates'][$monthNumber] as $date)
                            <div class="calendar-cell date">
                                <div class="date-block {{$date['class']}}" {!! $date['attribute'] !!}>{{$date['label']}}</div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>