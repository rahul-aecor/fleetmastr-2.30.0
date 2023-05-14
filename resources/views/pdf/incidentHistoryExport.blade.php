@extends('layouts.pdf')

@section('pdf_title') 
  Incident History 
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
                    <td><a href="{{ url('/vehicles/' .  $incident->vehicle->id) }}">{{ $incident->vehicle->registration }}</a></td>
                </tr>
                <tr>
                    <td>Category:</td>
                    <td>{{ $incident->vehicle->type->present()->vehicle_category_to_display() }}</td>
                </tr>
                <tr>
                    <td>Type:</td>
                    <td>{{ $incident->vehicle->type->vehicle_type }}</td>
                </tr>
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
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Incident Data</div>
            </div>
            <table class="table table-striped table-summary">
                <tbody>
                <tr>
                    <td>Created by:</td>
                    <td>{{ $incident->creator->first_name }} {{ $incident->creator->last_name }} (<a href="mailto:{{ $incident->creator->email }}" class="font-blue">{{ $incident->creator->email }}</a>)</td>
                </tr>
                <tr>
                    <td>Created date:</td>
                    <td>{{ $incident->present()->formattedCreatedAt() }}</td>
                </tr>
                <tr>
                    <td>Last modified by:</td>
                    <td>{{ $incident->updater->first_name }} {{ $incident->updater->last_name }} (<a href="mailto:{{ $incident->updater->email }}" class="font-blue">{{ $incident->updater->email }}</a>)</td>
                </tr>                
                <tr>
                    <td>Last modified date:</td>
                    <td>{{ $incident->present()->formattedUpdatedAt() }}</td>
                </tr>            
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6"> 
            <div class="portlet-title bg-red-rubine">
                <div class="caption">Incident Details</div>
            </div>
            <table class="table table-striped table-summary" id="incident-details">
                <tbody>
                    <tr>
                        <td>Incident number:</td>
                        <td>{{ $incident->id }}</td>
                    </tr>
                     <tr>
                        <td>Incident time:</td>
                        <td>{{ Carbon\Carbon::parse($incident->incident_date_time)->format('H:i:s') }}</td>
                    </tr>
                     <tr>
                        <td>Incident date:</td>
                        <td>{{ Carbon\Carbon::parse($incident->incident_date_time)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Incident type:</td>
                        <td>{{ $incident->incident_type }}</td>
                    </tr>
                    <tr>
                        <td>Classification:</td>
                        <td>{{ $incident->classification }}</td>
                    </tr>
                    <tr>
                        <td>Incident informed:</td>
                        <td>{{ $incident->is_reported_to_insurance }}</td>
                    </tr>
                    <tr>
                        <td>Incident status:</td>
                        <td>{{ $incident->status }}</td>
                    </tr>
                    <tr>
                        <td>Incident allocated to:</td>
                        <td>{{ $incident->allocated_to ? $incident->allocated_to : 'N/A' }}</td>
                    </tr>                   
                </tbody>
            </table>
        </div>
        
        <div class="col-xs-6">
            <div class="portlet light">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Incident Image</div>
                </div>
                <div class="portlet-body">
                    @if (count($images))
                        <div class="table-striped">
                            <table style="width:99%">
                            @foreach ($images->chunk(4) as $chunk)                           
                                <tr class="table-row">
                                  @foreach ($chunk as $image)
                                      <td class="table-col" style="padding: 5px 5px;">
                                        <a href="{{ asset(getPresignedUrl($image)) }}" data-lightbox="img-incident">
                                            <img src="{{ asset(getPresignedUrl($image)) }}" alt="" class="img-rounded" style="max-width: 100%;max-height: 100px;">
                                        </a>
                                      </td>
                                  @endforeach                          
                                </tr> 
                            @endforeach                                           
                            </table>                                                      
                        </div>
                    @else
                        <div class="no-image-text-box text-center">
                            <p>No incident images captured.</p>    
                        </div>                        
                    @endif
                </div>
            </div>
        </div>
          
    </div>
    <div>
        <div class="portlet-title bg-red-rubine">
            <div class="caption">Incident History</div>
        </div>
        <table class="table table-striped table-summary" id="incident-details">
            <tbody>
                    
        @foreach ($comments as $comment) 
            <tr>
            @if ($comment->type === 'user')
            <td class="media" style="background-color:#F7F7F7;padding:5px;">                
                <div class="media-body">
                    <h6 class="media-heading" style="color:#B71D53">
                    {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                    wrote at {{ $comment->present()->formattedCreatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedCreatedAt()->format('j M Y') }}
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
                        <a href="{{ url('/incidents/downloadMedia/' .  $comment->id) }}" class="btn-link">{{ $media->file_name }}</a>
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
                    {{ $comment->comments }} at {{ $comment->present()->formattedCreatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedCreatedAt()->format('j M Y') }}
                    </h6>
                    @foreach ($comment->getMedia() as $media)
                        <span class="text-muted"> &nbsp;Attachment: </span>&nbsp;
                        <a href="{{ url('/incidents/downloadMedia/' .  $comment->id) }}" class="btn-link">{{ $media->file_name }}</a>
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