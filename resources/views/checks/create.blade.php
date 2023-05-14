@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/cropper.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-ui-1-12-1.css') }}" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/nprogress@0.2.0/nprogress.css" rel="stylesheet" />
@endsection

@section('scripts')
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/cropper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-cropper.js') }}" type="text/javascript"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script src="{{ elixir('js/jquery-ui-1-12-1.js') }}"></script>
    <script src="{{ elixir('js/bundles/checks-create.bundle.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="page-bar">
        {!! Breadcrumbs::render('defect_add') !!}
    </div>
    <div class="row" id="create-checks-page" v-cloak>
        <div class="col-md-12">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Create New Defect
                    </div>                    
                </div>
                <div class="portlet-body form">
                    <div class="form-horizontal form-bordered form-validation form-label-center-fix create-defect-form">
                        <div class="step-1" v-if="currentStep === 1">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="registration">
                                    Registration*:
                                </label>
                                <div class="col-md-6">
                                    <input type="text" name="registration" id="registration" class="form-control" v-model="form.registration">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="odometer-reading">
                                    Odometer*:
                                </label>
                                <div class="col-md-6">
                                    <input type="text" name="odometer-reading" id="odometer-reading" class="form-control" v-model="form.odometer">
                                </div>
                            </div>
                            <div class="form-group" v-if="isTrailerFeatureEnabled == 1">
                                <label class="col-md-3 control-label" for="is_trailer_attached">
                                    Trailer attached*:
                                </label>
                                <div class="col-md-6">
                                    <input type="text" name="is_trailer_attached" id="is_trailer_attached" class="form-control trailer-attached" v-model="form.is_trailer_attached">
                                </div>
                            </div>
                            <div class="form-group js-trailer-reference-number">
                                <label class="col-md-3 control-label" for="trailer_reference_number">
                                    Enter trailer ID*:
                                </label>
                                <div class="col-md-6" :class="{'invalid-field': enterTrailerIdValidationMessage == true }">
                                    <input type="text" name="trailer_reference_number" id="trailer_reference_number" class="form-control" v-model="form.trailer_reference_number" v-on:keyup="trailerIdCheckValidation()">
                                    <span class="has-error" v-if="enterTrailerIdValidationMessage == true">Please enter only letter and numeric characters</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3"></div>
                                <div class="col-md-9 btn-group">
                                    <button type="button" class="btn white-btn btn-padding col-md-4" v-on:click="defectCancelButton()">Cancel</button>     
                                    <button type="button" class="btn red-rubine btn-padding col-md-4" v-on:click="verifyOdometer" v-bind:class="{ 'disabled': (form.odometer == '' || form.registration == '') || (isTrailerFeatureEnabled == '1' && !isTrailerValueCheck) }">Confirm</button>
                                </div>
                            </div>
                        </div>
                        <div class="step-2 clearfix" v-if="currentStep === 2">
                            <existing-defect-list v-bind:selected-vehicle-information="selectedVehicleInformation"></existing-defect-list>
                            <div class="portlet box col-md-8 col-md-offset-2">
                                <div class="portlet-title bg-red-rubine">
                                    <div class="caption">Vehicle Summary</div>
                                    <!-- <button type="button" class="btn red-rubine btn-padding submit-button col-md-12" >
                                        view
                                    </button> -->
                                    <span class="caption pull-right"  v-if="selectedVehicleInformation.hasDefects"><font class="blue-clr">This vehicle has existing defects – </font><a v-on:click="showExistingDefects()" style="text-decoration: underline"><font class="blue-clr">view</font></a></span>
                                </div>
                                <div class="portlet-body form">
                                    <table class="table table-striped table-hover table-summary">
                                        <tbody>                        
                                            <tr>
                                                <td>Registration:</td>
                                                <td>@{{ selectedVehicleInformation.registration }}</td>
                                            </tr>
                                            <tr>
                                                <td>Type:</td>
                                                <td>@{{ selectedVehicleInformation.type }}</td>
                                            </tr>
                                            <tr>
                                                <td>Manufacturer:</td>
                                                <td>@{{ selectedVehicleInformation.manufacturer }}</td>
                                            </tr>
                                            <tr>
                                                <td>Model:</td>
                                                <td>@{{ selectedVehicleInformation.model }}</td>
                                            </tr>
                                            <tr>
                                                <td>Last checked:</td>
                                                <td>@{{ selectedVehicleInformation.lastChecked }}</td>
                                            </tr>
                                            <tr>
                                                <td>Status:</td>
                                                <td>@{{{ selectedVehicleInformation.status | checkStatusFormatter }}}</td>
                                                <!-- <td>@{{ selectedVehicleInformation.status }}</td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="defect-vehicle-info">
                                        <div class="btn-group w-100">
                                            <button type="button" class="btn white-btn btn-padding col-md-6" v-on:click="defectCancelButton()">Cancel</button>     
                                            <button type="button" class="btn red-rubine btn-padding col-md-6" v-on:click="confirmVehicleDetails">Confirm</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="step-3 clearfix" v-if="currentStep === 3">
                            <div class="row">
                                <div class="col-md-12" v-if="selectedVehicleInformation.hasDefects">
                                    Any pre-existing vehicle defects are highlighted below in red.
                                    <span class="col-md-12">&nbsp;</span>
                                </div>                                    
                                <div class="col-md-12">                                    
                                    <template v-for="screen in surveyJson.screens.screen">
                                        <check-type-list 
                                            v-if="screen._type=='list'"
                                            :screen="screen"
                                            :existing-defect-list-parsed="existingDefectListParsed"
                                            :edit="edit"
                                            :new-defect="imageUrlString"
                                            :is-trailer-attached="form.is_trailer_attached">
                                        </check-type-list>
                                    </template>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3"></div>
                                <div class="col-md-9 btn-group">
                                    <button type="button" class="btn white-btn btn-padding col-md-4" v-on:click="defectCancelButton()">Cancel</button>
                                    <button type="button" class="btn red-rubine btn-padding submit-button col-md-4" v-on:click="createCheck" v-bind:class="{ disabled: !validCheck }">Save</button>
                                </div>
                            </div>                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal default-modal modal-scroll fade" tabindex="-1" role="dialog" id="edit-defect-modal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                        <h4 class="modal-title">Add Defect</h4>
                        <a v-bind:class="{'hide': uploadImageInProgress }"  class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                        </a>
                    </div>

                    <div class="modal-body">
                        <form action="#" class="form-horizontal form-bordered form-row-stripped" novalidate="novalidate">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="text-right col-md-4">Defect category:</div>
                                    <div class="col-md-8">
                                        @{{ selectedDefectCategory }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="text-right col-md-4">Defect:</div>
                                    <div class="col-md-8">
                                        @{{ updatedDefect.text }}
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="control-label col-md-4">Update defect status:</label>
                                    <div class="col-md-8">
                                        <button type="button" class="btn" 
                                            v-bind:class="updatedDefect.selected === 'yes' ? 'red-rubine' : 'btn-default'"
                                            v-on:click="updatedDefect.selected = 'yes'">
                                            Defect
                                        </button>
                                        <button type="button" class="btn" 
                                            v-bind:class="updatedDefect.selected === 'no' ? 'red-rubine' : 'btn-default'"
                                            v-on:click="noDefectSelected()">
                                            No defect
                                        </button>
                                    </div>
                                </div>                                 
                                <div class="form-group" v-show="updatedDefect.selected === 'yes'">
                                    <label class="control-label col-md-4">Select an image: </label>-->
                                <div v-for="(index, image) in imageData" v-show="imageRequired">
                                    <div class="form-group">
                                        <label class="control-label col-md-4" v-if="imageData[index].imageString == '' && index > 0">Add another image: </label>
                                        <label class="control-label col-md-4" v-else>Select an image:* </label>
                                        <div class="col-md-8">                                     
                                            <button type="button" 
                                                class="btn btn-default btn-default custom-upload-btn"
                                                v-bind:class="{'disabled': uploadImageInProgress }"
                                                v-on:click="defectCustomUploadClicked(index)"
                                                v-show="imageData[index].imageString">
                                                Change image
                                            </button>
                                            <button type="button" 
                                                class="btn red-rubine"
                                                v-bind:class="{'disabled': uploadImageInProgress }"
                                                v-on:click="removeImage(index)"
                                                v-show="imageData[index].imageString">
                                                Delete
                                            </button>
                                            <button type="button" 
                                                class="btn btn-default btn-default custom-upload-btn"
                                                v-on:click="defectCustomUploadClicked(index)"
                                                v-show="!imageData[index].imageString">
                                                Upload image
                                            </button>
                                            <div class="inline-help text-error" v-show="updatedDefect.isInvalid">
                                                Image is mandatory.
                                            </div>
                                            <input type='file' class="image-upload-btn-@{{ index }}" v-on:change="handleImageChange(updatedDefect, $event, index)" style="display: none" accept="image/*" />
                                        </div>
                                    </div>
                                    <div class="form-group" v-show="imageData[index].imageString">
                                        <label class="control-label col-md-4">&nbsp;</label>
                                        <div class="col-md-4">
                                            <img
                                                v-bind:src="imageData[index].imageString"
                                                class="img-rounded image-@{{ index }}" 
                                                style="max-height:100px; max-width: 100%;">
                                        </div>
                                        
                                        <div class="col-md-4 docs-buttons">
                                            <button type="button" id="rotate0" class="btn red-rubine" title="Rotate Left" v-on:click="rotateImage('-90', index)">
                                                <span class="fa fa-rotate-left"></span>
                                            </button>
                                            <button type="button" id="rotate1" class="btn red-rubine" title="Rotate Right" v-on:click="rotateImage('90', index)">
                                                <span class="fa fa-rotate-right"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="form-group">
                                    <label class="control-label col-md-4">Comments:</label>
                                    <div class="col-md-8">
                                        <textarea rows="4" class="form-control" v-model="updatedDefect.comments"></textarea>
                                    </div>
                                </div> -->
                            </div>                            
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button"
                                    class="btn white-btn btn-padding col-md-6"
                                    v-bind:class="{'disabled': uploadImageInProgress }"    
                                    data-dismiss="modal">Cancel</button>
                            <button type="button" 
                                class="btn red-rubine btn-padding col-md-6 "
                                v-bind:class="{disabled: (updatedDefectIsInvalid || uploadImageInProgress), 'is-loading': uploadImageInProgress}"
                                v-on:click="createDefect">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal default-modal modal-scroll fade" tabindex="-1" role="dialog" id="vehicle-check-status" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                        <h4 class="modal-title">Confirm Vehicle Status</h4>
                        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <form action="#" class="form-horizontal form-bordered form-row-stripped" novalidate="novalidate">
                            <div class="form-body">
                                <p>Choose an option below.</p>
                            </div>                            
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button"
                                class="btn btn-padding col-md-6 red-btn"
                                data-dismiss="modal" v-on:click="changeVehicleStatusAndCreate('UnsafeToOperate')">Unsafe to operate
                            </button>                            
                            <button type="button" 
                                class="btn btn-padding col-md-6 green-btn"
                                v-on:click="changeVehicleStatusAndCreate('SafeToOperate')" data-dismiss="modal">Safe to operate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    

@endsection