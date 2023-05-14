<div class="journey-timeline-wrapper-sidebar-header divLiveTabUserVehicleDetailsBlock">
    <div class="heading-area">
        <div class="back-button">
            <button class="divLiveTabUserVehicleDetailsBackBtn">
                <svg xmlns="http://www.w3.org/2000/svg" class="back-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div>
            <div class="heading-area-title">Last updated</div>
            <div class="heading-area-info">{{isset($data->last_update)?$data->last_update:''}}</div>
        </div>
    </div>
    <div class="vehichle-number-plate"><span>{{isset($data->user)?$data->user:''}}</span>
        <button type="button" class="vehichle-number-plate-close-btn divLiveTabUserVehicleDetailsBackBtn">
            <span class="sr-only">Close panel</span>
            <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>