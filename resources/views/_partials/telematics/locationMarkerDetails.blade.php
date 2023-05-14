<div id="locationMarkerDetailsModal" class="locationMarkerDetailsModal" tabindex="-1" data-background="static">
    <h4>Location Details</h4>
    
    <table class="table margin-bottom0">
        <tbody>
            <tr>
                <td>
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <td>Name:</td>
                                <td>{{ $location->name }}</td>
                            </tr>
                            <tr>
                                <td>Category:</td>
                                <td>{{ $location->category->name }}</td>
                            </tr>
                            <tr>
                                <td>Address:</td>
                                <td>{{ $location->address1 }}, {{ $location->address2 }}</td>
                            </tr>
                            <tr>
                                <td>Town / City:</td>
                                <td>{{ $location->town_city }}</td>
                            </tr>
                            <tr>
                                <td>Postcode:</td>
                                <td>{{ $location->postcode }}</td>
                                <input type="hidden" id="markerDetailsPostcode" value="{{ $location->postcode }}">
                                <input type="hidden" id="markerDetailsLatitude" value="{{ $location->latitude }}">
                                <input type="hidden" id="markerDetailsLongitude" value="{{ $location->longitude }}">
                            </tr>                            
                        </tbody>
                    </table>
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="text-center">
                                    <button type="button" class="btn red-rubine js-view-location-zoom btnViewLocationZoom" id="btnViewLocationZoom">View location (zoom)</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="location-view-img">
                                        <button name="streetViewBtnLocation" class="streetViewBtnLocation">
                                            <img src="https://maps.googleapis.com/maps/api/streetview?location={{ $location->latitude }},{{ $location->longitude }}&size=120x120&key={{ env('GOOGLE_MAP_KEY') }}" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>