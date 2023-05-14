@foreach ($comments as $comment) 
    @if ($comment->type === 'user')
        <div class="timeline-item">
            <div class="timeline-badge">
                <div class="timeline-icon">
                    <i class="icon-bubbles font-red-rubine"></i>
                </div>
            </div>
            <div class="timeline-body">
                <div class="timeline-body-arrow">
                </div>
                <div class="timeline-body-head">
                    <div class="timeline-body-head-caption">
                        {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="bold timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                        <span class="timeline-body-time">
                            wrote at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('d M Y') }}
                        </span>
                        @if ($comment->created_at != $comment->updated_at)
                        <span class="timeline-body-time"> edited at {{ $comment->present()->formattedUpdatedAt()->format('H:i:s') }} on {{ $comment->present()->formattedUpdatedAt()->format('d M Y') }}</span>
                        @endif
                    </div>
                    <div class="timeline-body-head-actions">
                        <div class="">
                        @if ($comment->created_by == Auth::id())
                            <button type="button" class="btn red-rubine edit-comment-btn btn-height" style=""><i class="jv-icon jv-edit"></i></button>
                            <button type="button" data-delete-url="/defects/delete_comment/{{ $comment->id }}"                                                         
                                class="btn delete-button grey-gallery btn-height ml0" 
                                title="Delete comment" 
                                data-confirm-msg="Are you sure you would like to delete this comment?">
                                <i class="jv-icon jv-dustbin"></i>
                            </button>
                        @endif                                        
                        </div>
                    </div>
                </div>
                <div class="timeline-body-content">
                    @if ($comment->created_by == Auth::id())
                    <span class="">
                        <a href="javascript:;" class="comments" data-type="textarea" data-pk="{{ $comment->id }}" data-original-title="Update comment"> {{ $comment->comments }}</a> 
                    </span>
                    @else
                        <span class=""> {{ $comment->comments }} </span>
                    @endif
                </div>
                <div class="timeline-body-content">
                    @foreach ($comment->getMedia() as $media)
                        <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                        <br><br>
                        <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-defect">
                            @if (strpos($media->getCustomProperty('mime-type'), 'image/') === 0)
                                <img class="img-rounded" style="max-width: 120px; max-height: 120px;" src="{{ asset(getPresignedUrl($media)) }}" alt="">
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @else 
        <div class="timeline-item">                            
            <div class="timeline-badge">
                <div class="timeline-icon">
                    <i class="icon-bell font-red-rubine"></i>
                </div>
            </div>
            <div class="timeline-body">
                <div class="timeline-body-arrow">
                </div>
                <div class="timeline-body-head">
                    <div class="timeline-body-head-caption">
                        {{ $comment->creator->first_name }} {{ $comment->creator->last_name }} <a href="mailto:{{ $comment->creator->email}}" class="bold timeline-body-title font-blue-madison">({{ $comment->creator->email}})</a>
                        <span class="timeline-body-time">
                            {{ $comment->comments }} at {{ $comment->present()->formattedReportDatetime()->format('H:i:s') }} on {{ $comment->present()->formattedReportDatetime()->format('d M Y') }}
                        </span>                                        
                    </div>
                </div>
                <div class="timeline-body-content">
                    @if ($comment->defect_status_comment != NULL)
                        <span class=""> {{ $comment->defect_status_comment }} </span>
                    @endif
                    @foreach ($comment->getMedia() as $media)
                        <strong class="text-muted"><i class="icon-doc"></i> &nbsp;Attachment: </strong>&nbsp;
                        <a href="{{ url('/defects/downloadMedia/' .  $media->id) }}" class="btn-link">{{ $media->file_name }}</a>
                    @endforeach                                
                </div>
            </div>
        </div>
    @endif

@endforeach