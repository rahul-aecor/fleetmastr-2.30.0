@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/inputs-ext/address/address.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui.css') }}" rel="stylesheet" type="text/css"/>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-noscript.css') }}"></noscript>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui-noscript.css') }}"></noscript>

    <style>
        #view-events .modal-body .form-group {
            margin-bottom: 0px !important;
        }
        /*.select2-chosen{
            margin-right:62px !important;
        }*/
    </style>

@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-file-upload/jquery.ui.widget.js') }}" type="text/javascript"></script>
    <!-- The Templates plugin is included to render the upload/download listings -->
    <script src="{{ elixir('js/jquery-file-upload/tmpl.min.js') }}" type="text/javascript"></script>
    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/load-image.min.js') }}" type="text/javascript"></script>
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/canvas-to-blob.min.js') }}" type="text/javascript"></script>
    <!-- blueimp Gallery script -->
    <script src="{{ elixir('js/blueimp-gallery/jquery.blueimp-gallery.min.js') }}" type="text/javascript"></script>
    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.iframe-transport.js') }}" type="text/javascript"></script>
    <!-- The basic File Upload plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload.js') }}" type="text/javascript"></script>
    <!-- The File Upload processing plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-process.js') }}" type="text/javascript"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-image.js') }}" type="text/javascript"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-audio.js') }}" type="text/javascript"></script>
    <!-- The File Upload video preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-video.js') }}" type="text/javascript"></script>
    <!-- The File Upload validation plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-validate.js') }}" type="text/javascript"></script>
    <!-- The File Upload user interface plugin -->
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.mockjax.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/inputs-ext/address/address.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/form-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-ui.js') }}" type="text/javascript"></script>
   {{--  <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script> --}}
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicles-add.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicle-document-upload.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicle-maintenance-history.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicle_documents.js') }}" type="text/javascript"></script>

