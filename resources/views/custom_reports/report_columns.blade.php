<div class="portlet-title bg-red-rubine">
    <h4 class="modal-title padding-top-5">
        Data Set - {{ $report->name }}
    </h4>
</div>
<div class="portlet-body">
    <label class="control-label ml10">This report includes the following data:</label>
    <div class="js-show-report-data"></div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        @if($dataSet)
            <div class="scroller" data-height="200px">
                <table class="table table-striped table-hover custom-table-striped">
                    <tbody>
                        @if($type == 'create' && (!isset($report) || $report->is_custom_report))
                            @foreach($dataSet as $column)
                                <tr>
                                    <td>
                                        <input type="hidden" name="report_dataset[]" value="{{ $column->id }}">
                                        {{ $column->title }}
                                    </td>
                                    <td>{{ str_replace('App\Models\\', '', $column->model_type) }}</td>
                                </tr>
                            @endforeach
                        @else
                            @foreach($dataSet as $column)
                                <?php $columnData = explode("|", $column);?>
                                <tr>
                                    <td>{{ $columnData[0] }}</td>
                                    <td>{{ isset($columnData[1]) ? $columnData[1] : 'Vehicle' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>