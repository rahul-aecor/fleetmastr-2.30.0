<div class="end-border-bottom" id="eebLiveTabLocationCategoryListFrontTab"></div>
<div class="journey-timeline-wrapper-sidebar-body padding-0" id="liveTabLocationCategoryListFrontTab">
    <ul class="list-unstyled sidebar-lists ul-tab-list">
        @if($data && $data->count()>0)
            @foreach($data as $d)
                <li class="sidebar-lists-item">
                    <a class="sidebar-lists-item-link space-x-3 _locationCategoryList" href="javascript:void(0);" locationCategoryId="{{$d->id}}">
                        <div class="flex-shrink-0 sidebar-lists-item-status">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate title">
                                <span>{{$d->name}}</span>
                            </div>
                            <div class="info">
                                <span>{{$d->location->count()}} Locations</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="next-arrow-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </a>
                </li>
            @endforeach
            @else
            <li><div class="text-center margin-top-5">No results found</div></li>
        @endif
    </ul>
</div>
