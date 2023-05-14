@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
    <script src="//maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}"></script>
@endsection

@section('content')
<div class="page-title-inner">
    <h3 class="page-title">{{ $title }}</h3><br>
</div>
<div class="page-bar">
    {!! Breadcrumbs::render('location_details_add') !!}
</div>
<div class="row">
    {!! BootForm::openHorizontal(['md' => [3, 6]])->addClass('form-bordered form-validation location-form')->action('/locations/store')->id('addLocation') !!}
	<div class="col-md-12 col-lg-12">
		<div class="portlet box">
			<div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Add New Location
                </div>
            </div>
		</div>
        <div>
        	<div class="portlet-body form">
    			{!! BootForm::text('Location name*:', 'name') !!}
                {!! BootForm::text('Address line 1*:', 'address1') !!}
                {!! BootForm::text('Address line 2:', 'address2') !!}
                {!! BootForm::text('Town / City*:', 'town_city') !!}
                <div class="form-group row category--modal">
                    <label class="col-md-3 control-label" for="category_id">Category*:</label>
                    <div class="category--modal-col-55 col-md-6">
                        <div class="d-flex">
                            <div class="flex-grow-1 margin-right-15" style="width: calc(100% - 109px);">
                                <select class="form-control select2me js-category-id" id="category_id" name="category_id">
                                    <option value=""></option>
                                    @foreach($locationCategories as $locationCategory)
                                        <option value="{{ $locationCategory['id'] }}">{{ $locationCategory['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="flex-shrink: 0">
                                <div class="d-flex">
                                    <a href="#add-category" data-toggle="modal" class="btn btn-h-45 red-rubine d-flex align-items-center justify-content-center">
                                        <i class="jv-icon jv-plus"></i>
                                    </a>
                                    <a href="#view-category" id="view_category" data-path="viewAllCategories" data-toggle="modal" class="btn btn-h-45 red-rubine d-flex align-items-center justify-content-center" style="margin-left: 0;">
                                        <i class="jv-icon jv-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 control-label" for="category_id">Postcode*:</label>
                    <div class="col-md-6">
                        <div class="row gutters-tiny">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="postcode" id="postcode">
                                <input type="hidden" class="form-control" name="latitude" id="latitude">
                                <input type="hidden" class="form-control" name="longitude" id="longitude">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn-block align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center js-find-pincode-btn btn-block">Find</button>
                            </div>
                        </div>
                    </div>
                </div>
        	</div>
        </div>
	</div>
</div>
@include('_partials.locations.map', ['from' => $from])
<div class="row">
    <div class="col-md-6 col-md-offset-3 btn-group">
         <a href="/telematics" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
        <button type="submit" class="btn red-rubine btn-padding col-md-6 js-add-location-form" id="submit-button">Save</button>
    </div>
</div>
{!! BootForm::close() !!}
@include('_partials.locations.category_modals')
@endsection

@push('scripts')
<script src="{{ elixir('js/telematics_locations.js') }}" type="text/javascript"></script>
@endpush