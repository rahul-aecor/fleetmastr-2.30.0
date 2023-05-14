@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bundles/defects.bundle.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div id="defects-page">        
        <div class="portlet light" v-cloak>
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-fire font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase">Report Defect</span>
                    <span class="caption-helper uppercase">@{{ currentStep }}</span>
                </div>            
            </div>
            <div class="portlet-body form">
                <component :is="currentStep" 
                    :registration="registration"
                    :vehicle="vehicle"
                    :survey-master="surveyMaster"
                    :prohibitional-defects="prohibitionalDefects"
                    :option-list="optionList">
                </component>
            </div>
        </div>      
        <defects-confirmation-modal 
            :original-defect="originalDefect"
            :prohibitional-defects="prohibitionalDefects">
        </defects-confirmation-modal>  
    </div>
@endsection