@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
@endsection
@section('content')
<div class="page-title-inner">
    <h3 class="page-title">{{ $title }}</h3><br>
</div>
<div class="page-bar">
    {!! Breadcrumbs::render('profile_details_add') !!}
</div>
<?php
    $columnSizes = [
      'md' => [4, 8]
    ];
    $url= '/profiles';
?>
    
<div class="row">
	{!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation form')->id('addType')->action($url)->multipart()->post() !!}
    <input type="hidden" name="fromPage" class="fromPage" value={{$fromPage}}>
	<div class="col-md-7 col-lg-8">
		<div class="portlet box">
			<div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Add Vehicle Profile
                </div>
            </div>
            <div class="">
            	<div class="portlet-body form">
            		<div class="form-body form-bordered form-label-center-fix form-add-vehicle-profile">
                        <div class="alert alert-danger display-hide  bg-red-rubine">
                            <button class="close" data-close="alert"></button>
                            <!-- You have some form errors. Please check below. -->
                            Please complete the errors highlighted below.
                        </div>
                        <div class="alert alert-success display-hide">
                            <button class="close" data-close="alert"></button>
                            Your form validation is successful!
                        </div>
                        {!! BootForm::text('Type*:', 'vehicle_type') !!}
                        {!! BootForm::select('Category*:', 'vehicle_category')->options($vehicleCategoryList)->addClass('select2') !!}
                        {!! BootForm::select('Sub category*:', 'vehicle_subcategory')->options($vehicleSubCategoriesNonHGV)->addClass('select2') !!}
                        {!! BootForm::select('Odometer setting*:', 'odometer_setting')->options(config('config-variables.vehicle_type_odometer_setting'))->addClass('select2me') !!}
                        {!! BootForm::select('Usage*:', 'usage_type')->options($usageTypeList)->addClass('select2') !!}
                        {!! BootForm::text('Manufacturer*:', 'manufacturer') !!}                       
                        {!! BootForm::text('Model*:', 'model') !!}
                        {!! BootForm::text('Bodybuilder:', 'body_builder') !!}
                        {!! BootForm::text('Gross vehicle weight:', 'gross_vehicle_weight') !!}
                        {!! BootForm::text('Tyre size drive:', 'tyre_size_drive') !!}
                        {!! BootForm::text('Tyre size steer:', 'tyre_size_steer') !!}
                        {!! BootForm::text('Type pressure drive:', 'tyre_pressure_drive') !!}
                        {!! BootForm::text('Type pressure steer:', 'tyre_pressure_steer') !!}
                        {!! BootForm::text('Nut size:', 'nut_size') !!}
                        {!! BootForm::text('Re-torque:', 're_torque') !!}
                        {!! BootForm::text('Length (mm):', 'length') !!}
                        {!! BootForm::text('Width (mm):', 'width') !!}
                        {!! BootForm::text('Height (mm):', 'height') !!}
                        {!! BootForm::select('Fuel type*:', 'fuel_type')->options($fuelTypeList)->addClass('select2') !!}
                        {!! BootForm::select('Type of engine*:', 'engine_type')->options($engineTypeList)->addClass('select2')->placeholder('Select') !!}
                        {{-- {!! BootForm::text('Engine size (cc)*:', 'engine_size') !!} --}}
                        <div class="form-group">
                            <input type="hidden" id="engineSizeMandatoryFlag" value="{{ $engineSizeMandatoryFlag }}">
                            <label class="col-md-4 control-label" for="engine_size">Engine size (cc):</label>
                            <div class="col-md-8">
                                <input type="text" name="engine_size" id="engine_size" class="form-control">
                            </div>
                        </div>
                        {!! BootForm::select('Oil grade:', 'oil_grade')->options($oilGrade)->addClass('select2')  !!}
                        {!! BootForm::text('CO2 (g/km):', 'co2')!!}                         
                        <span class="co2_profile_type" id="errmsg"></span>
                        
                        <div class="form-group d-flex align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center h-100">
                                    <label for="vehicle_insurance_cost" class="control-label align-self-center pt-0 w-100">Monthly insurance cost per vehicle:</label>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon">&pound;</span>
                                    <input type="text" name="vehicle_insurance_cost" id="vehicle_insurance_cost" readonly class="form-control vehicle_tax_cost" value="{{ isset($currentMonthVehicleTaxCost) != 0 ?number_format($currentMonthVehicleTaxCost,2): ''}} ">
                                </div>
                            </div>
                            <div class="col-md-1 align-items-center d-flex justify-content-center">
                                <input type="hidden" name="saveInsuranceCostFlag" class="saveInsuranceCostFlag"/>
                                <input type="hidden" name="monthly_vehicle_insurance" class="monthly_vehicle_insurance"/>
                                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_vehicle_insurance_cost" href="#monthly_vehicle_insurance_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                            </div>
                        </div>

                        <div class="form-group d-flex align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center h-100">
                                    <label for="vehicle_tax_cost" class="control-label align-self-center pt-0 w-100">Monthly vehicle tax:</label>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon">&pound;</span>
                                    <input type="text" name="vehicle_tax_cost" id="vehicle_tax_cost" readonly class="form-control vehicle_tax_cost" value="{{ isset($currentMonthVehicleTaxCost) != 0 ?number_format($currentMonthVehicleTaxCost,2): ''}} ">
                                </div>
                            </div>
                            <div class="col-md-1 align-items-center d-flex justify-content-center">
                                <input type="hidden" name="saveMonthlyCostFlag" class="saveMonthlyCostFlag"/>
                                <input type="hidden" name="monthly_vehicle_tax" class="monthly_vehicle_tax"/>
                                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_vehicle_tax_cost" href="#monthly_vehicle_tax_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                            </div>
                        </div>
                        
                        {!! BootForm::select('ADR test interval:', 'adr_test_date')
                            ->options(config('config-variables.adr_test'))->addClass('select2') !!}
                        {!! BootForm::select('Compressor service interval:', 'compressor_service_interval')->options(config('config-variables.compressorServiceInterval'))->addClass('select2')  !!}
                        {!! BootForm::select('Invertor service interval:', 'invertor_service_interval')->options(config('config-variables.invertorServiceInterval'))->addClass('select2')  !!}
                        {!! BootForm::select('LOLER test interval:', 'loler_test_interval')->options(config('config-variables.loler_test_interval'))->addClass('select2')  !!}
                        <div class="js-pmi-interval"> 
                            {!! BootForm::select('PMI interval:', 'pmi_interval')->options(config('config-variables.pmiIntervalService'))->addClass('select2')  !!}
                        </div>
                        

                        {!! BootForm::select('PTO service interval:', 'pto_service_interval')->options(config('config-variables.ptoServiceInterval'))->addClass('select2')  !!}
                        
                        {!! BootForm::select('Service interval type:', 'service_interval_type')
                            ->options(config('config-variables.service_inspection_type'))->addClass('select2') !!}

                        <div class="form-group js-service-interval hide">
                            <label class="col-md-4 control-label" for="service_inspection_interval">Service interval:</label><div class="col-md-8">
                                <select class="form-control select2" id="service_inspection_interval" name="service_inspection_interval" placeholder="Select">
                                    @foreach (config('config-variables.serviceInspection') as $key => $value)
                                        <option value="{{ $key }}">{{ $value}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {!! BootForm::select('Tank test interval:', 'tank_test_interval')
                            ->options(config('config-variables.tank_test_interval'))->addClass('select2') !!}

                        <input type="hidden" name="vehicle_tax" id="vehicle_tax" value="[]">
                        <input type="hidden" name="vehicle_insurance" id="vehicle_insurance" value="[]">
            		</div>
            	</div>
            </div>
            <div class="form-actions row">
                <div class="col-md-12 btn-group">
                     <a href="{{ $url }}" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
                    <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="submit-button">Save</button>
               </div>
            </div>
		</div>
	</div>
	<div class="col-md-5 col-lg-4">
		<div class="portlet box vehicle--profile" style="margin-bottom: 0px;">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Add Vehicle Profile Images
                </div>
            </div>
            <style type="text/css">
                .bottom-pad{
                    padding-bottom: 10px;
                }
                .img-caption{
                    font-size: 13px;
                }
            </style>
            <div class="modal-body">
            	<div class="portlet-body form">
            		<div class="form-body">
                        @if(!empty($medialist))
                			@foreach ($medialist as $key => $media)
                			<div class="input-cont form-group">
                                <div class="row" style="border-bottom: solid 1px silver;">
                                    <div class="col-md-12 text-center bottom-pad img-caption">{{ ucfirst(strtolower($key)) }}</div>
                                    <div class="col-md-12">
                                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                          <div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div>
                                          <span class="input-group-addon btn btn-file grey-gallery"><span class="fileinput-new">Select file</span><span class="fileinput-exists">Change</span><input type="file" name="{{ $media->for }}" style="height: 45px;"></span>
                                          <a href="#" class="input-group-addon btn grey-gallery fileinput-exists" data-dismiss="fileinput">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
            		</div>
            	</div>
            </div>
		</div>
        <div class="portlet box defect-list" style="margin-bottom: 0px;">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Take Out / Return Defects
                </div>
            </div>
            <div class="portlet-body form">
                <div class="form-body">
                    <div class="row">
                        @foreach ($defectMasterList as $defect)
                            <label class="col-md-12">
                                <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked" readonly="readonly" 
                                 >{{ $defect['page_title'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="portlet box defect-list">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Ad-hoc Defects
                </div>
            </div>
            <div class="portlet-body form">
                <div class="form-body">
                    <div class="row">
                        @foreach ($defectMasterDefectsOnlyList as $defect)
                            <label class="col-md-12">
                                <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked" readonly="readonly" 
                                 >{{ $defect['page_title'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
	</div>
	{!! BootForm::close() !!}

</div>
    <div id="vehicle_tax_add_modal" class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false" data-backdrop="static">
        <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title annual-vehicle-tax">Add Annual Vehicle Tax</h4>
            <a class="font-red-rubine annualVehicleTaxCancle" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
            </a>
        </div>
        <div class="modal-body">
            {!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation vehicle-tax-form')->action('vehicle_tax/add')->id('addVehicleTaxYear') !!}
            <div class="form-group d-flex align-items-center">
                <label class="control-label col-md-3 pt-0">Tax year*:</label>
                <div class="col-md-9">
                    <select class="form-control select2me" id="tax_year_to_add" name="tax_year_to_add">
                    <option value="">Select</option>
                        @foreach ($taxYearList as $key => $taxYear)
                            <?php
                                $taxYear = explode('-', $taxYear);
                                $taxYearValue = substr($taxYear[1], 2);
                                $taxYearFormatValue = $taxYear[0] . '-' . $taxYearValue; 
                            ?>
                            <option value="{{ $taxYearFormatValue }}">{{ $taxYearFormatValue }} {{ $currentYearFormat == $taxYearFormatValue ? ' (current)' : ''}}</option>
                        @endforeach 
                   </select>
                </div>   
            </div> 
            <div class="form-group d-flex align-items-center">
                <label class="control-label col-md-3 pt-0">Tax value*:</label>
                <div class="col-md-9">
                    <input type="text" id="tax_val" name="tax_val" class="form-control" required="true" />
                </div> 
            </div> 
           
            {!! BootForm::close() !!}
        </div>
        <div class="modal-footer">
            <div class="col-md-offset-2 col-md-8 ">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6 annualVehicleTaxCancle" data-dismiss="modal">Cancel</button>
                    <button type="button" id="addVehicleTaxYearConfirm" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                </div>
            </div>
        </div>
    </div>

<!--     <div class="modal fade default-modal annual_vehicle_tax_add_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
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
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button id="" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button id="annualCostDeleteConfirm" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>            
                    </div>
                </div>
            </div>
            <input type="hidden" id="annual_vehicle_tax_add_modal_hidden_value">
        </div>
    </div> -->
    @include('_partials.vehicle_types.vehicle_tax')
    @include('_partials.vehicle_types.vehicle_insurance')
@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/vehicle_insurance.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
@endpush
