@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/tinymce/tinymce.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/tinymce/jquery.tinymce.min.js') }}" type="text/javascript"></script>    
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script> 
    <script src="{{ elixir('js/messages-jqgrid.js') }}" type="text/javascript"></script>   
    <script src="{{ elixir('js/dropzone.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/flow/flow.js') }}" type="text/javascript"></script>
@endsection

@section('content')
<div id="messages-page">
    {{-- management section --}}
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#tab_1" data-toggle="tab">
                Send Message </a>
            </li>
            <li>
                <a href="#tab_2" data-toggle="tab">
                History </a>
            </li>
            <li>
                <a href="#tab_3" data-toggle="tab" aria-expanded="true">
                Manage Groups </a>
            </li>
            <li>
                <a href="#tab_4" data-toggle="tab">
                Manage Templates </a>
            </li>
        </ul>
        <div class="tab-content rl-padding">
            <div class="tab-pane active" id="tab_1">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label class="control-label col-md-12">Create and send a message. You can incorporate any groups and/or templates created on the other tabs.</label>
                    </div>
                </div>
                <div class="col-md-12"> 
                    <messagesform :templates="templates" :groups="groups" :contacts="contacts" :site-contacts="siteContacts" :userdivisions="userdivisions"></messagesform>
                </div>
            </div>
            <div class="tab-pane" id="tab_2">
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet box">
                            <div class="portlet-title">
                                <div class="caption">Statistics</div>
                            </div>
                            <div class="portlet-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-sm-2 col-xs-4 text-stat" style="background: #f2f2f2;">
                                            <h4>Today</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['today'] }}</h3>
                                        </div>
                                        <div class="col-sm-2 col-xs-4 text-stat">
                                            <h4>Yesterday</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['yesterday'] }}</h3>
                                        </div>
                                        <div class="col-sm-2 col-xs-4 text-stat">
                                            <h4>This Month</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['this_month'] }}</h3>
                                        </div>
                                        <div class="col-sm-2 col-xs-4 text-stat">
                                            <h4>Last Month</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['last_month'] }}</h3>
                                        </div>
                                        <div class="col-sm-2 col-xs-4 text-stat">
                                            <h4>This Year</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['this_year'] }}</h3>
                                        </div>
                                        <div class="col-sm-2 col-xs-4 text-stat">
                                            <h4>All Time</h4>
                                            <h3 style="font-weight: 400;">{{ $messagesCount['all_time'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet box">
                            <div class="portlet-title">
                                <div class="caption">
                                    History
                                </div>
                            </div>
                            <div class="portlet-body work_table timesheet">
                                {{-- messages table component --}}
                                <table id="jqGrid" class="table-striped table-hover"></table>
                                <div id="jqGridPager"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab_3">
                <div class="row"> 
                    <div class="form-group col-md-12">
                        <label class="control-label col-md-12">Create a new group or choose an existing group to view/edit. Division groups are automatically created and available to select when sending a message.</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <groupform :contacts="contacts" :site-contacts="siteContacts" :groups="groups"></groupform>
                </div>
            </div>
            <div class="tab-pane" id="tab_4">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label class="control-label col-md-12">Use templates to create a new message and select recipients to receive the communication.</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <templateform :contacts="contacts" :site-contacts="siteContacts" :groups="groups" :templates="templates" :userdivisions="userdivisions"></templateform>
                    <!-- <templateform :contacts="contacts" :groups="groups" :templates="templates"></templateform> -->
                </div>
            </div>
        </div>
    </div>
    
    {{-- new message modal --}}
    <div class="modal modal-scroll fade" id="portlet-config1" tabindex="-1" data-width="1050" data-background="static">    
        <messageform :templates="templates" :groups="groups" :contacts="contacts" :site-contacts="siteContacts"></messageform>
        <!-- <messageform :templates="templates" :groups="groups" :contacts="contacts"></messageform> -->
    </div>    
    {{-- message details modal --}}
    
    {{-- message details modal start --}}
    <div id="message-details-modal" class="modal fade js-message-details-modal" data-width="1024" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Report Details</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
            </a>
        </div>
        <div class="modal-body" id="ajax-modal-content">
            <h4><i class="jv-icon jv-reload fa-spin"></i></h4>
        </div>
    </div>
    <div id="message-modal" class="modal fade js-message-modal" data-width="800" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
           <h4 class="modal-title">Message Details</h4>
           <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
            </a>
        </div>
        <div class="modal-body" id="ajax-modal-content">
            <h4><i class="jv-icon jv-reload fa-spin"></i></h4>
        </div>
    </div>
    {{-- message details modal end --}}
</div>
@endsection

@push('scripts')
    <script src="{{ elixir('js/tinymce_acknowledgement_plugin.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bundles/training.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/message-custom.js') }}" type="text/javascript"></script>
@endpush