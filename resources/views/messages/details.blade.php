<div class="row static-info">
    <div class="col-md-2 name">
         Date:
    </div>
    <div class="col-md-10 value">
        {{ $message->sent_at }}
    </div>
</div>

<div class="row static-info">
    <div class="col-md-2 name">
         Sender:
    </div>
    <div class="col-md-10 value" style="font-weight: normal;">
        {{ $message->sender->email }}
    </div>
</div>

{{-- <div class="row static-info">
    <div class="col-md-2 name">
         Message:
    </div>
    <div class="col-md-10 value media-size" style="font-weight: normal;">
         {!! $message->content !!}
    </div>
</div> --}}

@if ($message->template_name)
<div class="row static-info">
    <div class="col-md-2 name">
        Template:
    </div>
    <div class="col-md-10 value" style="font-weight: normal;">
        {{ $message->template_name }}
    </div>
</div>
@endif

<div class="row static-info">
    <div class="col-md-2 name">
        Recipients:
    </div>
    <div class="col-md-10 value" style="font-weight: normal;">
        {{ $message->receiver->count() }} (Read {{ $message->getReadRecieverCount() }} / Not read {{ $message->getUnReadRecieverCount() }})
    </div>
</div>

@if ($message->type !== 'standard' && $message->type !== '')
    <div class="row static-info" style="margin-bottom: 0px;">
        <div class="col-md-2 name">
            Response:
        </div>
        <div class="col-md-10 value" style="font-weight: normal;">
            <div class="row static-info">
                <div class="col-md-2 value">
                    <a href="messages/report/{{$message->id}}" class="font-blue" 
                        @if($percent == 0)
                            disabled
                        @endif>
                        Download report
                    </a>
                </div>
                <div class="col-md-1 response-percent-text">{{ $percent }}%</div>
                <div class="col-md-9">
                    <div class="progress mb-0">
                        <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" style="width: {{$percent}}%">
                            <span class="sr-only">{{$percent}}% Complete (success)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row static-info">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-2 name">
                 Status:
            </div>
            <div class="col-md-10 value">
                 <a href="/messages/{{$message->id}}/statusReport" class="font-blue">Download status</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 value">
                <div class="scroller padding0" data-height="280px">
                    <table class="table table-bordered table-striped table-hover message-recipient-list margin-top-15">
                        <thead>
                            <tr class="bg-grey-gallery sticky-table-header">
                                <th style="width: 15%;vertical-align: top;">Name</th>
                                <th style="width: 15%;vertical-align: top;">Division</th>
                                <th style="width: 15%;vertical-align: top;">Region</th>
                                <th style="width: 10%;vertical-align: top;">ID</th>
                                <th style="width: 10%;vertical-align: top;">Send Channel</th>
                                <th style="width: 10%;vertical-align: top;">Status</th>
                                <th style="width: 25%;vertical-align: top;">Response/Acknowledgement Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($message->receiver as $key => $val)
                                <tr>
                                    <td>{{ $val->name }}</td>
                                    <td>{{ $val->division_name ? $val->division_name : '' }}</td>
                                    <td>{{ $val->region_name ? $val->region_name : '' }}</td>
                                    <td>{{ $val->engineer_id }}</td>
                                    <td>
                                        @if ($val->sent_via == 'sms')
                                            {{ $val->mobile or '-'}}
                                        @else
                                            {{ ucfirst($val->sent_via) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ ucfirst($val->status) }}
                                        @if($val->status === "error")
                                            <span class="pull-right">
                                                <i class="fa fa-question-circle tooltips" title="{{ $val->error_json['message'] }}" data-placement="left" data-container=".modal-header"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>                                 
                                        @if($val->response_received_at != null)
                                            {{ $val->response_received_at->setTimezone(config('config-variables.format.displayTimezone'))->format(config('config-variables.format.showDateTime')) }}
                                        @else
                                            None
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>