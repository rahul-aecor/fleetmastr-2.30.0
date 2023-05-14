@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/inputs-ext/address/address.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/defects.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery.mockjax.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/inputs-ext/address/address.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/form-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-bar">
        <?php 
            $defectRegistrationUrl = '';
            if($defect->vehicle->status == "Archived" || $defect->vehicle->status == "Archived - De-commissioned" || $defect->vehicle->status == "Archived - Written off") {
                $defectRegistrationUrl = '/vehicles/' .  $defect->vehicle->id . '?vehicleDisplay='.$vehicleDisplay;
            } else {
                $defectRegistrationUrl =  '/vehicles/' .  $defect->vehicle->id;
            }
        ?>
        {!! Breadcrumbs::render('defect_details') !!}
        <div class="page-toolbar">
        <div>
            <a class="btn btn-plain" href="#" id='edit-vehicle-defect-btn' <?php if ($defect->duplicate_flag == 1) { echo "disabled";}?> >
                <i class="jv-icon jv-edit"></i> Edit defect
            </a>
            <a class="btn hidden-print btn-plain" href="{{ url('defects/exportNotePdf/' . $defect->id) }}">
                <i class="jv-icon jv-download"></i> Export defect note
            </a>
            <a class="btn hidden-print btn-plain" href="{{ url('defects/exportPdf/' . $defect->id) }}">
                <i class="jv-icon jv-download"></i> Export defect history
            </a>
            </div>
        </div>
    </div>
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Vehicle Summary</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <tr>
                            <td>Registration:</td>
                            <td><a class="text-info font-blue" href="{{ url($defectRegistrationUrl) }}">{{ $defect->vehicle->registration }}</a></td>
                        </tr>
                        <tr>
                            <td>Date added to fleet:</td>
                            <td>{{ $defect->vehicle->dt_added_to_fleet }}</td>
                        </tr>
                        <tr>
                            <td>Type:</td>
                            <td>{{ $defect->vehicle->type->vehicle_type }}</td>
                        </tr>
                        <tr>
                            <td>Category:</td>
                            <td>{{ $defect->vehicle->type->present()->vehicle_category_to_display() }}</td>
                        </tr>
                        @if($defect->vehicle->type->vehicle_category == "non-hgv")
                        <tr>
                            <td>Sub category:</td>
                            <td>{{ $defect->vehicle->type->present()->vehicle_sub_category_to_display() }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Manufacturer:</td>
                            <td>{{ $defect->vehicle->type->manufacturer }}</td>
                        </tr>
                        <tr>
                            <td>Model:</td>
                            <td>{{ $defect->vehicle->type->model }}</td>
                        </tr>
                        <tr>
                            <td>Odometer:</td>
                            @if ($defect->check->odometer_reading)
                                <td>{{ number_format($defect->check->odometer_reading) . ' ' . $defect->vehicle->type->odometer_setting }}</td>
                            @else
                                <td>{{ number_format($defect->vehicle->last_odometer_reading) . ' ' . $defect->vehicle->type->odometer_setting }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Vehicle status:</td>
                            <td id="vehicle-status-select">
                                <span class="label vehicle-status-view {{ $defect->vehicle->present()->label_class_for_status }} label-results">  {{ $defect->vehicle->status }} @if(starts_with($defect->vehicle->status, 'VOR') 
                                    && $vorDuration) ({{ $vorDuration }}) @endif
                                </span>
                                <div class="editable-wrapper" style="display: none">
                                    <a class="vehicle-status-edit" data-type="select2" data-pk="{{ $defect->vehicle->id }}" data-value="{{ $defect->vehicle->status }}">{{ $defect->vehicle->status }}</a>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="portlet box mb0">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Defect Data</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <!-- <tr>
                            <td>Defect number:</td>
                            <td>{{ $defect->id }}</td>
                        </tr> -->
                        <tr>
                            <td>Created by:</td>
                            <td>{{ $defect->creator->first_name }} {{ $defect->creator->last_name }} (<a href="mailto:{{$defect->creator->email}}" class="font-blue">{{ $defect->creator->email }}</a>)</td>
                        </tr>
                        <tr>
                            <td>Created date:</td>
                            <td>{{ $defect->present()->formattedReportDatetime() }}</td>
                        </tr>
                        <tr>
                            <td>Last modified by:</td>
                            <td>{{ $defect->updater->first_name }} {{ $defect->updater->last_name }} (<a href="mailto:{{$defect->updater->email}}" class="font-blue">{{ $defect->updater->email }}</a>)</td>
                        </tr>
                        <tr>
                            <td>Last modified date:</td>
                            <td>{{ $defect->present()->formattedUpdatedAt() }}</td>
                        </tr>
                        @if($defect->status == 'Resolved')
                        <tr>
                            <td>Resolved date:</td>
                            <td>{{ $defect->present()->formattedResolvedDatetime()}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Check:</td>
                            <td>{{ $defect->check->present()->types_to_display() }}</td>
                        </tr>
                        <tr>
                            <td>Trailer attached:</td>
                            <td>{{ $defect->check->is_trailer_attached == 1 ? "Yes" : "No" }}</td>
                        </tr>
                        <tr>
                            <td>Trailer ID:</td>
                            <td>{{ $defect->check->is_trailer_attached == 1 ? $defect->check->trailer_reference_number : "Not applicable" }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Defect Details</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary" id="defect-details">
                        <tbody>
                            <tr>
                                <td>Defect number:</td>
                                <td>{{ $defect->id }}</td>
                            </tr>
                            <tr>
                                <td>Category:</td>
                                <td>{{ $defect->defectMaster->page_title }}</td>
                            </tr>
                            <tr>
                                <td>Defect:</td>
                                <td>{{ $defect->title != null ? $defect->title : $defect->defectMaster->defect }}</td>
                            </tr>
                            <tr>
                                <td>Roadside assistance:</td>
                                <td id="defect-roadside-assistance-td">
                                    <span class="defect-roadside-assistance-view">
                                        {{ $defect->roadside_assistance }}
                                    </span>
                                    <div class="editable-wrapper defect-workshop-close editable-wrapper-width" id="defect_roadside_assistance" style="display: none">
                                        <a href="javascript:void" id="defect-roadside-assistance-edit" class="
                                        defect-roadside-assistance-edit" data-type="select2" data-pk="{{ $defect->id }}" data-value="{{ $defect->roadside_assistance }}">{{ $defect->roadside_assistance }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Defect status: &nbsp;<button class="btn btn-icon-only btn-circle small" data-toggle="modal" data-target="#defect-info-modal"><i class="fa fa-info"></i></button></td>
                                <td id="defect-status-td">
                                    <span class="label defect-status-view label-default {{ $defect->present()->label_class_for_status }} label-results">
                                        {{ $defect->status }}  <?php if ($defect->duplicate_flag == 1) { echo " (D)";}?>
                                    </span>
                                    <div class="editable-wrapper" id="defect_status" style="display: none">
                                        <a href="javascript:void" id="defect-status-edit" class="
                                        defect-status-edit" data-type="select2" data-pk="{{ $defect->id }}" data-value="{{ $defect->status }}">{{ $defect->status }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr id="rejectreason" <?php if($defect->status != 'Repair rejected') {?> style="display: none;" <?php } ?> >
                                <td>Reject reason: &nbsp;</td>
                                <td id="defect-rejectreason-td" >
                                    <span class="defect-rejectreason-view" >
                                        {{ $defect->rejectreason }}  <?php if (empty($defect->rejectreason)) { echo "N/A";}?>
                                    </span>
                                    <div class="editable-wrapper defect-workshop-close editable-wrapper-width" id="rejectreason-editable-wrapper" style="display: none">
                                        <a href="#" id="defect-rejectreason-edit" class="defect-rejectreason-edit" data-type="select2" data-pk="{{ $defect->id }}" data-value="{{ $defect->rejectreason }}">{{ $defect->rejectreason }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr id="defectallocatedto">
                                <td>Defect allocated to: &nbsp;</td>
                                <td id="defect-workshop-td" >
                                    <span class="defect-workshop-view" >
                                        <?php
                                        $workshopShow = "";
                                        if(isset($workshops)) {
                                            foreach ($workshops as $key => $value) {
                                                $value = json_decode($value);
                                                if ($value->value == $defect->workshop) {
                                                    $workshopShow = $value->text;
                                                }
                                            }
                                        }
                                        ?>
                                        {{ $workshopShow }}  <?php if (empty($defect->workshop)) { echo "N/A";}?>

                                    </span>

                                    <div class="editable-wrapper defect-workshop-close editable-wrapper-width" style="display: none">
                                        <a href="#" id="defect-workshop-edit" class="defect-workshop-edit" data-type="select2" data-pk="{{ $defect->id }}" data-value="{{ $defect->workshop }}">{{ $workshopShow }}
                                         <?php if (empty($workshopShow)) { echo "N/A";}?></a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Est completion date:</td>
                                <td id="completion_date_td">
                                    <span class="defect-completion-view" >
                                        @if($defect->est_completion_date == null)
                                            N/A
                                        @else
                                            {{ $defect->est_completion_date }}
                                        @endif
                                    </span>
                                    <div class="editable-wrapper" style="display: none">
                                        <a href="javascript:;" class="defect-completion-edit" id="est_completion_date" data-type="date" data-viewformat="dd M yyyy" data-pk="{{ $defect->id }}" data-placement="right">
                                        @if($defect->est_completion_date == null)
                                            N/A
                                        @else
                                            {{ $defect->est_completion_date }}
                                        @endif
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td> Estimated defect cost &pound;:</td>
                                <td id="estimated-defect-cost-td">
                                    <span class="vehicle-estimated-defect-cost-view">
                                        @if($defect->estimated_defect_cost_value == null || $defect->estimated_defect_cost_value == '')
                                            N/A
                                        @else
                                            {{ number_format($defect->estimated_defect_cost_value,2) }}
                                        @endif
                                    </span>
                                    <div class="editable-wrapper" style="display: none" id="estimated_defect_cost_value">
                                        <a href="javascript:void" id="vehicle-estimated-defect-cost-edit" class="vehicle-estimated-defect-cost-edit" data-type="text" data-pk="{{ $defect->id }}" data-value="{{ $defect->estimated_defect_cost_value }}">
                                            @if($defect->estimated_defect_cost_value == null || $defect->estimated_defect_cost_value == '')
                                                N/A
                                            @else
                                                {{ $defect->estimated_defect_cost_value }}
                                            @endif
                                        </a>&nbsp;
                                    </div>
                                    <span class="estimated_defect_cost_hint" style="display: none">If cost is covered by warranty enter "0".</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Actual defect cost &pound;:</td>
                                <td id="actual-defect-cost-td">
                                    <span class="vehicle-actual-defect-cost-view">
                                        @if($defect->actual_defect_cost_value == null || $defect->actual_defect_cost_value =='')
                                            N/A
                                        @else
                                            {{ number_format($defect->actual_defect_cost_value,2) }}
                                        @endif
                                    </span>
                                    <div class="editable-wrapper" style="display: none" id="actual_defect_cost_status">
                                        <a href="javascript:void" class="vehicle-actual-defect-cost-edit" id="vehicle-actual-defect-cost-edit" data-type="text" data-pk="{{ $defect->id }}" data-value="{{ $defect->actual_defect_cost_value }}">
                                            @if($defect->actual_defect_cost_value == null || $defect->actual_defect_cost_value =='')
                                                N/A
                                            @else
                                                {{ $defect->actual_defect_cost_value }}
                                            @endif
                                        </a>&nbsp;
                                    </div>
                                    <span class="actual_defect_cost_hint" style="display: none">If cost is covered by warranty enter "0".</span>
                                </td>
                            </tr>
                            @if(config('branding.name') != "clh")
                            <tr>
                                <td>Defect invoice date:</td>
                                <td id="invoice-date-td">
                                    <span class="defect-invoice-date-view">
                                        @if($defect->invoice_date == null)
                                            N/A
                                        @else
                                            {{ $defect->invoice_date }}
                                        @endif
                                    </span>
                                    <div class="editable-wrapper" style="display: none">
                                        <a href="#" class="defect-invoice-date-edit" data-type="date" data-pk="{{ $defect->id }}" data-type="date" data-viewformat="dd M yyyy" id="defect-invoice-date">
                                            @if($defect->invoice_date == null)
                                                N/A
                                            @else
                                                {{ $defect->invoice_date }}
                                            @endif
                                        </a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td> Defect invoice number:</td>
                                <td id="defect-invoice-number-td">
                                    <span class="defect-invoice-number-view">
                                        @if($defect->invoice_number == null)
                                            N/A
                                        @else
                                            {{ $defect->invoice_number }}
                                        @endif
                                    </span>
                                    <div class="editable-wrapper" style="display: none">
                                        <a href="#" class="defect-invoice-number-edit" data-type="text" data-pk="{{ $defect->id }}" data-value="{{ $defect->invoice_number }}"  id="defect-invoice-number">
                                            @if($defect->invoice_number == null)
                                                N/A
                                            @else
                                                {{ $defect->invoice_number }}
                                            @endif
                                        </a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>Days VOR:</td>
                                <td>{{ $vorDay }}</td>
                            </tr>
                            <tr>
                                <td>VOR cost &pound;:</td>
                                <td>
                                    <span>
                                        {{ number_format($vorCostPerDay) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Defect Image
                    </div>
                </div>
                <div class="portlet-body text-center" style="height: 206px;">
                    @if (count($images))
                        @foreach ($images as $image)
                            <div class="col-md-3">
                                <a href="{{ asset(getPresignedUrl($image)) }}" data-lightbox="img-defect">
                                    {{-- <img src="{{ asset(getPresignedUrl($image)) }}" alt="" class="img-rounded" style="height: 192px;">--}}
                                    <img src="{{ asset(getPresignedUrl($image)) }}" alt="" class="img-rounded" style="width:100%;">
                                </a>
                            </div>
                        @endforeach
                    @else
                        <div class="no-image-text-box">
                            <p>Image capture not mandatory for this defect.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
       <!--  <div class="@if (count($images)) col-sm-10 @else col-sm-12 @endif"> -->
        <div class="">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                      <!--   <i class="icon-bubble font-red-rubine"></i> -->
                        <!-- <span class="caption-subject font-red-rubine bold">Add New Comment</span> -->
                        <div>Add New Comment</div>
                    </div>
                </div>
                <div class="portlet-body pt15" id="defects-comments-drag">
                 {!! BootForm::openHorizontal(['md' => [3, 8]])->id('saveCommentForDefect')->addClass('form-bordered form-validation')->action('/defects/storeComment')->multipart() !!}
                <div class="alert alert-danger display-hide  bg-red-rubine">
                    <button class="close" data-close="alert"></button>
                    <!-- You have some form errors. Please check below. -->
                    Please complete the errors highlighted below.
                </div>
                <div class="input-cont form-group">
                    <textarea name="comments" id="" rows="4" class="form-control" placeholder="Enter comments here" autocomplete="off"></textarea>
                </div>
                <div class="fileupload-buttonbar">
                    <div class="dropZoneElement">
                        <div class="fileinput-button">
                            <div>
                                <p class="fileinput-button-title"><span>+</span>Add file</p>
                                <p class="dropImageHereText">Click or drop your file here to upload</p>
                                <input type="file" name="attachment" class="select-file-defect">
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
                        <input type="text" name="file_input_name" id="name" class="form-control fileupload" placeholder="Enter file name here" autocomplete="off">
                </div>
                <div class="col-md-7">
                    <div class="row d-flex align-items-center">
                        <div class="col-md-12">
                            <!-- <span class="btn red-rubine btn-file select-file-defect">
                                <span class="fileinput-new">Select file</span>
                                <input type="file" name="attachment" class="select-file-defect">
                            </span> -->
                            <span class="btn red-rubine btn-file js-new-document-file">
                                <span class="fileinput-new">Select file</span>
                            </span>
                            <button class="fileinput-exists btn grey-gallery remove-file-defect" style="display: none;" data-dismiss="fileinput">Remove</button>
                            <div class="inline-block ml-3">
                                <span class="js-file-name"></span>
                            </div>
                            <!-- <div class="fileinput fileinput-new" data-provides="fileinput">
                                <span class="btn red-rubine btn-file">
                                    <span class="fileinput-new">Select file</span>
                                    <span class="fileinput-exists">Change</span>
                                    <input type="file" name="attachment">
                                </span>
                                <a href="#" class="fileinput-exists btn grey-gallery" data-dismiss="fileinput" style="float: none">Remove</a>
                            </div>   -->
                        </div>
                    </div>
                </div>
                </div>
                <!-- <div class="input-cont form-group col-md-11" style="padding-left:0px;">
                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                      <span class="input-group-addon btn grey-gallery haze btn-file">
                          <span class="fileinput-new">Select file</span>
                          <span class="fileinput-exists">Change</span>
                          <input type="file" name="attachment">
                      </span>
                      <a href="#" class="input-group-addon btn grey-gallery fileinput-exists" data-dismiss="fileinput">Remove</a>
                      <div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div>
                    </div>
                </div> -->
                <input type="hidden" id="defect_id" name="defect_id" value="{{ $defect->id }}" />
                <div class="btn-cont" id="defects-comment-button">
                    <input type="submit" class="btn icn-only red-rubine" value="Save" id="saveComment">
                    </a>
                </div>
            {!! BootForm::close() !!}
        </div>
    </div>
    <div class="portlet light ">
        <div class="portlet-title">
            <div class="caption">
               <!--  <i class="icon-list font-red-rubine"></i> -->
                <!-- <span class="caption-subject font-red-rubine bold">Defect History</span> -->
                <div>Defect History</div>
            </div>
        </div>
        <div class="portlet-body">
            <div class="timeline defect-comments-timeline js-defect-comments">
                @foreach ($comments as $comment)
                    @if ($comment->type === 'user')
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
                                            @if ($comment->defect_status_comment != NULL)
                                                <span class="timeline-body-time">
                                                    {{ $comment->defect_status_comment }} and added comment at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="timeline-body-time">
                                                    wrote at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('d M Y') }}
                                                </span>
                                            @endif
                                            @if ($comment->created_at != $comment->updated_at)
                                            <span class="timeline-body-time"> edited at {{ $comment->present()->formattedUpdatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedUpdatedAt()->format('d M Y') }}</span>
                                            @endif
                                    </div>
                                    </br>
                                    <div class="timeline-body-head-actions">
                                        <div class="">
                                        @if ($comment->created_by == Auth::id())
                                            <button type="button" class="btn red-rubine edit-comment-btn btn-height" style=""><i class="jv-icon jv-edit"></i></button>
                                            <button type="button" data-delete-url="/defects/delete_comment/{{ $comment->id }}"
                                                class="btn delete-button grey-gallery btn-height ml0"
                                                title="Delete comment"
                                                data-confirm-msg="Are you sure you would like to delete this comment?">
                                                <i class="jv-icon jv-dustbin"></i>
                                            </button>
                                        @endif
                                        </div>
                                    </div>
                                    <div class="timeline-body-content">
                                        @if($comment->comments)
                                            @if ($comment->created_by == Auth::id())
                                            <span class="">
                                                <a href="javascript:;" class="comments" data-type="textarea" data-pk="{{ $comment->id }}" data-original-title="Update comment">{{ $comment->comments }}</a>
                                            </span>
                                            @else
                                                <span class=""><br/>{!! nl2br($comment->comments) !!}</span>
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
                                                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                                                    </div>

                                                    @if($media->getCustomProperty('relates-to'))
                                                        <div class="margin-top-10">
                                                            @if($media->getCustomProperty('relates-to') === 'job_sheet')
                                                                Image of job sheet
                                                            @endif
                                                            @if($media->getCustomProperty('relates-to') === 'additional_information')
                                                                Additional image
                                                            @endif
                                                        </div>
                                                    @endif
                                                    
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
                    @else
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <div class="timeline-icon">
                                    <i class="icon-bell font-red-rubine"></i>
                                </div>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-body-arrow">
                                </div>
                                <div class="timeline-body-head">
                                    <div class="timeline-body-head-caption">
                                        {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="bold timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                                        <span class="timeline-body-time">{{ $comment->comments }} at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <div class="timeline-body-content">
                                    @if ($comment->defect_status_comment != NULL)
                                        <span class=""> {{ $comment->defect_status_comment }} </span>
                                    @endif
                                    @foreach ($comment->getMedia() as $media)
                                        <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="modal fade" id="defect-info-modal" tabindex="-1" role="dialog" aria-labelledby="defect-info-modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                  <!--   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                    <h4 class="modal-title" id="defect-info-modal-title">Defect Status Information</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <table class="table" id="defect-info-table">
                        @if(!Auth::user()->isWorkshopManager())
                        <tr>
                            <td><span class="label label-danger label-results">Reported</span></td>
                            <td>Driver has reported a defect.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-warning label-results">Acknowledged</span></td>
                            <td>Fleet management has acknowledged the defect.</td>
                        </tr>
                        @endif
                        <tr>
                            <td><span class="label label-warning label-results">Allocated</span></td>
                            <td>Defect has been allocated and awaiting repair.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-warning label-results">Under repair</span></td>
                            <td>Work has started on repairing the defect.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-warning label-results">Discharged</span></td>
                            <td>Defect repair has now been completed.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-danger label-results">Repair rejected</span></td>
                            <td>Defect repair has been rejected by workshop.</td>
                        </tr>
                        @if(!Auth::user()->isWorkshopManager())
                        <tr>
                            <td><span class="label label-success label-results">Resolved</span></td>
                            <td>Defect has been resolved.</td>
                        </tr>
                        @endif
                        <!--                         <tr>
                            <td><span class="label label-success label-results">(D)</span></td>
                            <td>Duplicate defect.</td>
                        </tr> -->
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" class="btn white-btn btn-padding col-md-12" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Defect status modal-->

    <div class="modal fade" id="defect_status_modal" data-backdrop="static" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add New Comment</h4>
                </div>
                <form id="defectStatus" class="form-validation defectStatus">
                    <div class="modal-body">
                        <div class="form-group" id="defect_comment">
                            <label for="comment" class="control-label">Comment:</label>
                                <textarea class="form-control" rows="5" name="comment" id="comment"></textarea>
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="defectStatusClose" data-dismiss="modal"> Close</button>
                        <button type="submit" class="btn btn-primary defect_status_modal_comment"
                        id="defectStatusSave">Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vehicle-status-modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Confirm Vehicle Status</h4>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comment" class="control-label">The list of defects below are unresolved for this vehicle. Would you like to continue to change the vehicle status?</label>
                        </div>
                        <table class="table no-wrap-header">
                            <thead>
                              <tr>
                                <th>Registration</th>
                                <th>Defect ID</th>
                                <th>Category</th>
                                <th>Defect</th>
                                <th>Defect Status</th>
                                <!-- <th>VOR Defect?</th> -->
                              </tr>
                            </thead>
                            <tbody>
                            @foreach($vehicleDefectRecords as $vehicleDefect)
                              <tr>
                                <td>{{$vehicleDefect->vehicle->registration}}</td>
                                <td>{{$vehicleDefect->id}}</td>
                                <td>{{$vehicleDefect->defectMaster->page_title}}</td>
                                <td>{{$vehicleDefect->defectMaster->defect}}</td>
                                @if($vehicleDefect->status == 'Reported')
                                <td class="label-danger label-results">{{$vehicleDefect->status}}</td>
                                @elseif($vehicleDefect->status == 'Acknowledged' || $vehicleDefect->status == 'Under repair' || $vehicleDefect->status == 'Discharged' || $vehicleDefect->status == 'Allocated')
                                <td class="label-warning label-results">{{$vehicleDefect->status}}</td>
                                @elseif($vehicleDefect->status == 'Resolved')
                                <td class="label-success label-results">{{$vehicleDefect->status}}</td>
                                @elseif($vehicleDefect->status == 'Repair rejected')
                                <td class="label-danger label-results">{{$vehicleDefect->status}}</td>
                                @endif
                                <!-- <td>{{$vehicleDefect->defectMaster->is_prohibitional == 1 ? 'Yes' : 'No'}}</td> -->
                              </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal" id="vehicleStatusClose">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6" data-dismiss="modal" id="vehicleStatusChange">Confirm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="cost_numeric_currency_field" data-backdrop="static" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="numeric_currency_title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="numeric_currency_title">Numeric Currency Field</h4>
                </div>
                <form id="estimaedValue" class="form-validation estimaedValue">
                    <div class="modal-body">
                        <div class="form-group" id="estimated_cost">
                            <label for="cost_numeric_value" class="control-label">&#163; cost</label>
                            <input class="form-control" name="cost_numeric_value" id="cost_numeric_value" value=""></input>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="defectEstimatedCostCancle" data-dismiss="modal"> Close</button>
                        <button type="submit" class="btn btn-primary defect_estimated_cost_value defect_status_modal_comment" id="defectEstimatedCostSave">Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="actual_defect_cost_currency_field" data-backdrop="static" data-backdrop="static" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="actual_defect_cost_currency_field">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Numeric Currency Field</h4>
                </div>
                <form id="actualDefectValue" class="form-validation actualDefectValue">
                    <div class="modal-body">
                        <div class="form-group" id="actual_cost">
                            <label for="actual_defect_cost" class="control-label">&#163; cost</label>
                                <input class="form-control" name="actual_defect_cost" id="actual_defect_cost" value=""></input>
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="defectActualCostCancle" data-dismiss="modal"> Close</button>
                        <button type="submit" class="btn btn-primary defect_actual_cost_value defect_status_modal_comment" id="defectActualCostSave">Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="defect_status_resolved" class="modal modal-fix  fade modal-overflow in" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="false" data-width="900">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Confirm Vehicle Status</h4>
                    <a class="font-red-rubine bootbox-close-button" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>There are no other unresolved defects on this vehicle. Would you like to change the vehicle status to "Roadworthy"?</div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="defect_status_resolved_cancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">No</button>
                        <button type="button" id="defect_status_resolved_yes" class="btn red-rubine btn-padding submit-button col-md-6">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection