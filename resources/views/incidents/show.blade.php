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
    <script src="{{ elixir('js/incidents.js') }}" type="text/javascript"></script>
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
            if($incident->vehicle->status == "Archived" || $incident->vehicle->status == "Archived - De-commissioned" || $incident->vehicle->status == "Archived - Written off") {
                $defectRegistrationUrl = '/vehicles/' .  $incident->vehicle->id . '?vehicleDisplay='.$vehicleDisplay;
            } else {
                $defectRegistrationUrl =  '/vehicles/' .  $incident->vehicle->id;
            }
        ?>
        {!! Breadcrumbs::render('incident_details') !!}
        <div class="page-toolbar">
        <div>
            <a class="btn btn-plain" href="#" id='edit-incident-btn'>
                <i class="jv-icon jv-edit"></i> Edit incident
            </a>
            <a class="btn hidden-print btn-plain" href="{{ url('incidents/exportPdf/' . $incident->id) }}">
                <i class="jv-icon jv-download"></i> Export incident history
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
                            <td><a class="text-info font-blue" href="{{ url($defectRegistrationUrl) }}">
                                {{ $incident->vehicle->registration }}</a></td>
                        </tr>
                        <tr>
                            <td>Type:</td>
                            <td>{{ $incident->vehicle->type->vehicle_type }}</td>
                        </tr>
                        <tr>
                            <td>Category:</td>
                            <td>{{ $incident->vehicle->type->present()->vehicle_category_to_display() }}</td>
                        </tr>
                        @if($incident->vehicle->type->vehicle_category == "non-hgv")
                        <tr>
                            <td>Sub category:</td>
                            <td>{{ $incident->vehicle->type->present()->vehicle_sub_category_to_display() }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Manufacturer:</td>
                            <td>{{ $incident->vehicle->type->manufacturer }}</td>
                        </tr>
                        <tr>
                            <td>Model:</td>
                            <td>{{ $incident->vehicle->type->model }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="portlet box mb0">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Incident Data</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <tr>
                            <td>Created by:</td>
                            <td>{{ $incident->creator->first_name }} {{ $incident->creator->last_name }} (<a href="mailto:{{$incident->creator->email}}" class="font-blue">{{ $incident->creator->email }}</a>)</td>
                        </tr>
                        <tr>
                            <td>Created date:</td>
                            <td>{{ $incident->present()->formattedCreatedAt() }}</td>
                        </tr>
                        <tr>
                            <td>Last modified by:</td>
                            <td>{{ $incident->updater->first_name }} {{ $incident->updater->last_name }} (<a href="mailto:{{$incident->updater->email}}" class="font-blue">{{ $incident->updater->email }}</a>)</td>
                        </tr>
                        <tr>
                            <td>Last modified date:</td>
                            <td>{{ $incident->present()->formattedUpdatedAt() }}</td>
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
                    <div class="caption">Incident Details</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary" id="incident-details">
                        <tbody>
                            <tr>
                                <td>Incident number:</td>
                                <td>{{ $incident->id }}</td>
                            </tr>
                            <tr>
                                <td>Incident time:</td>
                                <td id="incident-time-td">
                                    <span class="incident-time-view">
                                        {{ Carbon\Carbon::parse($incident->incident_date_time)->format('H:i:s') }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_time" style="display: none">
                                        <a href="javascript:void" id="incident-time-edit" class="
                                        incident-time-edit" data-type="combodate" data-pk="{{ $incident->id }}" data-value="{{ Carbon\Carbon::parse($incident->incident_date_time)->format('H:i:s') }}">{{ Carbon\Carbon::parse($incident->incident_date_time)->format('H:i:s') }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Incident date:</td>
                                <td id="incident-date-td">
                                    <span class="incident-date-view">
                                        {{ Carbon\Carbon::parse($incident->incident_date_time)->format('d M Y ') }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_date" style="display: none"><a href="javascript:void" id="incident-date-edit" class="
                                        incident-date-edit" data-type="date" data-pk="{{ $incident->id }}" data-value="{{ Carbon\Carbon::parse($incident->incident_date_time)->format('Y-m-d') }}">{{ Carbon\Carbon::parse($incident->incident_date_time)->format('d M Y ') }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Incident type:</td>
                                <td id="incident-type-td">
                                    <span class="incident-type-view">
                                        {{ $incident->incident_type }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_type" style="display: none">
                                        <a href="javascript:void" id="incident-type-edit" class="
                                        incident-type-edit" data-type="select2" data-pk="{{ $incident->id }}" data-value="{{ $incident->incident_type }}">{{ $incident->incident_type }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>Classification:</td>
                                <td id="incident-classification-td">
                                    <span class="incident-classification-view">
                                        {{ $incident->classification }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_classification" style="display: none">
                                        <a href="javascript:void" id="incident-classification-edit" class="
                                        incident-classification-edit" data-type="select2" data-pk="{{ $incident->id }}" data-value="{{ $incident->classification }}">{{ $incident->classification }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>Insurance informed:</td>
                                <td id="incident-informed-td">
                                    <span class="incident-informed-view">
                                        {{ $incident->is_reported_to_insurance }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_informed" style="display: none">
                                        <a href="javascript:void" id="incident-informed-edit" class="
                                        incident-informed-edit" data-type="select2" data-pk="{{ $incident->id }}" data-value="{{ $incident->is_reported_to_insurance }}">{{ $incident->is_reported_to_insurance }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Incident status: &nbsp;<button class="btn btn-icon-only btn-circle small" data-toggle="modal" data-target="#incident-info-modal"><i class="fa fa-info"></i></button></td>
                                <td id="incident-status-td">
                                    <span class="label incident-status-view label-default {{ $incident->present()->label_class_for_status }} label-results">
                                        {{ $incident->status }}  
                                    </span>
                                    <div class="editable-wrapper" id="incident_status" style="display: none">
                                        <a href="javascript:void" id="incident-status-edit" class="
                                        incident-status-edit" data-type="select2" data-pk="{{ $incident->id }}" data-value="{{ $incident->status }}">{{ $incident->status }}</a>&nbsp;
                                    </div>
                                </td>
                            </tr>                            
                            <tr>
                                <td>Incident allocated to:</td>
                                <td id="incident-allocated-to-td">
                                    <span class="incident-allocated-to-view">
                                        {{ $incident->allocated_to ? $incident->allocated_to : 'N/A' }}
                                    </span>
                                    <div class="editable-wrapper" id="incident_allocated_to" style="display: none">
                                        <a href="javascript:void" id="incident-allocated-to-edit" class="
                                        incident-allocated-to-edit" data-type="select2" data-pk="{{ $incident->id }}" data-value="{{ $incident->allocated_to }}">{{ $incident->allocated_to ? $incident->allocated_to : 'N/A' }}</a>&nbsp;
                                    </div>
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
                        Incident Images
                    </div>
                </div>
                <div class="portlet-body text-center">
                    <div class="row">
                        @if (count($images))
                            @foreach ($images as $image)
                                <div class="col-md-3">
                                    <a class="incident-image" href="{{ asset(getPresignedUrl($image)) }}" data-lightbox="img-incident">
                                        <img src="{{ asset(getPresignedUrl($image)) }}" alt="" class="img-rounded d-block" style="width:100%;">
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12">
                                <div class="no-image-text-box">
                                    <p>No incident images captured.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                        <div>Add New Comment</div>
                    </div>
                </div>
                <div class="portlet-body pt15" id="incidents-comments-drag">
                 {!! BootForm::openHorizontal(['md' => [3, 8]])->id('saveCommentForIncident')->addClass('form-bordered form-validation')->action('/incidents/storeComment')->multipart() !!}
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
                                <input type="file" name="attachment" class="select-file-incident">
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
                            <span class="btn red-rubine btn-file js-new-document-file">
                                <span class="fileinput-new">Select file</span>
                            </span>
                            <button class="fileinput-exists btn grey-gallery remove-file-incident" style="display: none;" data-dismiss="fileinput">Remove</button>
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
                <input type="hidden" id="incident_id" name="incident_id" value="{{ $incident->id }}" />
                <div class="btn-cont" id="incidents-comment-button">
                    <input type="submit" class="btn icn-only red-rubine" value="Save" id="saveComment">
                    </a>
                </div>
            {!! BootForm::close() !!}
        </div>
    </div>
    <div class="portlet light ">
            <div class="portlet-title">
                <div class="caption">
                    <div>Incident History</div>
                </div>
            </div>
            <div class="portlet-body">
                <div class="timeline incident-comments-timeline js-incident-comments">
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
                                            <span class="timeline-body-time">
                                                wrote at {{ $comment->present()->formattedCreatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedCreatedAt()->format('d M Y') }}
                                            </span>
                                            @if ($comment->created_at != $comment->updated_at)
                                            <span class="timeline-body-time"> edited at {{ $comment->present()->formattedUpdatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedUpdatedAt()->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="timeline-body-head-actions">
                                            <div class="">
                                            @if ($comment->created_by == Auth::id())
                                                <button type="button" class="btn red-rubine edit-comment-btn btn-height" style=""><i class="jv-icon jv-edit"></i></button>
                                                <button type="button" data-delete-url="/incidents/delete_comment/{{ $comment->id }}"
                                                    class="btn delete-button grey-gallery btn-height ml0"
                                                    title="Delete comment"
                                                    data-confirm-msg="Are you sure you would like to delete this comment?">
                                                    <i class="jv-icon jv-dustbin"></i>
                                                </button>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body-content">
                                        @if($comment->comments)
                                            @if ($comment->created_by == Auth::id())
                                            <span class="">
                                                <a href="javascript:;" class="comments" data-type="textarea" data-pk="{{ $comment->id }}" data-original-title="Update comment"> {{ $comment->comments }}</a>
                                            </span>
                                            @else
                                                <span class=""> {{ $comment->comments }} </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="timeline-body-content">
                                        @foreach ($comment->getMedia() as $media)
                                            <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                                            <a href="{{ url('/incidents/downloadMedia/' .  $comment->id) }}" class="btn-link">{{ $media->file_name }}</a>
                                            <br><br>
                                            <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-incident">
                                                @if (strpos($media->getCustomProperty('mime-type'), 'image/') === 0)
                                                    <img class="img-rounded" style="max-width: 120px; max-height: 120px;" src="{{ asset(getPresignedUrl($media)) }}" alt="">
                                                @endif
                                            </a>
                                        @endforeach
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
                                            <span class="timeline-body-time">
                                                {{ $comment->comments }} at {{ $comment->present()->formattedCreatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedCreatedAt()->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="timeline-body-content">
                                        @if ($comment->incident_status_comment != NULL)
                                            <span class=""> {{ $comment->incident_status_comment }} </span>
                                        @endif
                                        @foreach ($comment->getMedia() as $media)
                                            <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                                            <a href="{{ url('/incidents/downloadMedia/' .  $comment->id) }}" class="btn-link">{{ $media->file_name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                    @endforeach
                </div>
            </div>
        </div>
    </div>

    </div>

    <div class="modal fade" id="incident-info-modal" tabindex="-1" role="dialog" aria-labelledby="incident-info-modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title" id="incident-info-modal-title">Incident Status Information</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <table class="table" id="incident-info-table">
                        <tr>
                            <td><span class="label label-danger label-results">Reported</span></td>
                            <td>Driver has reported the incident.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-warning label-results">Under investigation</span></td>
                            <td>Incident currently under investigation.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-warning label-results">Allocated</span></td>
                            <td>Incident allocated to be resolved.</td>
                        </tr>
                        <tr>
                            <td><span class="label label-success label-results">Closed</span></td>
                            <td>Incident resolved.</td>
                        </tr>
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

    <!-- Incident status modal-->

    <div class="modal fade" id="incident_status_modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="incident-info-modal-title" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add New Comment</h4>
                </div>
                <form id="incidentStatus" class="form-validation incidentStatus">
                    <div class="modal-body">
                        <div class="form-group" id="incident_comment">
                            <label for="comment" class="control-label">Comment:</label>
                                <textarea class="form-control" rows="5" name="comment" id="comment"></textarea>
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="incidentStatusClose" data-dismiss="modal"> Close</button>
                        <button type="submit" class="btn btn-primary defect_status_modal_comment"
                        id="incidentStatusSave">Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection