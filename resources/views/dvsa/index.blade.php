@extends('layouts.default')

@section('plugin-styles')
<link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}"
  rel="stylesheet" type="text/css" />
<style>
  .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
  }

  .tracking-wider {
    letter-spacing: 0.05em;
  }

  dl {
    display: flex;
    margin: 0;
  }

  dl dt {
    color: #6b7280;
    margin-right: 0.5rem;
  }

  dl dd {
    color: #111827;
    margin: 0;
  }

  .dvsa-table {
    width: 100%;
    background: white;
    border: 1px solid #e5e7eb;
    border-collapse: collapse;
  }

  .dvsa-table.dvsa-table-striped tbody tr:nth-of-type(odd) {
    background: #c4d8f2;
  }

  .dvsa-table thead th,
  .dvsa-table tfoot th {
    color: #4d4e4e;
  }

  .dvsa-table th,
  .dvsa-table td {
    padding: 0.5em;
    border: 1px solid #e5e7eb;
  }

  .dvsa-table th:nth-child(n+3),
  .dvsa-table td:nth-child(n+3) {
    text-align: center;
    font-weight: 400;
  }

  .dvsa-table tbody tr {
    background-color: #daedf3;
  }

  .bg-light-gray {
    background-color: #f0f2f5 !important;
  }

  .text-left {
    text-align: left;
  }

  .text-center {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }

  .text-gray-500 {
    color: #6b7280;
  }

  .font-weight-normal {
    font-weight: 400;
  }

  .font-weight-bold {
    font-weight: 700;
  }

  .uppercase {
    text-transform: uppercase;
  }

  .border-bottom {
    border-bottom: 1px solid #e5e7eb;
  }

  .score-display-box {
    height: 45px;
    padding: 0 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 1px 0 #a0a0a0;
  }
</style>
@endsection

@section('plugin-scripts')
@endsection

@section('scripts')
<script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}"
  type="text/javascript"></script>
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/dvsa.js') }}" type="text/javascript"></script>
@endsection

@section('content')
<div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
  <ul class="nav nav-tabs nav-justified">
    <li class="active" id="dvsa_dashboard">
      <a href="#dvsa-tab" data-toggle="tab" id="dvsa_tab">
        Dashboard</a>
    </li>
    <li id="reported_issues">
      <a href="#reported-issue-tab" data-toggle="tab" id="reported_issue_tab">
        Reported Issues</a>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="dvsa-tab">
      <form class="form row d-flex align-items-center" id="checks-quick-filter-form">
        <div class="col-md-3">
          <div class="form-group margin-bottom0">
            <select class="js-dvsa-year form-control js-select2">
              @foreach (config('config-variables.dvsa_years') as $key => $year)
                <option value="{{ $key }}" {{ $key == '2021' ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-9">
          <div class="row d-flex justify-content-end">
            <div class="col-md-3 text-right">
              <span>Operator ID: <span class="font-weight-bold">0000-0000</span></span>
            </div>
            <div class="col-md-3 text-right">
              <span>System Provider ID: <span class="font-weight-bold">ZZ0000</span></span>
            </div>
          </div>
        </div>
      </form>
      <section class="portlet box margin-top-15">
        <div class="portlet-title">
          <div class="caption">
            DVSA Earned Recognition Dashboard
          </div>
          <div class="actions">
            <span class="jv-icon jv-download"></span>
          </div>
        </div>
        <div class="portlet-body">
          <article class="margin-top-10">
            <div class="table-data">
              <table class="dvsa-table dvsa-table-striped">
                <thead>
                  <tr>
                    <th rowspan="2"></th>
                    <th class="text-right font-weight-bold">Reporting period (00)</th>
                    <th>01</th>
                    <th>02</th>
                    <th>03</th>
                    <th>04</th>
                    <th>05</th>
                    <th>06</th>
                    <th>07</th>
                    <th>08</th>
                    <th>09</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12</th>
                    <th>13</th>
                  </tr>
                  <tr>
                    <th class="text-right font-weight-bold">Year (0000)</th>
                    <th class="font-weight-normal text-center">2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                    <th>2021</th>
                  </tr>
                </thead>
                <tbody>
                  <tr style="background-color: #f2f2f2; color: #4d4e4e">
                    <td>Code</td>
                    <td>Description</td>
                    <td colspan="13"></td>
                  </tr>
                  <tr>
                    <td class="bg-white">M1</td>
                    <td class="bg-white">Full Set</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td style="background-color: yellow"><a href="javascript:void(0)" class="js-navigate-tab" data-status="yellow" data-id="M1"><u>99.50</u></a></td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="bg-white">M2</td>
                    <td class="bg-white">Completed</td>
                    <td>100.00</td>
                    <td style="background-color: yellow"><a href="javascript:void(0)" class="js-navigate-tab" data-status="yellow" data-id="M2"><u>99.40</u></a></td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="bg-white">M3</td>
                    <td class="bg-white">Frequency</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td style="background-color: yellow"><a href="javascript:void(0)" class="js-navigate-tab" data-status="yellow" data-id="M3"><u>99.25</u></a></td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="bg-white">M4</td>
                    <td class="bg-white">Driver Defects</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td style="background-color: red"><a href="javascript:void(0)" class="js-navigate-tab" data-status="red" data-id="M4"><u>99.25</u></a></td>
                    <td>100.00</td>
                    <td>100.00</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr style="background-color: #f2f2f2; color: #4d4e4e">
                    <td>Code</td>
                    <td>Description</td>
                    <td colspan="13"></td>
                  </tr>
                  <tr>
                    <td class="bg-white">M5</td>
                    <td class="bg-white">MOT</td>
                    <td><a href="#"><u>98.00</u></a></td>
                    <td><a href="#"><u>98.25</u></a></td>
                    <td><a href="#"><u>98.00</u></a></td>
                    <td><a href="javascript:void(0)" class="js-navigate-tab" data-status="blue" data-id="M5"><u>98.10</u></a></td>
                    <td><a href="#"><u>98.35</u></a></td>
                    <td><a href="#"><u>98.25</u></a></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </section>
    </div>
    <div class="tab-pane" id="reported-issue-tab">
      <form class="form row d-flex align-items-center" id="checks-quick-filter-form">
        <div class="col-md-4">
          <div class="check_search">
            <div class="form-group margin-bottom0">
              <select class="js-dvsa-periods form-control js-select2">
                @foreach (config('config-variables.dvsa_periods') as $key => $period)
                  <option value="{{ $key }}">{{ $period }}</option>
                @endforeach
              </select>
              <small class="text-danger">{{ $errors->first('registration') }}</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group margin-bottom0">
            <select class="js-dvsa-codes form-control js-select2">
              @foreach (config('config-variables.dvsa_codes') as $key => $code)
                <option value="{{ $key }}">{{ $code }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex align-items-center">
            <span>Score</span>
            <span class="margin-left-15 score-display-box js-score bg-yellow-custom">99.50</span>
          </div>
        </div>
      </form>
      <section class="portlet box margin-top-15">
        <div class="portlet-title">
          <div class="caption">
            Reported Issues
          </div>
          <div class="actions">
            <span class="jv-icon jv-download"></span>
          </div>
        </div>
        <div class="portlet-body">
          <article class="margin-top-10">
            <table id="jqGrid" class="table-striped table-bordered table-hover margin-bottom-20" data-type="dvsa"></table>
            <div id="jqGridPager" class="multiple-action"></div>
          </article>
        </div>
      </section>
    </div>
  </div>
</div>
@endsection