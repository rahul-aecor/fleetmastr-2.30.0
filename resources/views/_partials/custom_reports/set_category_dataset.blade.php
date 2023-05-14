<div class="form-group">
    <label class="col-md-4 control-label align-self-start pt25 js-add-data" for="report_name">Add data:</label>
    <div class="col-md-8">
        <input type="hidden" name="dataset_order" id='dataset-order'>
        <input type="hidden" name="is_dataset_changed" id='is-dataset-changed' value="false">
        @if($report['report_for'] == 'user' || $report['report_for'] == 'all')
            @include('_partials.custom_reports.user_category_dataset')
            @include('_partials.custom_reports.vehicle_category_dataset')
        @elseif($report['report_for'] == 'vehicle')
            @include('_partials.custom_reports.vehicle_category_dataset')
            @include('_partials.custom_reports.user_category_dataset')
        @else
            @include('_partials.custom_reports.user_category_dataset')
            @include('_partials.custom_reports.vehicle_category_dataset')
        @endif
        
        <div class="row">
            <div class="field-checkbox-wrapper-error col-md-12"></div>
        </div>

    </div>
</div>