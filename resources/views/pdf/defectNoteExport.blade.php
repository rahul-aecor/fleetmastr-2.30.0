@extends('layouts.pdf')

@section('pdf_title') 
  Vehicle Defect Note
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
    {{-- <div class="row">
      <div class="col-xs-12">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">Vehicle Check</div>
        </div>
        </div>
        @foreach ($defectMasterData->chunk((ceil($defectMasterData->count() + 1 )/ 2)) as $chunk)
        <div class="col-xs-6">
            <ul class="list-group defect_note_ul">
            @foreach ($chunk as $row)
                @if ($row->page_title === $defect->defectMaster->page_title)
                    <li class="list-group-item text-danger">
                        <strong>
                            {{ $row->page_title }}
                            <span class="pull-right">&#10006;</span>    
                        </strong>                        
                    </li>
                @else
                    <li class="list-group-item">
                        {{ $row->page_title }}
                    </li>
                @endif                
            @endforeach
            </ul>
          </div>
          @endforeach
        
    </div> --}}
    <div class="row">
        <div class="col-xs-6">
            <div class="portlet-title bg-red-rubine">
              <div class="caption">Defect Details</div>
            </div>
            <table class="table table-striped table-summary">
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
              </tr>
              <tr>
                  <td>Estimated defect cost &pound;:</td>
                  <td>{{ $defect->estimated_defect_cost_value ? $defect->estimated_defect_cost_value : 0 }}</td>
              </tr> 
              <tr>
                  <td>Actual defect cost &pound;:</td>
                  <td>{{ $defect->actual_defect_cost_value ? $defect->actual_defect_cost_value : 0 }}</td>
              </tr>
              <tr>
                  <td>Days VOR:</td>
                  <td>{{ $vorDay }}</td>
              </tr>
              <tr>
                  <td>VOR Cost &pound;:</td>
                  <td>{{ number_format($vorCostPerDay) }}</td>
              </tr>     
           </table> 
        </div>

        <div class="col-xs-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine no-border">
                    <div class="caption">Defect Image</div>
                </div>
                <div class="portlet-body text-center">
                    @if (count($images))
                        <div class="table-striped">
                          <table style="width:99%">  
                          @foreach ($images->chunk(4) as $chunk)                          
                            <tr class="table-row">
                              @foreach ($chunk as $image)
                                  <td class="table-col" style="padding: 5px 5px;">
                                    <a href="{{ asset(getPresignedUrl($image)) }}" data-lightbox="img-defect">
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
                            <p>Image capture not mandatory for this defect.</p>    
                        </div>                        
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="portlet-title bg-red-rubine">
              <div class="caption">Company Notes</div>
            </div>
            <div class="portlet-body">
              <div class="no-image-text-box text-center">                
                <form>
                    <textarea name="notes" style="width: 100%; height: 180px; border: none; font-size: 9px; color: #888;">
                      
                    </textarea>
                </form>
              </div>                        
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-12">
            <div class="portlet-title bg-red-rubine">
              <div class="caption">Garage Notes</div>
            </div>
            <div class="portlet-body">
              <div class="no-image-text-box" style="height: 180px; width: 100%">                                
              </div>                        
            </div>
        </div>
    </div> 
    <br>   
    <div class="row">
        <div class="col-xs-12">
            <div class="portlet-title bg-red-rubine">
              <div class="caption">Garage Signature</div>
            </div>
            <div class="portlet-body">
              <div class="no-image-text-box" style="height: 120px; width: 100%">                                
              </div>                        
            </div>
        </div>
    </div>    
@endsection