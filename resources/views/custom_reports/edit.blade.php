@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
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
        {!! Breadcrumbs::render('custom_report_edit', $report->id) !!}
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
                        $url= '/reports/'.$report->id;
                    ?>
                    {!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation')->id('frmCustomReport')->action($url)->put() !!}

                        {{ csrf_field() }}
                        {!! BootForm::bind($report) !!}

                        @include('_partials.custom_reports.form')

                    {!! BootForm::close() !!}

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