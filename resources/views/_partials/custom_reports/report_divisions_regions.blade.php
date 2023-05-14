@if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
    <div class="form-group row">
        <label class="col-md-4 control-label pr-0 js-select-division-region" for="accessible_regions[]">{{ $labelTitle }}</label>
        <div class="col-md-8">
            <div class="checkbox-list">
                <label>
                    <input type="checkbox" name="all_divisions" class="js-all-divisions">All divisions
                </label>
            </div>
        </div>

        <div class="report-section-accordion js-division-linked-with-region accessible-regions-checkbox-wrapper {{ $type == 'edit' ? 'col-md-8 col-md-offset-4' : 'col-md-12 margin-top-10' }} ">
            <div class="panel-body scroller padding0" data-height="175px">
                @foreach ($allVehicleDivisionsList as $division => $regions)
                    <div id="accordion{{ $division }}" class="panel-group accordion all_division_list">
                        <div class="panel panel-default">
                            <div class="panel-heading bg-red-rubine">
                                <h4 class="panel-title">
                                    <div class="checkbox-list">
                                        <label>
                                            <input type="checkbox" name="accessible_divisions[]" class="accessible-divisions-checkbox divisions-group  division-{{ $division }}" value="{{ $division }}">
                                        </label>
                                    </div>
                                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion{{ $division }}" href="#nested-regions{{ $division }}">
                                       {{ $vehicleDivisions[$division] }} (select all regions)
                                       <span class="float-right margin-right-10">Expand</span>
                                    </a>
                                </h4>
                            </div>
                            <div id="nested-regions{{ $division }}" class="panel-collapse collapse">
                                <div class="row margin-top-10">
                                    <div class="nested-regions">
                                        <label class="margin-bottom-5">
                                            <input type="checkbox" class="all_division_region" value="{{ $division }}" disabled="">
                                            All
                                        </label>

                                        <div class="row marginbottom0">
                                            @foreach(array_chunk($regions, 2, true) as $chunk)
                                                @foreach($chunk as $region_id => $region_name)
                                                    <div class="col-md-12">
                                                        <div class="all_regions margin-bottom-5">
                                                            <label>
                                                                <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox-{{ $division }} regions-group" value="{{ $region_id }}" disabled="disabled" {{ $selectedRegions && in_array($region_id, $selectedRegions) ? 'checked' : '' }}>
                                                                {{ $region_name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <!-- #6284
    <div class="form-group row margin-bottom-10">
        <label class="col-md-4 control-label pr-0 js-select-division-region" for="accessible_regions[]">{{ $labelTitle }}</label>
        <div class="col-md-8 report-section-accordion">
            <div class="row">
                <div class="col-md-12 accessible-regions-checkbox-wrapper">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="checkbox-list">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>
                                            <input type="checkbox" id="all_accessible_region" value=""  {{ $selectedRegions && count($selectedRegions) == count($allVehicleDivisionsList) ? 'checked' : '' }}>
                                            All
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach(array_chunk($allVehicleDivisionsList, 2, true) as $chunk)
                                        @foreach($chunk as $division => $regions)
                                            <div class="col-md-6">
                                                <div class="all_regions">
                                                    <label>
                                                        <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $division }}" {{ $selectedRegions && in_array($division, $selectedRegions) ? 'checked' : '' }}>
                                                        {{ $regions }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 accessible-regions-checkbox-wrapper-error"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="form-group row">
        <label class="col-md-4 control-label pr-0 js-select-division-region" for="accessible_regions[]">{{ $labelTitle }}</label>
        <div class="col-md-8 padding-bottom-5">
            <div class="checkbox-list">
                <label>
                    <input type="checkbox" id="all_accessible_region" value=""  {{ $selectedRegions && count($selectedRegions) == count($allVehicleDivisionsList) ? 'checked' : '' }}>All regions
                </label>
            </div>
        </div>

        <div class="report-section-accordion js-division-linked-with-region accessible-regions-checkbox-wrapper {{ $type == 'edit' ? 'col-md-8 col-md-offset-4' : 'col-md-12 margin-top-10' }} ">
            <div class="panel-body scroller padding0" data-height="175px">
                <?php $division = 'all';?>
                <div id="accordion{{ $division }}" class="panel-group accordion all_division_list">
                    <div class="panel panel-default">
                        <div class="panel-heading bg-red-rubine">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion{{ $division }}" href="#nested-regions{{ $division }}">
                                   Select regions
                                   <span class="float-right margin-right-10">Expand</span>
                                </a>
                            </h4>
                        </div>
                        <div id="nested-regions{{ $division }}" class="panel-collapse collapse">
                            <div class="row margin-top-10">
                                <div class="nested-regions">
                                    <div class="row marginbottom0">
                                        @foreach(array_chunk($allVehicleDivisionsList, 2, true) as $chunk)
                                            @foreach($chunk as $division => $regions)
                                                <div class="col-md-12">
                                                    <div class="all_regions margin-bottom-5">
                                                        <label>
                                                            <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $division }}" {{ $selectedRegions && in_array($division, $selectedRegions) ? 'checked' : '' }}>
                                                            {{ $regions }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif