@extends('layouts.pdf')

@section('pdf_title') 
  Vehicle Defect History 
@endsection

@section('content')
    <div class="row" style="margin-top: 1px;">
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Vehicle Summary</div>
            </div>
            <table class="table table-striped table-summary">
                <tbody>                
                <tr>
                    <td>Registration:</td>
                    <td><a href="{{ url('/vehicles/' .  $defect->vehicle->id) }}">{{ $defect->vehicle->registration }}</a></td>
                </tr>
                <tr>
                    <td>Date added to fleet:</td>
                    <td>{{ $defect->vehicle->dt_added_to_fleet }}</td>
                </tr>
                <tr>
                    <td>Category:</td>
                    <td>{{ $defect->vehicle->type->present()->vehicle_category_to_display() }}</td>
                </tr>
                <tr>
                    <td>Type:</td>
                    <td>{{ $defect->vehicle->type->vehicle_type }}</td>
                </tr>
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
                        <span class="label vehicle-status-view {{ $defect->vehicle->present()->label_class_for_status }} label-results" >  {{ $defect->vehicle->status }}
                        </span> 
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Defect Data</div>
            </div>
            <table class="table table-striped table-summary">
                <tbody>
                <tr>
                    <td>Created by:</td>
                    <td>{{ $defect->creator->first_name }} {{ $defect->creator->last_name }} (<a href="mailto:{{ $defect->creator->email }}" class="font-blue">{{ $defect->creator->email }}</a>)</td>
                </tr>
                <tr>
                    <td>Created date:</td>
                    <td>{{ $defect->present()->formattedReportDatetime() }}</td>
                </tr>
                <tr>
                    <td>Last modified by:</td>
                    <td>{{ $defect->updater->first_name }} {{ $defect->updater->last_name }} (<a href="mailto:{{ $defect->updater->email }}" class="font-blue">{{ $defect->updater->email }}</a>)</td>
                </tr>                
                <tr>
                    <td>Last modified date:</td>
                    <td>{{ $defect->present()->formattedUpdatedAt() }}</td>
                </tr>  
                <tr>
                    <td>Check:</td>
                    <td>{{ $defect->check->present()->types_to_display() }}</td>
                </tr>              
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6"> 
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Defect Details</div>
            </div>
            <table class="table table-striped table-summary" id="defect-details">
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
                        </td>
                    </tr>
                    <tr>
                        <td>Defect status: &nbsp;</td>
                        <td id="defect-status-td">
                            <span class="label defect-status-view label-default {{ $defect->present()->label_class_for_status }} label-results" >
                                {{ $defect->status }}
                            </span>
                            <div class="editable-wrapper" style="display: none">
                                <a href="#" class="defect-status-edit" data-type="select2" data-pk="{{ $defect->id }}" data-value="{{ $defect->status }}">{{ $defect->status }}</a>&nbsp;
                            </div>
                        </td>
                    </tr>
                    <tr <?php if($defect->status != 'Repair rejected') {?> style="display: none;" <?php } ?> >
                        <td>Reject reason:</td>
                        <td>{{ $defect->rejectreason }}  <?php if (empty($defect->rejectreason)) { echo "N/A";}?></td>                        
                    </tr>
                    <tr>
                        <td>Defect allocated to:</td>
                        <td>
                            <?php
                            $workshopShow = "";
                            foreach ($workshops as $key => $value) {
                                $value = json_decode($value);
                                if ($value->value == $defect->workshop) {
                                    $workshopShow = $value->text;
                                }
                            }
                            ?>
                            {{ $workshopShow }}  <?php if (empty($defect->workshop)) { echo "N/A";}?>
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
                        </td>
                    </tr>
                    @if(config('branding.name') != "clh")
                    <tr>
                        <td>Defect invoice date:</td>
                        <td>
                          @if($defect->invoice_date == null)
                              N/A
                          @else
                              {{ $defect->invoice_date }}
                          @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Defect invoice number:</td>
                        <td>
                          @if($defect->invoice_number == null)
                            N/A
                          @else
                              {{ $defect->invoice_number }}
                          @endif
                        </td>
                    </tr> 
                    @endif
                    <tr>
                        <td>Estimated defect cost &pound;:</td>
                        <td>{{ $defect->estimated_defect_cost_value ? $defect->estimated_defect_cost_value : 0 }}</td>
                    </tr> 
                    <tr>
                        <td>Actual defect cost &pound;:</td>
                        <td>{{ $defect->actual_defect_cost_value ? $defect->actual_defect_cost_value : 0 }}</td>
                    </tr> 
                    <tr>
                    <tr>
                        <td>Days VOR:</td>
                        <td>{{ $vorDay }}</td>
                    </tr>
                    <tr>
                        <td>VOR Cost &pound;:</td>
                        <td>{{ number_format($vorCostPerDay) }}</td>
                    </tr>                        
                </tbody>
            </table>
        </div>
        
        <div class="col-xs-6">
            <div class="portlet light">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Defect Image</div>
                </div>
                <div class="portlet-body">
                    @if (count($images))
                        <div class="table-striped">
                            <table style="width:99%">
                            @foreach ($images->chunk(4) as $chunk)                           
                                <tr class="table-row">
                                  @foreach ($chunk as $media)
                                      <td class="table-col" style="padding: 5px 5px;">
                                        <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-defect">
                                            <img src="{{ asset(getPresignedUrl($media)) }}" alt="" class="img-rounded" style="max-width: 100%;max-height: 100px;">
                                        </a>
                                      </td>
                                  @endforeach                          
                                </tr> 
                            @endforeach                                           
                            </table>                                                      
                        </div>
                    @else
                        <div class="no-image-text-box text-center">
                            <p>Image capture not mandatory for this defect.</p>    
                        </div>                        
                    @endif
                </div>
            </div>
        </div>
          
    </div>
    <div>
        <div class="portlet-title bg-red-rubine">
            <div class="caption">Defect History</div>
        </div>
        <table class="table table-striped table-summary" id="defect-details">
            <tbody>
                    
        @foreach ($comments as $comment) 
            <tr>
            @if ($comment->type === 'user')
            <td class="media" style="background-color:#F7F7F7;padding:5px;">                
                <div class="media-body">
                    <h6 class="media-heading" style="color:#B71D53">
                    {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                    wrote at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('j M Y') }}
                    @if ($comment->created_at != $comment->updated_at)
                    <span class="timeline-body-time"> edited at {{ $comment->present()->formattedUpdatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedUpdatedAt()->format('j M Y') }}</span>
                    @endif
                    </h6>
                    <div class="clearfix">
                        @if ($comment->created_by == Auth::id())
                        <span class="">
                            {{ $comment->comments }} 
                        </span>
                        @else
                            <span class=""> {{ $comment->comments }} </span>
                        @endif
                    </div>
                    <div>
                        @foreach ($comment->getMedia() as $media)
                        <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                        <br><br>
                        <a href="{{ asset(getPresignedUrl($media)) }}">
                            @if (strpos($media->getCustomProperty('mime-type'), 'image/') === 0)
                                <img class="img-rounded" style="max-width: 120px; max-height: 120px;" src="{{ asset(getPresignedUrl($media)) }}" alt="">
                            @endif
                        </a>
                    @endforeach
                    </div>
                    
                </div>
            </td>                
            @else 
            <td class="media" style="background-color:#F7F7F7;padding:5px;">                
                <div class="media-body">
                    <h6 class="media-heading" style="color:#B71D53">
                    {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                    {{ $comment->comments }} at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('j M Y') }}
                    </h6>
                    @foreach ($comment->getMedia() as $media)
                        <span class="text-muted"> &nbsp;Attachment: </span>&nbsp;
                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                    @endforeach
                </div>
            </td>                
            @endif
            </tr>
        
        @endforeach
            </tbody>
            </table>
    </div>

@endsection