@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="page-bar">
        <?php
            $vehicleChecksUrl = '';
            $vehicleDefectsUrl = '';
            $vehicleIncidentsUrl = '';
            if($vehicle->status == "Archived" || $vehicle->status == "Archived - De-commissioned" || $vehicle->status == "Archived - Written off" || $vehicle->status == "Archived - Sold") {
                $vehicleChecksUrl = 'vehicles/' . $vehicle->id . '/checks?vehicleDisplay='.$vehicleDisplay;
                $vehicleDefectsUrl = 'vehicles/' . $vehicle->id . '/defects?vehicleDisplay='.$vehicleDisplay;
                $vehicleIncidentsUrl = 'vehicles/' . $vehicle->id . '/incidents?vehicleDisplay='.$vehicleDisplay;
            } else {
                $vehicleChecksUrl =  'vehicles/' . $vehicle->id . '/checks';
                $vehicleDefectsUrl = 'vehicles/' . $vehicle->id . '/defects';
                $vehicleIncidentsUrl = 'vehicles/' . $vehicle->id . '/incidents';
            }
        ?>

        {!! Breadcrumbs::render('search_details',$vehicle->id) !!}
        <div class="page-toolbar">
            <a class="btn btn-plain hidden-print js-user-information-only" href="{{ url('vehicles/'.$vehicle->id.'/edit') }}" id='edit-vehicle-btn'>
                    <i class="jv-icon jv-edit"></i> Edit vehicle
            </a>
            <a class="btn btn-plain hidden-print" href="{{ url($vehicleChecksUrl) }}">
                <i class="jv-icon jv-checklist"></i> View vehicle checks
            </a>
            <a class="btn btn-plain hidden-print" href="{{ url($vehicleDefectsUrl) }}">
                <i class="jv-icon jv-error"></i> View vehicle defects
            </a>
            @if(setting('is_incident_reports_enabled') == 1)
            <a class="btn btn-plain hidden-print" href="{{ url($vehicleIncidentsUrl) }}">
                <i class="jv-icon jv-vehicle-crash"></i> View vehicle incidents
            </a>
            @endif
            @if(setting('is_fleetcost_enabled'))
                <a class="btn btn-plain hidden-print" id="view-vehicle-cost">
                    &pound; View vehicle costs
                </a>
            @endif
            <!-- <a class="btn grey-gallery hidden-print" href="{{ url('vehicles/exportPdf/' . $vehicle->id) }}">
                <i class="fa fa-print"></i> Print PDF
            </a> -->
        </div>
    </div>
    <div class="row">
        <div class="col-md-10">
            <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
                <ul class="nav nav-tabs nav-justified" id="vehicle_detail" role="tablist">
                    <li class="{{ showVehicleSelectedTab($selectedTab, "vehicle_summary") }}" href="#vehicle_summary" data-toggle="tab">
                        <a>Summary</a>
                    </li>
                    <li class="{{ showVehicleSelectedTab($selectedTab, "administration") }}" href="#administration" data-toggle="tab">
                        <a>Administration</a>
                    </li>
                    <li class="{{ showVehicleSelectedTab($selectedTab, "planning") }}" href="#planning" data-toggle="tab">
                        <a>Planning</a>
                    </li>
                    <li class="{{ showVehicleSelectedTab($selectedTab, "maintenance_tab") }}" href="#maintenance_tab" data-toggle="tab">
                        <a>Maintenance</a>
                    </li>
                    @if(setting('is_fleetcost_enabled'))
                        <li class="{{ showVehicleSelectedTab($selectedTab, "assignment") }}" href="#assignment" data-toggle="tab">
                            <a>Assignment</a>
                        </li>
                    @endif
                    <li class="{{ showVehicleSelectedTab($selectedTab, "documents") }}  documents" href="#documents" data-toggle="tab">
                        <a>Documents</a>
                    </li>
                    <li class="{{ showVehicleSelectedTab($selectedTab, "specification") }}" href="#specification" data-toggle="tab">
                        <a>Profile</a>
                    </li>
                    <li class="{{ showVehicleSelectedTab($selectedTab, "history") }}" href="#history" data-toggle="tab">
                        <a>History</a>
                    </li>
                </ul>
                <div class="tab-content" id="vehicle_detail_content">
                    <div class="vehicle-summary tab-pane {{ showVehicleSelectedTab($selectedTab, "vehicle_summary") }}" id="vehicle_summary">
                        <table class="table table-striped table-hover custom-table-striped">
                            <tbody>
                                <tr>
                                    <td>Date added to fleet:</td>
                                    <td>{{ $vehicle->dt_added_to_fleet}}</td>
                                </tr>
                                <tr>
                                    <td>Category:</td>
                                    <td>{{ $vehicle->type->present()->vehicle_category_to_display() }}</td>
                                </tr>
                                @if($vehicle->type->vehicle_category == "non-hgv")
                                    <tr>
                                        <td>Sub category:</td>
                                        <td>{{ $vehicle->type->present()->vehicle_sub_category_to_display() }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Type:</td>
                                    <td>{{ $vehicle->type->vehicle_type }}</td>
                                </tr>
                                <tr>
                                    <td>Manufacturer:</td>
                                    <td>{{ $vehicle->type->manufacturer }}</td>
                                </tr>
                                <tr>
                                    <td>Model:</td>
                                    <td>{{ $vehicle->type->model }}</td>
                                </tr>
                                <tr>
                                    <td>Odometer:</td>
                                    <td>{{ number_format($vehicle->last_odometer_reading ) }} {{ $vehicle->type->odometer_setting }}</td>
                                </tr>                                
                                <tr>
                                    <td>Vehicle status:</td>
                                    <td id="vehicle_status_select">
                                        @if(strtolower($vehicle->status) == 'archived' || strtolower($vehicle->status) == 'archived - de-commissioned' || strtolower($vehicle->status) == 'archived - written off' || strtolower($vehicle->status) == 'archived - sold')
                                            <span class="label label-default {{ $vehicle->present()->label_class_for_status }} label-results js-vehicle-status-view" style="display:none;"><strong>{{ $vehicle->status }} ({{ date("d M Y", strtotime($vehicle->archived_date)) }})</strong></span>
                                            <div class="editable-wrapper js-vehicle-status-editable">
                                                <span class="label label-default {{ $vehicle->present()->label_class_for_status }} label-results js-vehicle-status-view">
                                                <span class="js-vehicle-status-edit cursor-pointer" data-type="date" data-viewformat="dd M yyyy" data-pk="{{ $vehicle->id }}" data-value="{{ $vehicle->archived_date }}" data-placement="right">{{ $vehicle->status }}{{ $vehicle->archived_date ? ' ('.date("d M Y", strtotime($vehicle->archived_date)).')' : '' }}</span>
                                                </span>
                                            </div>
                                        @else
                                            <span class="label label-default {{ $vehicle->present()->label_class_for_status }} label-results">
                                            {{ $vehicle->status }} @if(starts_with($vehicle->status, 'VOR') && $vorDuration) ({{ $vorDuration }}) @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                @if (!empty($vehicle->dt_vehicle_disposed))
                                    <tr>
                                        <td>Vehicle disposed:</td>
                                        <td>{{ $vehicle->dt_vehicle_disposed }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Usage:</td>
                                    <td>{{ $vehicle->usage_type ? $vehicle->usage_type : ($vehicle->type->usage_type ? $vehicle->type->usage_type : '')}}</td>
                                </tr>
                                <tr>
                                    <td>Ownership status:</td>
                                    <td>{{ $vehicle->staus_owned_leased }}</td>
                                </tr>
                                <tr>
                                    <td>Telematics:</td>
                                    <td>{{ ($vehicle->is_telematics_enabled == 1) ? "Yes":"No" }}</td>
                                </tr>
                                @if ($vehicle->is_telematics_enabled == 1)
                                    <tr>
                                        <td>Supplier:</td>
                                        <td class="text-capitalize">{{ ($vehicle->supplier) ? $vehicle->supplier:'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Device:</td>
                                        <td>{{ ($vehicle->device) ? $vehicle->device:'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Serial ID:</td>
                                        <td>{{ ($vehicle->serial_id) ? $vehicle->serial_id:'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Installation date:</td>
                                        <td>{{ ($installationDate) ? $installationDate:'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Last data update::</td>
                                        <td>{{ $lastDateUpdateDevice }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Created by:</td>
                                    <!-- <td>{{ $vehicle->creator->email }}</td> -->
                                    <td>{{ $vehicle->creator->first_name }} {{ $vehicle->creator->last_name }} (<a href="mailto:{{$vehicle->creator->email}}" class="font-blue">{{ $vehicle->creator->email }}</a>)</td>
                                </tr>
                                <tr>
                                    <td>Created date:</td>
                                    <td>{{ $vehicle->present()->formattedCreatedAt() }}</td>
                                </tr>
                                <tr>
                                    <td>Last modified by:</td>
                                    <td>{{ $vehicle->updater->first_name or '' }} {{ $vehicle->updater->last_name or '' }} (<a href="mailto:{{$vehicle->updater->email or '' }}" class="font-blue">{{ $vehicle->updater->email or '' }}</a>)</td>
                                </tr>
                                <tr>
                                    <td>Last modified date:</td>
                                    <td>{{ $vehicle->present()->formattedUpdatedAt() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="vehicle-summary tab-pane {{ showVehicleSelectedTab($selectedTab, "administration") }}" id="administration">
                        <table class="table table-striped table-hover custom-table-striped">
                            <tbody>
                                <tr>
                                    <td>Nominated driver:</td>
                                    <td>
                                    @if (!empty($vehicle->nominatedDriver->email))
                                        {{ $vehicle->nominatedDriver->first_name }}&nbsp;{{ $vehicle->nominatedDriver->last_name }}
                                    @else
                                        &nbsp;
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Private use during current tax year:</td>
                                    <td>{{ $privateUseDays }}&nbsp;days</td>
                                </tr>
                                <tr>
                                    <td>Registration date:</td>
                                    <td>{{ $vehicle->dt_registration }}</td>
                                </tr>
                                <tr>
                                    <td>First use inspection date:</td>
                                    @if (!empty($vehicle->dt_first_use_inspection))
                                        <td>{{ $vehicle->dt_first_use_inspection }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                </tr>
                                @if($vehicle->staus_owned_leased == "Leased")
                                    <tr>
                                        <td>Vehicle lease expiry date:</td>
                                        @if (!empty($vehicle->lease_expiry_date))
                                            <td>{{ $vehicle->lease_expiry_date }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    </tr>
                                @endif
                                <tr>
                                    <td>P11D list price or benefit charge:</td>
                                    <td>
                                        @if($vehicle->P11D_list_price != null)
                                            &pound; {{ (floor($vehicle->P11D_list_price) == $vehicle->P11D_list_price) ? number_format($vehicle->P11D_list_price, 0) : number_format($vehicle->P11D_list_price, 2) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Operator license:</td>
                                    <td>{{ $vehicle->operator_license }}</td>
                                </tr>
                                <tr>
                                    <td>Chassis number:</td>
                                    <td>{{ $vehicle->chassis_number }}</td>
                                </tr>
                                <tr>
                                    <td>Contract ID:</td>
                                    <td>{{ $vehicle->contract_id }}</td>
                                </tr>
                                <tr>
                                    <td>Notes:</td>
                                    <td>{{ $vehicle->notes }}</td>
                                </tr>
                                <tr>
                                    <td>Vehicle division:</td>
                                    <td>{{ $vehicle->division->name }}</td>
                                </tr>
                                <tr>
                                    <td>Vehicle region:</td>
                                    <td>{{ $vehicle->region->name }}</td>
                                </tr>
                                <tr>
                                    <td>Vehicle location:</td>
                                    @if (!empty($vehicle->location->name))
                                        <td>{{ $vehicle->location->name }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "planning") }}" id="planning">
                        <table class="table custom-table-striped table-maintenance-location">
                            <tbody>
                                <tr>
                                    <td>Repair/Maintenance location:
                                    @if (!empty($vehicle->repair_location->name))
                                        <span class="margin_left">{{ $vehicle->repair_location->name }}</span>
                                    @endif
                                    </td>
                                    <td>
                                        <a href="javasript:void(0)" class="btn btn-plain btn-sm title_right_btn float-right 12_month_schedule"><i class="jv-icon jv-calendar"></i>&nbsp; 12 month schedule</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @include('_partials.vehicles.show_planning_table')
                        <div class="row">
                            <div>
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <div>Add New Comment</div>
                                        </div>
                                    </div>
                                    <div class="portlet-body pt15" id="planning-comments-drag">
                                    {!! BootForm::openHorizontal(['md' => [3, 8]])->id('saveCommentForVehiclePlanning')->addClass('form-bordered form-validation')->action('/vehicles/storeComment')->multipart() !!}
                                        <div class="alert alert-danger display-hide  bg-red-rubine">
                                            <button class="close" data-close="alert"></button>
                                            <!-- You have some form errors. Please check below. -->
                                            Please complete the errors highlighted below.
                                        </div>
                                        <div class="input-cont form-group planning-comment-block">
                                            <textarea name="comments" rows="4" class="form-control vehicle-planning-tab-textarea" placeholder="Enter comments here" autocomplete="off"></textarea>
                                        </div>
                                        <div class="fileupload-buttonbar">
                                            <div class="dropZoneElement">
                                                <div class="fileinput-button">
                                                    <div>
                                                        <p class="fileinput-button-title"><span>+</span>Add file</p>
                                                        <p class="dropImageHereText">Click or drop your file here to upload</p>
                                                        <input type="file" name="attachment" class="select-file-vehicle-planning">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <div class="help-block">(*.gif, .jpg, .jpeg, .png, .doc, .docx, .xls, .xlsx, .csv, *.pdf)</div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-md-5">
                                                <input type="text" name="file_input_name" id="name" class="form-control fileupload js-files-name" placeholder="Enter file name here">
                                            </div>
                                            <div class="col-md-7" id="vehiclePlanningDisplay">
                                                <div class="row d-flex align-items-center">
                                                    <div class="col-md-12">
                                                        <span class="btn red-rubine btn-file js-new-attachment-file">
                                                            <span class="fileinput-new">Select file</span>
                                                        </span>
                                                        <button class="fileinput-exists btn grey-gallery remove-file-vehicle-planning" style="display: none;" data-dismiss="fileinput">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" style="">
                                          <div class="col-md-12 planning_photo_display" style="display: none;">
                                            <iframe id="planning_document" height="500" frameborder="0" style="overflow: scroll; width: 100%; position: relative;display:none;margin-bottom:20px"></iframe>
                                            <img id="planning_photo" src="#" alt="image" style="width:80px;height:46px;display:none;margin-bottom:20px">
                                          </div>
                                        </div>

                                        <input type="hidden" id="vehicle_id" name="vehicle_id" value="{{ $vehicle->id }}" />
                                        <div class="btn-cont" id="vehicle-comment-button">
                                            <input type="submit" class="btn icn-only red-rubine" value="Save" id="saveComment">
                                        </div>
                                    {!! BootForm::close() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="portlet-planning" class="modal fade" tabindex="-1" data-width="800" data-background="static">
                          <div class="portlet-title">
                            <div class="caption">
                              <div class="planning_history">Planning History</div>
                            </div>
                          </div>
                          <div class="portlet-body">
                            <div class="timeline defect-comments-timeline js-defect-comments">
                              @foreach ($comments as $comment)
                                <div class="timeline-item">
                                  <div class="timeline-badge">
                                    <div class="timeline-icon">
                                      <i class="icon-bubbles font-red-rubine"></i>
                                    </div>
                                  </div>
                                  <div class="timeline-body">
                                    <div class="timeline-body-arrow">
                                    </div>
                                    <div class="timeline-body-head">
                                      <div class="timeline-body-head-caption">
                                        {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="bold timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                                      </div>
                                    </br>
                                    <div class="timeline-body-head-actions">
                                      <div class="">
                                        @if ($comment->user_id == Auth::id())
                                          <button type="button" class="btn red-rubine js-edit-comment-btn btn-height" style=""><i class="jv-icon jv-edit"></i></button>
                                          <button type="button" data-delete-url="/vehicles/delete_comment/{{ $comment->id }}"
                                            class="btn delete-button grey-gallery btn-height ml0"
                                            title="Delete comment"
                                            data-confirm-msg="Are you sure you would like to delete this comment?">
                                            <i class="jv-icon jv-dustbin"></i>
                                          </button>
                                        @endif
                                      </div>
                                    </div>
                                    <div class="timeline-body-content">
                                      @if($comment->comment)
                                        @if ($comment->user_id == Auth::id())
                                          <span class="">
                                            <a href="javascript:;" class="comments" data-type="textarea" data-pk="{{ $comment->id }}" data-original-title="Update comment">{{ $comment->comment }}</a>
                                          </span>
                                        @else
                                          <span class=""><br/>{!! nl2br($comment->comment) !!}</span>
                                        @endif
                                      @endif
                                    </div>
                                    <div class="timeline-body-content">
                                      <div class="row">
                                        <?php $totalImages=count($comment->getMedia()) ?>
                                        @foreach ($comment->getMedia() as $index=>$media)

                                          @if($index % 3 === 0)
                                            <div class="col-md-12 margin-bottom-25">
                                              <div class="row">
                                          @endif
                                            <div class="col-md-4">
                                              <div>
                                                <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                                                <a href="{{ url('/vehicles/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                                              </div>

                                              <div class="margin-top-10">
                                                <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-defect" style="display: inline-block;">
                                                  @if (strpos($media->getCustomProperty('mime-type'), 'image/') === 0)
                                                  <img class="img-rounded" style="max-width: 120px; max-height: 120px;" src="{{ asset(getPresignedUrl($media)) }}" alt="">
                                                  @endif
                                                </a>
                                              </div>
                                            </div>
                                            @if($index%3 === 2 || $totalImages === ($index + 1))
                                                </div>
                                              </div>
                                            @endif
                                          @endforeach
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                    </div>

                    <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "maintenance_tab") }}" id="maintenance_tab">
                        <div class="row">
                            <div class="col-md-12">
                                <form id="vehicle_maintenance_history_search_form">
                                    <input type="hidden" name="filter_vehicle_id" value="{{ $vehicle->id }}">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                {!! Form::select('search_maintenance_event_type', $maintenanceEventTypesForSearchOption, null, ['id' => 'search_maintenance_event_type', 'class' => 'form-control select2-maintenance-event-type reset-clear', 'data-placeholder' => 'All events']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                {!! Form::text('search_maintenance_event_date', null, ['id' => 'search_maintenance_event_date', 'class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Report date', 'readonly' => 'readonly']) !!}
                                                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-0">
                                            <button class="btn btn-h-45 red-rubine" type="submit">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn js-vehicle-maintenance-clear-btn btn-h-45 grey-gallery justify-content-center" style="margin-right: 0">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row js-search-error-msg" style="display: none">
                                    <div class="col-md-12">
                                        <div class="form-group has-error">
                                          <span class="help-block help-block-error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box marginbottom0">
                                    <div class="portlet-title bg-red-rubine display_block">
                                        <div class="actions new_btn align-self-end">
                                            <a href="javascript:void(0)" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn add_new_maintenance_history_modal"><i class="jv-icon jv-plus"></i> Add new entry</a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="jqgrid-wrapper">
                                            <table id="jqGrid" class="table-striped table-bordered table-hover" data-type="vehicles"></table>
                                            <div id="jqGridPager" class="multiple-action"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "assignment") }}" id="assignment">
                        <div class="row">
                            <div class="col-md-12">
                                <form id="vehicle_assignment_search_form">
                                    <input type="hidden" name="filter_assignment_vehicle_id" value="{{ $vehicle->id }}">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                {!! Form::text('search_assignment_event_date', null, ['id' => 'search_assignment_event_date', 'class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Report date', 'readonly' => 'readonly']) !!}
                                                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-0">
                                            <button class="btn btn-h-45 red-rubine" type="submit">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn js-vehicle-assignment-clear-btn btn-h-45 grey-gallery justify-content-center" style="margin-right: 0">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row js-assignment-search-error-msg" style="display: none">
                                    <div class="col-md-12">
                                        <div class="form-group has-error">
                                          <span class="help-block help-block-error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box marginbottom0">
                                    <div class="portlet-title bg-red-rubine display_block">
                                        <div class="actions new_btn align-self-end">
                                            {{-- <a href="{{route('vehicles.edit',$vehicle->id)}}#vehicle-assignment" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add assignment</a> --}}

                                            <a title="Add assignment" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn" data-target="#add_new_assignment" href="#add_new_assignment" data-toggle="modal"><i class="jv-icon jv-plus"></i> Add assignment</a>

                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="jqgrid-wrapper">
                                            <table id="assignmentjqGrid" class="table-striped table-bordered table-hover" data-type="vehicles"></table>
                                            <div id="assignmentjqGridPager" class="multiple-action jqGridPager"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "documents") }}" id="documents">
                        <div class="row">
                            <div class="col-md-12">
                                <form id="vehicle_document_search_form">
                                    <input type="hidden" name="filter_vehicle_id" value="{{ $vehicle->id }}">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-md-3">
                                            {!! Form::text('documentNameInput', null, ['class' => 'form-control data-filter', 'placeholder' => 'Search document', 'id' => 'documentNameInput']) !!}
                                            {{-- <input type="text" class="form-control" name="documentNameInput" id="documentNameInput" placeholder="Search Document"> --}}
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                {!! Form::select('search_documents', ['All'=>'All','Documents'=>'Documents','Maintenance'=>'Maintenance'], null, ['id' => 'search_documents', 'class' => 'form-control select2-document reset-clear', 'data-placeholder' => 'All']) !!}
                                            </div>
                                        </div>
                                        <div class="d-flex mb-0">
                                            <button class="btn btn-h-45 red-rubine" type="button" id="searchDocumentBtn">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn js-vehicle-documents-clear-btn btn-h-45 grey-gallery justify-content-center" style="margin-right: 0">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box marginbottom0">
                                    <div class="portlet-title bg-red-rubine display_block">
                                        <div class="actions new_btn align-self-end">
                                            <a href="#" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn add_new_vehicle_document_modal"><i class="jv-icon jv-plus"></i> Add new document</a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="jqgrid-wrapper">
                                            <table id="documentsJqGrid" class="table-striped table-bordered table-hover"></table>
                                            <div id="documentsJqGridPager" class="multiple-action jqGridPagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vehicle-summary tab-pane {{ showVehicleSelectedTab($selectedTab, "specification") }}" id="specification">
                        <table class="table table-striped table-hover custom-table-striped">
                            <tbody>
                                <tr>
                                    <td>Fuel type:</td>
                                    <td>{{ $vehicle->type->fuel_type }}</td>
                                </tr>
                                <tr>
                                    <td>Type of engine:</td>
                                    <td>
                                        {{ $vehicle->type->engine_type }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Oil grade:</td>
                                    <td>
                                        {{ $vehicle->type->oil_grade }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>CO2:</td>
                                    <td>
                                     {{ $vehicle->CO2 ? $vehicle->CO2 . ' ' . config('config-variables.co2Unit') : ($vehicle->type->co2 ? $vehicle->type->co2 . ' ' . config('config-variables.co2Unit') : '')}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Monthly vehicle tax:</td>
                                    <td>
                                        @if($vehicleTaxValue != 0)
                                            &pound; {{ $vehicleTaxValue }}
                                        @endif
                                    </td>
                                </tr>                                
                                <tr>
                                    <td>Tyre size drive:</td>
                                    <td>{{ $vehicle->type->tyre_size_drive }}</td>
                                </tr>
                                <tr>
                                    <td>Tyre size steer:</td>
                                    <td>{{ $vehicle->type->tyre_size_steer }}</td>
                                </tr>
                                <tr>
                                    <td>Nut size:</td>
                                    <td>{{ $vehicle->type->nut_size }}</td>
                                </tr>
                                <tr>
                                    <td>Re-torque:</td>
                                    <td>{{ $vehicle->type->re_torque }}</td>
                                </tr>
                                <tr>
                                    <td>Tyre pressure drive:</td>
                                    <td>{{ $vehicle->type->tyre_pressure_drive }}</td>
                                </tr>
                                <tr>
                                    <td>Tyre pressure steer:</td>
                                    <td>{{ $vehicle->type->tyre_pressure_steer }}</td>
                                </tr>
                                <tr>
                                    <td>Bodybuilder:</td>
                                    <td>{{ $vehicle->type->body_builder }}</td>
                                </tr>
                                <tr>
                                    <td>Dimensions (mm):</td>
                                    <td>
                                        @if( !empty($vehicle->type->present()->displayDimensions()))
                                            {{ $vehicle->type->present()->displayDimensions() }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Gross vehicle weight:</td>
                                    <td>
                                    @if($vehicle->type->gross_vehicle_weight != '')
                                        {{ (floor($vehicle->type->gross_vehicle_weight) == $vehicle->type->gross_vehicle_weight) ? number_format($vehicle->type->gross_vehicle_weight, 0) : number_format($vehicle->type->gross_vehicle_weight, 2) }}
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>ADR test interval :</td>
                                    <td>{{ $vehicle->type->adr_test_date }}</td>
                                </tr>
                                <tr>
                                    <td>Compressor service interval:</td>
                                    <td>{{ $vehicle->type->compressor_service_interval }}</td>
                                </tr>
                                <tr>
                                    <td>Invertor service interval:</td>
                                    <td>{{ $vehicle->type->invertor_service_interval }}</td>
                                </tr>
                                <tr>
                                    <td>LOLER test interval:</td>
                                    <td>{{ $vehicle->type->loler_test_interval }}</td>
                                </tr>                                
                                <tr>
                                    <td>PMI interval:</td>
                                    <td>{{ $vehicle->type->pmi_interval }}</td>
                                </tr>
                                <tr>
                                    <td>PTO service interval:</td>
                                    <td>{{ $vehicle->type->pto_service_interval }}</td>
                                </tr>
                                <tr>
                                    <td>Service interval type:</td>
                                    <td>{{ $vehicle->type->service_interval_type }}</td>
                                </tr>
                                <tr>
                                    <td>Service interval:</td>
                                    <td>{{ $vehicle->type->service_interval_type == 'Time' ? $vehicle->type->service_inspection_interval : 'Every '.$vehicle->type->service_inspection_interval}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "history") }}" id="history">
                        <div class="row">
                            <div class="col-md-12">
                                <form id="vehicle_history_search_form">
                                    <input type="hidden" name="filter_history_vehicle_id" value="{{ $vehicle->id }}">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                {!! Form::text('search_history_event_date', null, ['id' => 'search_history_event_date', 'class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Report date', 'readonly' => 'readonly']) !!}
                                                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-0">
                                            <button class="btn btn-h-45 red-rubine" type="submit">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn js-vehicle-history-clear-btn btn-h-45 grey-gallery justify-content-center" style="margin-right: 0">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row js-history-search-error-msg" style="display: none">
                                    <div class="col-md-12">
                                        <div class="form-group has-error">
                                          <span class="help-block help-block-error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box marginbottom0">
                                    <div class="portlet-title bg-red-rubine">
                                        <div class="actions new_btn align-self-end">
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="jqgrid-wrapper">
                                            <table id="historyjqGrid" class="table-striped table-bordered table-hover" data-type="vehicles"></table>
                                            <div id="historyjqGridPager" class="multiple-action jqGridPager"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="registartion-number text-center"><span>{{ $vehicle->registration }}</span></div>
            <div class="row">
                @foreach ($vehicle->type->getMediaList() as $key => $media)
                    @if(is_a($media,'Spatie\MediaLibrary\Media'))
                        <div class="col-md-12">
                            <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-defect" data-title="{{ $key }}">
                                <img class="img-rounded img-responsive" src="{{getPresignedUrl($media)}}" alt="{{ $key }}">
                            </a>
                            <p class="text-center">{{ ucfirst(strtolower($key)) }}</p>
                        </div>
                    @else
                        <div class="col-md-12">
                            <div class="no--image no--image--4x">
                                <div class="no--image--title">
                                    No image uploaded
                                </div>
                            </div>
                            <p class="text-center">{{ ucfirst(strtolower($key)) }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

{{-- View vehicle costs summary --}}
<div id="view_vehicle_cost_modal" class="modal fade view_vehicle_cost_modal" data-width="1050"  data-backdrop="static" tabindex="-1" data-keyboard="false" role="dialog" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Vehicle Costs Summary</h4>
                <a class="font-red-rubine  vehicle-cost-summary-cancle" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                <div class="row d-flex align-items-center">
                    <div class="col-md-1"><label class="control-label" for="period">Period:</label></div>
                    <div class="col-md-2">
                        <select class="form-control select2me period-value" id="period" name="period">
                            @if(isset($displayMonthYear))
                                @foreach($displayMonthYear as $key => $month)
                                    <option value="{{ $key }}" {{ Carbon\Carbon::now()->subMonth(1)->format("M Y") == $month ? 'selected' : '' }} >{{ $month }}</option>
                                    {{-- <option value="{{ $key }}">{{ $month }}</option> --}}
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="row margin-top-20 margin-bottom-15">
                    <div class="col-md-3">
                        <div class="card card-bordered h-100 fixed-height-card-h1 bg-grey-cararra">
                            <div class="card-header">
                                <span class="card-title">Vehicle cost</span>
                            </div>
                            <div class="card-body h-100">
                                <div class="row align-items-center d-flex h-100">
                                    <div class="col-xs-12">
                                        <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                            <h1 class="count-number" id="vehicleVariableCost">&#163;</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-bordered h-100 fixed-height-card-h1 bg-grey-cararra">
                            <div class="card-header">
                                <span class="card-title">Miles per month</span>
                            </div>
                            <div class="card-body h-100">
                                <div class="row align-items-center d-flex h-100">
                                    <div class="col-xs-12">
                                        <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                            <h1 class="count-number" id="odometerMilesPerMonthValue"></h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-bordered h-100 fixed-height-card-h1 bg-grey-cararra">
                            <div class="card-header">
                                <span class="card-title">Cost per mile</span>
                            </div>
                            <div class="card-body h-100">
                                <div class="row align-items-center d-flex h-100">
                                    <div class="col-xs-12">
                                        <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                            <h1 class="count-number" id="vehicleCostPerMileValue"></h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-bordered h-100 fixed-height-card-h1 bg-grey-cararra">
                            <div class="card-header">
                                <span class="card-title">Defects/Damage costs</span>
                            </div>
                            <div class="card-body h-100">
                                <div class="row align-items-center d-flex h-100">
                                    <div class="col-xs-12">
                                        <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                            <h1 class="count-number" id="damageCostValue"></h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-between">
                    <div class="col-md-4 text-center">
                        <canvas id="myChart" width="130" height="200" style="max-width: 280px; margin: 0 auto;"></canvas>
                    </div>
                    <div class="col-md-8">
                        <canvas id="myLineChart" width="200" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-offset-2 col-md-8">
                    <div class="btn-group pull-left width100">
                        <button type="button" class="btn white-btn col-md-12 btn-padding vehicle-cost-summary-cancle" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.modal -->

{{-- Add new maintenance history --}}

<div id="add_new_maintenance_history" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            @include("_partials.vehicles.add_maintainance_history")
        </div>
    </div>
</div><!-- /.modal -->


<!-- Modal to add event starts here -->
<div id="add-event" class="modal modal-fix fade" tabindex="-1" data-width="620" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Add Event</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation')->id('addEvent') !!}

                <div class="align-items-center d-flex form-group">
                    <label class="col-md-3 control-label pt-0" for="eventName">Event*:</label>
                    <div class="col-md-9">
                        <input type="text" name="eventName" id="eventName" class="form-control add_event">
                    </div>
                </div>

                {{-- {!! BootForm::text('Abbreviation*:', 'abbreviation') !!} --}}
                {!! BootForm::close() !!}
            </div>
            <div class="modal-footer">

                <div class="btn-group pull-left width100">
                    <button type="button" id="addWorkshopCompanyCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="addEventBtn" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal to add events starts here -->

<div id="view-events" class="modal modal-fix fade" tabindex="-1" data-width="620" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">All Events</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                <div class="table-wrapper-scroll-y my-custom-scrollbar">
                    <table class="table table-hover table-striped table-company">
                        <thead class="thead-dark">
                        <tr>
                            <th scope="col" width="70%">Event Name</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody id="view_all_events">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">

                {{--<div class="btn-group pull-left width100">
                    <button type="button" id="addWorkshopCompanyCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="addEventBtn" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                </div>--}}
            </div>
        </div>
    </div>
</div>


{{-- Show maintenance history --}}
<div id="show_maintenance_history" class="modal modal-fix fade" tabindex="-1" data-width="620" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
        </div>
    </div>
</div><!-- /.modal -->


{{-- Edit maintenance history --}}

<div id="edit_new_maintenance_history" class="modal modal-fix fade" tabindex="-1" data-width="640" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;width:640px;">
        <div class="modal-content">
        </div>
    </div>
</div>
<!-- /.modal -->


<div id="edit_assignment_value" class="modal modal-fix fade" tabindex="-1" data-width="620" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            {{-- @include("_partials.vehicles.edit_assignment") --}}
        </div>
    </div>
</div>
<!-- /.modal -->

<div id="12_month_schedule_modal" class="modal modal-fix fade" tabindex="-1" data-width="620" data-backdrop="static" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
        </div>
    </div>
</div>

{{-- Delete maintenance history --}}
<div class="modal fade default-modal maintenance_history_delete_pop_up" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <input type="hidden" name="maintenance_history_delet_id" id="maintenance_history_delet_id" value="">
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button id="" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="maintenancehistoryEntryDelete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete assignment data --}}
<div class="modal fade default-modal assignment_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <input type="hidden" name="assignment_delet_id" id="assignment_delet_id" value="">
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="assignmentEntryDelete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete maintenance eventType data --}}
<div id="confirmEventDelete" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this event?
            </div>
            <input type="hidden" name="event_delet_id" id="maintenance_event_delete_id" value="">
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="maitenanceEntryDelete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Confirm update next pmi schedule --}}
<div id="confirmUpdatePMI" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Update PMI Schedule?</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <form class="form-horizontal" role="form" id="frmMaitenancePMIupdate">
                <div class="modal-body">
                        <p>Would you like to update the future PMI schedule for this vehicle based on the new PMI event date?</p>
                        <p>Please note that this will affect the future scheduled dates for this vehicle.</p>
                        <div class="error-class mb-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="radio-default-overright font-sm">
                                        <input type="radio" name="update_pmi_schedule" class="js-update-pmi-schedule-radio" value="1">Yes, I would like to update all future scheduled PMI dates (next PMI on <span class='js-new-pmi-date'></span>).
                                    </label>
                                </div>
                                <div class="col-md-12">
                                    <label class="radio-default-overright font-sm">
                                        <input type="radio" name="update_pmi_schedule" class="js-update-pmi-schedule-radio" value="0">No, please keep the scheduled dates as they are (next PMI on <span class='js-current-pmi-date'></span>).
                                    </label>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100 col-lg-offset-3">
                        {{-- <button type="button" id="maitenanceConfirmPMIupdateCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">No</button>
                        <button id="maitenanceConfirmPMIupdate" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button> --}}
                        <button id="maitenancePMIupdate" type="button" class="btn red-rubine btn-padding col-md-6 submit-button disabled">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm Edit update next pmi schedule --}}
<div id="confirmUpdatePMIEdit" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Update PMI Schedule?</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <form class="form-horizontal" role="form" id="frmMaitenancePMIupdateEdit">
                <div class="modal-body">
                        <p>Would you like to update the future PMI schedule for this vehicle based on the new PMI event date?</p>
                        <p>Please note that this will affect the future scheduled dates for this vehicle.</p>
                        <div class="error-class mb-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="radio-default-overright font-sm">
                                        <input type="radio" name="update_pmi_schedule_edit" class="js-update-pmi-schedule-edit-radio" value="1">Yes, please change it (next PMI due by <span class='js-new-pmi-date-edit'></span>).
                                    </label>
                                </div>
                                <div class="col-md-12">
                                    <label class="radio-default-overright font-sm">
                                        <input type="radio" name="update_pmi_schedule_edit" class="js-update-pmi-schedule-edit-radio" value="0">No, please keep the scheduled as it is (next PMI due by <span class='js-current-pmi-date-edit'></span>).
                                    </label>
                                </div>
                                <div class="col-md-12">
                                    <label class="control-label">Next scheduled date:</label>
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group date current_pmi_calendar">
                                        <input type="text" size="16" class="form-control" name="current_pmi_date" id="current_pmi_date" value="" placeholder="" disabled>
                                        <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100 col-lg-offset-3">
                        {{-- <button type="button" id="maitenanceConfirmPMIupdateCancelEdit" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">No</button>
                        <button id="maitenanceConfirmPMIupdateEdit" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button> --}}
                        <button id="maitenancePMIupdateEdit" type="button" class="btn red-rubine btn-padding col-md-6 submit-button disabled">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="uploadVehicleDocumentModal" class="modal modal-fix  fade user_modal" tabindex="-1" role="dialog" data-backdrop="static" data-width="1050">
    {{-- <div class="modal-dialog"> --}}
        {{-- <div class="modal-content"> --}}
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Vehicle Documents</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" onclick="setTimeout(function() {$('.documents').trigger('click')}, 100);">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" action="{{ '/vehicles/get_store_docs/'.$vehicle->id }}" id="updateVehicleDocument" data-upload-template-id="vehicle-template-upload-2" data-download-template-id="template-download-2" enctype="multipart/form-data">
                        <div class="fileupload-buttonbar">
                                <div class="dropZoneElement">
                                    <div class="fileinput-button">
                                        <div>
                                            <p class="fileinput-button-title"><span>+</span>Add file</p>
                                            <p class="dropImageHereText">Click or drop your file here to upload</p>
                                            <p class="dropImageHereText mt-40"><strong>Note:</strong> Please add maintenance documents in the maintenance tab</p>
                                            <input type="file" name="files[]" multiple="" accept=".gif, .jpg, .jpeg, .png, .doc, .docx, .xls, .xlsx, .csv, .pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="help-block text-center">(*.gif, *.jpg, *.jpeg, *.png, *.doc, *.docx, *.xls, *.xlsx, *.csv, *.pdf)</div>

                            <!-- The table listing the files available for upload/download -->
                            <table role="presentation" class="table table-striped table-hover custom-table-striped clearfix" id="upload-media-modal-table">
                                <thead>
                                    <th>Preview</th>
                                    <th>Document Name</th>
                                    <th>Size</th>
                                    <th>Uploaded By</th>
                                    <th>Date Uploaded</th>
                                    <th style="text-align: center;">Actions</th>
                                </thead>
                                <tbody class="files">
                                </tbody>
                            </table>

                            <script id="vehicle-template-upload-2" type="text/x-tmpl">
                                {% for (var i=0, file; file=o.files[i]; i++) { %}
                                    <tr class="template-upload fade">
                                        <td>
                                            <span class="preview">
                                            </span>
                                        </td>
                                        <td class="js-file-name-td">
                                            <input type="text" placeholder="Enter document name" id="caption" name="name" class="form-control"/>
                                            <span class="help-block help-block-error" style="display: none;">This field is required</span>
                                            <div class="progress mb-0 bg-grey">
                                                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                                    <span class="sr-only">20% Complete</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="white-space: nowrap">
                                            <p class="size">Processing...</p>
                                        </td>
                                        <td>
                                            <p class="uploaded-by">{%=Site.authUserName%}</p>
                                        </td>
                                        <td>
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">

                                            {% if (!i) { %}
                                                <button class="btn grey-gallery cancel">
                                                    <span>Cancel</span>
                                                </button>
                                            {% } %}
                                            {% if (!i && !o.options.autoUpload) { %}
                                                <button class="btn red-rubine start" disabled>
                                                    <span>Upload</span>
                                                </button>
                                            {% } %}
                                        </td>
                                    </tr>
                                {% } %}
                            </script>

                            <script id="template-download-2" type="text/x-tmpl">
                                {% for (var i=0, file; file=o.files[i]; i++) { %}
                                    <tr class="template-download fade">
                                        <td>
                                            {% if (file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpeg') { %}
                                                <span class="jv-icon jv-file-image table-docpreview-icon"></span>
                                            {% } else if (file.type === 'image/gif') { %}
                                                <span class="jv-icon jv-file-gif table-docpreview-icon"></span>
                                            {% } else if (file.type === 'application/pdf') { %}
                                                <span class="jv-icon jv-file-pdf table-docpreview-icon"></span>
                                            {% } else if (file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') { %}
                                                <span class="jv-icon jv-file-word table-docpreview-icon"></span>
                                            {% } else if (file.type === 'application/mspowerpoint' || file.type === 'application/powerpoint' || file.type === 'application/vnd.ms-powerpoint' || file.type === 'application/x-mspowerpoint' || file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') { %}
                                                <img src="/img/document_icons/ppt.png" style="height: 45px;">
                                            {% } else if (file.type === 'application/vnd.ms-excel' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') { %}
                                                <span class="jv-icon jv-file-excel table-docpreview-icon"></span>
                                            {% } else if (file.extension === 'csv') { %}
                                                <span class="jv-icon jv-file-csv table-docpreview-icon"></span>
                                            {% } else { %}
                                                <span class="jv-icon jv-doc table-docpreview-icon"></span>
                                            {% } %}
                                        </td>
                                        <td style="word-break: break-all;">
                                            <p class="name">
                                                {% if (file.url) { %}
                                                    <a rel="noopener" target="_blank" href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                                {% } else { %}
                                                    <span>{%=file.name%}</span>
                                                {% } %}
                                            </p>
                                            {% if (file.error) { %}
                                                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                            {% } %}
                                        </td>
                                        <td style="white-space: nowrap">
                                            <p class="size">{%=file.size%}</p>
                                        </td>
                                        <td>
                                            <p>{%=file.uploadedBy%}</p>
                                        </td>
                                        <td><p>{%=file.created%}</p></td>
                                        <td style="text-align: center">
                                            {% if (file.deleteUrl) { %}

                                                <div class="delete-wrapper">
                                                    <button class="btn doc-delete-btn1 date-set grey-gallery tras_btn btn-h-45" type="button">
                                                        <i class="jv-icon jv-dustbin normal-font"></i>
                                                    </button>
                                                    <button class="delete" style="display:none;" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                                                        <i class="jv-icon jv-dustbin normal-font"></i>
                                                    </button>
                                                </div>
                                            {% } else { %}
                                                <button class="btn doc-delete-btn1 date-set grey-gallery tras_btn btn-h-45 disabled" type="button">
                                                        <i class="jv-icon jv-dustbin normal-font"></i>
                                                    </button>
                                            {% } %}
                                        </td>
                                    </tr>
                                {% } %}
                            </script>
                        </form>
            </div>
        {{-- </div> --}}
    {{-- </div> --}}
</div>

{{-- Delete document --}}
<div class="modal fade default-modal delete-document-modal" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this document?
            </div>
            <input type="hidden" name="document_delete_id" id="document_delete_id" value="">
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button id="" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="documentDeleteBtn" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="confirmUpdateMOTEdit" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Update MOT Schedule?</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close close_mot_schedule"></i>
                </a>
            </div>
            <form class="form-horizontal" role="form" id="frmConfirmUpdateMOTEdit">
                <div class="modal-body">
                    {{-- <input type="hidden" name="confirmUpdateMOTEdit" id="confirmUpdateMOTEdit" value='0' />
                    <input type="hidden" name="confirmUpdateMOTDecided" id="confirmUpdateMOTDecided" value='0' /> --}}
                    <p>Would you like to update the future MOT schedule for this vehicle based on the new MOT event date?</p>
                    <div class="error-class mb-10">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="radio-default-overright font-sm">
                                    <input type="radio" name="updateMOT" class="roles-types-radio" value="1">Yes, please change it (next MOT due by <span id='motAsPerNew'></span>)
                                </label>
                            </div>
                            <div class="col-md-12">
                                <label class="radio-default-overright font-sm">
                                    <input type="radio" name="updateMOT" class="roles-types-radio" value="0" >No, please keep the scheduled as it is (next MOT due by <span id='motAsPerOld'></span>)
                                </label>
                            </div>
                            <div class="col-md-12">
                                <label class="control-label">Next scheduled date:</label>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group date current_mot_calendar">
                                    <input type="text" size="16" class="form-control" name="current_mot_date" id="current_mot_date" value="" placeholder="" disabled>
                                    <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100 col-lg-offset-3">
                        <button id="maitenanceConfirmMOTupdateEdit" type="button" class="btn red-rubine btn-padding col-md-6 submit-button disabled">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add new assignment --}}
<div id="add_new_assignment" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="modal-content">
            @include("_partials.vehicles.add_assignment")
        </div>
    </div>
</div><!-- /.modal -->
@endsection
