@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/vanilla-datetimerange-picker/vanilla-datetimerange-picker.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js" type="text/javascript"></script>
    <script src="{{ elixir('js/vanilla-datetimerange-picker/vanilla-datetimerange-picker.js') }}"></script>
    <script src="{{ elixir('js/report_categories.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="page-bar">
        {!! Breadcrumbs::render('custom_report_create') !!}
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="portlet box">
                <div class="portlet-body form ipad_edit_form">
                    <!-- BEGIN FORM-->

                    <?php
                        $columnSizes = [
                          'md' => [4, 8]
                        ];
                        $url= '/reports/generate_report';
                    ?>
                    {!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation')->id('frmCustomReport')->action($url)->post() !!}
                        {{ csrf_field() }}
                        @include('_partials.custom_reports.form')
                    </form>

                </div>
            </div>
        </div>
        @include('_partials.custom_reports.report_summary')
    </div>
    @include('_partials.custom_reports.modals')

@endsection

@push('scripts')
    <script src="{{ elixir('js/custom-report-calendar.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/custom-reports.js') }}" type="text/javascript"></script>
@endsection