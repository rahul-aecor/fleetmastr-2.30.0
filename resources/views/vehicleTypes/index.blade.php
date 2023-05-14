@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="row portlet-search">
        <div class="col-md-12">
            <div class="clearfix">
                <form class="form" id="defects-filter-form">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="d-flex">
                               <div class="flex-grow-1 margin-right-15 search_for_distance_range" style="width: calc(100% - 109px);">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="profiletype" id='profileType' placeholder="Search by vehicle profile type">
                                    </div>
                                </div>
                                <div style="flex-shrink: 0">
                                    <div class="form-group margin-bottom0">
                                        <div class="d-flex mb-0">
                                            <button class="btn red-rubine btn-h-45" type="submit" id="searchType">
                                                    <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn btn-success grey-gallery grid-clear-btn btn-h-45">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>                                      
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box">
                <div class="portlet-title">
                    <div class="caption">
                        <div>Vehicle Profiles</div>
                        <div class="actions">
                            <input type="hidden" id="show_archived_flag" value="1"/>
                            <label class="control-label mb-0" for="show_archived_vehicles_profiels">
                                <input type="checkbox" id="show_archived_vehicles_profiels" name="show_archived_vehicles_profiels">
                                <span class="vabottom">Show archived vehicle profiles</span>
                            </label>
                        </div>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        <a href="{{ route('profiles.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add new vehicle profile</a>
                    </div>
                </div>
                
                <div class="portlet-body work_table">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="table-striped table-bordered table-hover"
                         data-type="vehicleProfile"></table>
                        <div id="jqGridPager" class="multiple-action"></div>
                    </div>   
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to edit record starts here -->
    <div class="modal fade" id="type-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Edit
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-content" id="ajax-modal-content">
                    <!-- this content wil be loaded by ajax -->
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
@endpush