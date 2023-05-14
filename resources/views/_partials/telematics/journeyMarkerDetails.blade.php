<div id="markerDetailsModal" class="markerDetailsModal" tabindex="-1" data-background="static">
    <h4><img src="{{$incidentData['icon']}}"> &nbsp; {{$incidentData['incident_type']}}</h4>

    <table class="table margin-bottom0">
        <tbody>
        <tr>
            <td>
                <table class="table table-striped table-hover margin-bottom0">
                    <tbody>
                    <tr>
                        <td style="width: 30%">Registration:</td>
                        <td><a title="" href="/vehicles/{{ $incidentData['vehicle_id'] }}" class="font-blue" target="_blank">{{ $incidentData['registration'] }}</a></td>
                    </tr>
                    <tr>
                        <td>Driver:</td>
                        <td>{{ $incidentData['user'] }}</td>
                    </tr>

                    <tr>
                        <td>Status:</td>
                        <td>{{ $incidentData['status'] }}</td>
                    </tr>
                    <tr>
                        <td>Speed:</td>
                        <td>{{ $incidentData['speed'] }}</td>
                    </tr>
                    <tr>
                        <td>Speed limit:</td>
                        <td>{{ getStreetSpeed($incidentData['street_speed']) }} MPH</td>
                    </tr>
                    <tr>
                        <td>Direction:</td>
                        <td>{{ $incidentData['direction'] }}</td>
                    </tr>
                    <tr>
                        <td>Address:</td>
                        <td>{{ $incidentData['location'] }}</td>
                    </tr>
                    <tr>
                        <td>Lat/Long:</td>
                        <td>{{ $incidentData['latitude'] .",".$incidentData['longitude'] }}</td>
                    </tr>
                    <tr>
                        <td>Date/Time:</td>
                        <td>{{ Carbon\Carbon::parse($incidentData['date'])->format('H:i:s d M Y')  }}</td>
                    </tr>

                    </tbody>
                </table>
            </td>
            <td>
                <table class="table margin-bottom0">
                    <tbody>
                    <tr>
                        <td class="text-center">
                            <input type="hidden" id="markerDetailsLatitude" value="{{$incidentData['latitude']}}">
                            <input type="hidden" id="markerDetailsLongitude" value="{{$incidentData['longitude']}}">
                            <button name="streetViewBtn" class="streetViewBtn">
                                <img src="https://maps.googleapis.com/maps/api/streetview?location={{$incidentData['latitude']}},{{$incidentData['longitude']}}&size=400x400
                                &key={{ env('GOOGLE_MAP_KEY') }}" /></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>