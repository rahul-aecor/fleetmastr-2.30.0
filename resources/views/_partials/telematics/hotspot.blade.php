<div class="hotspot-btn-wrapper" id="hotspot-btn-wrapper">
    <button class="btn btn-white">
        <i class="jv-icon jv-hotspot"></i>
    </button>
    <div class="hotspot-content" style="display: none;">
        <div class="portlet box margin-bottom0">
            <div class="portlet-title d-flex align-items-center justify-content-between">
                <div class="caption flex-grow-1">
                    <h4 class="font-weight-700 margin-0">Hot spots</h4>
                </div>
                <div>
                    <a class="font-red-rubine closeBtn">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
            </div>
            <div class="portlet-body" style="padding: 10px;">
                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">Acceleration</label>
                    <label class="checkbox-inline d-flex pt-0 accelerationswitch">
                        <input type="checkbox" class="cbFilterIncidentType" checked id="accelerationswitch" value="tm8.dfb2.acc.l" data-toggle="toggle" data-on="On" data-off="Off"
                    name="">
                    </label>
                </div>

                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">Braking</label>
                    <label class="checkbox-inline d-flex pt-0 brakingswitch">
                        <input type="checkbox" class="cbFilterIncidentType" checked id="brakingswitch" value="tm8.dfb2.dec.l" data-toggle="toggle" data-on="On" data-off="Off"
                    name="">
                    </label>
                </div>

                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">Cornering</label>
                    <label class="checkbox-inline d-flex pt-0 corneringswitch">
                        <input type="checkbox" class="cbFilterIncidentType" checked id="corneringswitch" value="tm8.dfb2.cnrr.l" data-toggle="toggle" data-on="On" data-off="Off"
                    name="">
                    </label>
                </div>

                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">Speeding</label>
                    <label class="checkbox-inline d-flex pt-0 speedingswitch">
                        {{-- <input type="checkbox" class="cbFilterIncidentType" checked id="speedingswitch" value="tm8.dfb2.spd" data-toggle="toggle" data-on="On" data-off="Off" name=""> --}}
                        <input type="checkbox" class="cbFilterIncidentType" checked id="speedingswitch" value="tm8.dfb2.spdinc" data-toggle="toggle" data-on="On" data-off="Off" name="">
                    </label>
                </div>

                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">Idling</label>
                    <label class="checkbox-inline d-flex pt-0 idlingswitch">
                        <input type="checkbox" class="cbFilterIncidentType" checked id="idlingswitch" data-toggle="toggle" value="tm8.gps.idle.start" data-on="On" data-off="Off"
                    name="">
                    </label>
                </div>

                <div class="switch-wrapper d-flex align-items-center justify-content-between">
                    <label class="margin-0">RPM</label>
                    <label class="checkbox-inline d-flex pt-0 rpmswitch">
                        <input type="checkbox" class="cbFilterIncidentType" checked id="rpmswitch" value="tm8.dfb2.rpm" data-toggle="toggle" data-on="On" data-off="Off"
                    name="">
                    </label>
                </div>
            </div>
        </div>

        {{--<div class="switch-wrapper d-flex align-items-center justify-content-between">
            <label class="margin-0">Efficiency</label>
            <label class="checkbox-inline d-flex pt-0 efficiencyswitch">
                <input type="checkbox" class="cbFilterIncidentType" checked id="efficiencyswitch" data-toggle="toggle" data-on="On" data-off="Off"
              name="">
            </label>
        </div>

        <div class="switch-wrapper d-flex align-items-center justify-content-between">
            <label class="margin-0">Crashes</label>
            <label class="checkbox-inline d-flex pt-0 crashesswitch">
                <input type="checkbox" class="cbFilterIncidentType" checked id="crashesswitch" data-toggle="toggle" data-on="On" data-off="Off"
              name="">
            </label>
        </div>--}}
    </div>
</div>