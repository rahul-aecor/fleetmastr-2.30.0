@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bundles/checks.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/exif/exif.js') }}" type="text/javascript"></script>
    @if((isset($check->location)) && ($check->type == 'Vehicle Check On-call' || $check->type == 'Vehicle Check'))
        <script src="{{ elixir('js/checklocation.js') }}" type="text/javascript"></script>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&callback=initMap"> </script>
    @endif
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div id="checks-page">
        <div class="page-bar">
            {!! Breadcrumbs::render('check_details') !!}
            <div class="page-toolbar">
                <button class="btn btn-plain"
                        v-on:click="toggleEditMode"
                        v-bind:class="{'red-rubine': edit}">
                    <i class="jv-icon jv-edit"></i> Edit vehicle check
                </button>
                <a class="btn btn-plain hidden-print" href="{{ url('checks/exportPdf/' . $check->id) }}">
                    <i class="jv-icon jv-download"></i> Export vehicle check
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <vehiclesummary :vehicle="check.vehicle" :status="check.status" :count="checkJson.total_defect" :check="check" :duration="vorDuration"></vehiclesummary>
            </div>
            <div class="col-sm-6">
                <checksummary :check="check"></checksummary>                
            </div>
        </div>
        @if((isset($check->location)) && ($check->type == 'Vehicle Check On-call' || $check->type == 'Vehicle Check'))
        <div class="row">
            <div class="col-sm-12">
                <div class="portlet box">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">Check Location</div>
                    </div>
                    <div class="portlet-body form">
                        <div id="checklocation" class="gmaps"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <template v-for="screen in checkJson.screens.screen">
                    <check-type-list 
                        v-if="screen._type=='list'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </check-type-list>
                    <yesno-type-list 
                        v-if="screen._type=='yesno'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </yesno-type-list>
                    <media 
                        v-if="screen._type=='media'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </media>
                    <media-based-on-selection
                        v-if="screen._type=='media_based_on_selection'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </media-based-on-selection>
                    <dropdown
                        v-if="screen._type=='dropdown'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </dropdown>
                    <multi-input
                        v-if="screen._type=='multiinput'"
                        :is_trailer_attached="check.is_trailer_attached"
                        :screen="screen"
                        :edit="edit">
                    </multi-input>
                    <multiselect-type-list 
                        v-if="screen._type=='multiselect'"
                        :screen="screen"
                        :edit="edit">
                    </multiselect-type-list>
                    <declaration 
                        v-if="screen._type=='declaration'"
                        :screen="screen"
                        :edit="edit">
                    </declaration>
                </template>
            </div>            
        </div>
        
        {{-- <div class="form-actions" v-show="edit">
            <div class="row">
                <div class="col-md-offset-3 col-md-9">
                    <button type="button" 
                        class="btn green"
                        v-on:click="submitDefectDetails">
                        <i class="fa fa-save"></i> 
                        Save Changes
                    </button>
                    <button type="button" class="btn btn-danger">Cancel</button>
                </div>
            </div>                
        </div> --}}


    <div class="row">
        <div class="modal default-modal modal-scroll fade" tabindex="-1" role="dialog" id="edit-modal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-red-rubine">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Edit Defect</h4>
                    </div>
                    <div class="modal-body">
                        <form action="#" class="form-horizontal form-bordered form-row-stripped" novalidate="novalidate">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Defect category:</label>
                                    <div class="col-md-8">
                                        <div class="form-control-static">
                                            @{{ selectedDefectCategory }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">Defect:</label>
                                    <div class="col-md-8">
                                        <div class="form-control-static">
                                            @{{ updatedDefect.text }}                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">Update defect status:</label>
                                    <div class="col-md-8">
                                        <button type="button" class="btn" 
                                            v-bind:class="updatedDefect.selected === 'yes' ? 'red-rubine' : 'btn-default'"
                                            v-on:click="updateDefectStatus('yes')">
                                            Defect
                                        </button>
                                        <button type="button" class="btn" 
                                            v-bind:class="updatedDefect.selected === 'no' ? 'red-rubine' : 'btn-default'"
                                            v-on:click="updateDefectStatus('no')">
                                            No defect
                                        </button>
                                    </div>
                                </div>                                
                                <div class="form-group" v-show="updatedDefect.selected === 'yes'">
                                    <label class="control-label col-md-4">Select an image:</label>
                                    <div class="col-md-8">                                        
                                        <button type="button" 
                                            class="btn btn-default btn-default custom-upload-btn"
                                            v-show="updatedDefect.imageString">
                                            Change image
                                        </button>
                                        <button type="button" 
                                            class="btn red-rubine"
                                            v-on:click="imageRemoved"
                                            v-show="updatedDefect.imageString">
                                            Delete
                                        </button>
                                        <button type="button" 
                                            class="btn btn-default btn-default custom-upload-btn"
                                            v-show="! updatedDefect.imageString">
                                            Upload image
                                        </button>
                                        <div class="inline-help text-error" v-show="updatedDefect.isInvalid">
                                            Image is mandatory.
                                        </div>
                                        <input type='file' id="image-upload-btn" v-on:change="handleImageChange(updatedDefect, $event)" style="display: none" accept="image/*" />
                                    </div>
                                </div>
                                <div class="form-group" v-show="updatedDefect.imageString">
                                    <label class="control-label col-md-4">&nbsp;</label>
                                    <div class="col-md-8">
                                        <img 
                                            v-bind:src="updatedDefect.imageString"
                                            class="img-rounded" 
                                            style="max-height:45px;max-width:100px">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">Comments:</label>
                                    <div class="col-md-8">
                                        <textarea rows="4" class="form-control" v-model="updatedDefect.comments"></textarea>
                                    </div>
                                </div>
                            </div>                            
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button"
                                    class="btn white-btn btn-padding col-md-6"
                                    data-dismiss="modal">Cancel</button>
                            <button type="button" 
                                class="btn red-rubine btn-padding col-md-6 "
                                v-bind:class="{'disabled': updatedDefectIsInvalid}"
                                v-on:click="saveDefectClicked">Save</button>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="font-grey-gallery">*Capture of image mandatory via the App</p>
@endsection


