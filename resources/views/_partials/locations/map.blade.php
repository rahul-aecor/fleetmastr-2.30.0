<div class="row {{ $from != 'edit' ? 'hide' : '' }}" id="map_container">
    <div class="col-md-12 col-lg-12">
        <div class="portlet box">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Drop Pin
                </div>
            </div>
            <div class="portlet-body">
                <div id="map_wrapper" class="map_wrapper" style="position:relative;">
                    <div class="js-drop-pin-btn-container hide" id="" style="top: 10px;left: 0;right: 0;position: absolute;z-index: 1;display: flex;justify-content: center;">
                        <button type="button" class="btn btn-blue-color js-drop-pin-btn" style="height:40px;">Move position of pin</button>
                    </div>
                    <div id="location_map_canvas" class="mapping"></div>
                </div>
            </div>
        </div>
    </div>
</div>