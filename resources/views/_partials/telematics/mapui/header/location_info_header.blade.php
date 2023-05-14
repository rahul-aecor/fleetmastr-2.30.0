<div class="journey-timeline-wrapper-sidebar-header divLiveTabLocationInfoDetailsBlock">
    <div class="heading-area">
        <div class="back-button">
            <button class="divLiveTabLocationInfoDetailsBackBtn">
                <svg xmlns="http://www.w3.org/2000/svg" class="back-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div>
            <h2>
                @if(isset($data->category_name) && !empty($data->category_name))
                <span>{{$data->category_name}}</span>
                @endif
            </h2>
        </div>
    </div>
</div>