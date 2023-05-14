@extends('layouts.default')

@section('plugin-scripts')
@endsection

@section('scripts')
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN ALERTS PORTLET-->
            <div class="portlet light">
                <div class="portlet-body">
                    <div class="note note-danger text-center">
                        <h4 class="block">Report Not Found</h4>
                    </div>
                </div>
            </div>
            <!-- END ALERTS PORTLET-->
        </div>
    </div>
@endsection