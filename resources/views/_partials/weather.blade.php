<div id="portlet-geolocation" class="modal fade default-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-red-rubine">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Weather Information</h4>
      </div>
      <div class="modal-body">
        <div id="weather">
            <ul class="weather-detail">
              <li><img class="weather-icon" /></li>
              <li><h2 class="weather-temperature"></h2></li>
            </ul>
            <ul class="location-detail">
              <li class="weather-place"></li>
              <li class="weather-description"></li>
              <li>
                Min: <span class="weather-min-temperature"></span>                
              </li>
              <li>
                Max: <span class="weather-max-temperature"></span>
              </li>
            </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn grey-gallery" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->