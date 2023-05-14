 <table class="ui-jqgrid-htable table table-condensed table-striped table-hover custom-table-striped table-hmrc-info">
    <thead>
        <tr>
            <th>Date</th>
            <th>Last Edited By</th>
            <th>Tax Year</th>
            <th style="text-align: center;">Details</th>
        </tr>
    </thead>
    <tbody>
        @foreach($hmrcco2data as $key => $val)
        <?php
            $cls_date = new DateTime($val->edited_at);
            $display_date = $cls_date->format('h:i:s M d Y');
         ?>
            <tr>
                <td>{{ $display_date }}</td>
                <td>{{ $val->edited_by }}</td>
                <td>
                    {{ $val->year }}
                    @if ($taxyear == $val->year)
                        (current)
                    @endif
                </td>
                <td style='text-align: center;'>
                    <a title="Details" data-year="{{ $val->year }}" href="#hmrcco2_detail" class="btn btn-xs grey-gallery tras_btn hmrc_details"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> 
                    <a href="{{ url('/settings/hmrc/exportexcel/'.$val->year) }}" id="exportHMRCExcel" class="btn btn-xs grey-gallery tras_btn">
                        <span onclick=""><i class="jv-icon jv-download icon-big"></i></span>
                    </a>
                    @if(!in_array($val->year, $taxYearsFinalised))
                       <a title="Edit" href="#hmrcco2_edit" data-year="{{ $val->year }}" class="btn btn-xs grey-gallery tras_btn hmrc_edit"><i class="jv-icon jv-edit icon-big"></i></a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>