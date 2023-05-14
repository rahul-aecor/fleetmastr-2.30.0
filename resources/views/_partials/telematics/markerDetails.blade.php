<div id="markerDetailsModal" class="markerDetailsModal" tabindex="-1" data-background="static">
    <h4>Vehicle Details</h4>
    
    <table class="table margin-bottom0">
        <tbody>
            <tr>
                <td>
                    <table class="table table-striped table-hover margin-bottom0">
                        <tbody>
                            <tr>
                                <td style="width: 30%">Registration:</td>
                                <td><a title="" href="/vehicles/{{ $markerDetails['vehicleId'] }}" class="font-blue" target="_blank">{{ $markerDetails['registration'] }}</a></td>
                            </tr>
                            <tr>
                                <td>Driver:</td>
                                <td>{{ $markerDetails['driver'] }}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>{{ $markerDetails['status'] }}</td>
                            </tr>
                            <tr>
                                <td>Speed:</td>
                                <td>{{ $markerDetails['speed'] }}</td>
                            </tr>
                            <tr>
                                <td>Speed limit:</td>
                                <td>{{ isset($markerDetails['street_speed'])?getStreetSpeed($markerDetails['street_speed']).'MPH':'NA' }} </td>
                            </tr>
                            <tr>
                                <td>Direction:</td>
                                <td>{{ $markerDetails['direction'] }}</td>
                            </tr>
                            <tr>
                                <td>Address:</td>
                                <td>@if($markerDetails['street']!="") {{ $markerDetails['street'] }}, @endif
                                @if($markerDetails['town']!=""){{ $markerDetails['town'] }}, @endif{{ $markerDetails['postcode'] }}</td>
                            </tr>
                            <tr>
                                <td>Lat/Long:</td>
                                <td>{{ $markerDetails['lat'] .",".$markerDetails['lon'] }}</td>
                            </tr>
                            <tr>
                                <td>Date/Time:</td>
                                <td>{{ $markerDetails['last_update'] != '' ? Carbon\Carbon::parse($markerDetails['last_update'])->format('H:i:s d M Y') : 'N/A' }}</td>
                            </tr>
                            {{-- <tr>
                                <td>Last updated:</td>
                                <td>{{ Carbon\Carbon::parse($markerDetails['last_update'])->format('H:i:s d M Y')  }}</td>
                            </tr>
                            <tr>
                                <td>Driving:</td>
                                <td><span class="label-results label-success">{{ $markerDetails['total_driving'] }}</span></td>
                            </tr>
                            <tr>
                                <td>Idling:</td>
                                <td><span class="label-results label-warning">{{ $markerDetails['total_idling'] }}</span></td>
                            </tr>
                            <tr>
                                <td>Stopped:</td>
                                <td><span class="label-results label-danger">{{ $markerDetails['total_stopped'] }}</span></td>
                            </tr> --}}
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="table margin-bottom0">
                        <tbody>                            
                            <tr>
                                <td class="text-center">
                                    <input type="hidden" id="markerDetailsLatitude" value="{{$markerDetails['lat']}}">
                                    <input type="hidden" id="markerDetailsLongitude" value="{{$markerDetails['lon']}}">
                                    <button name="streetViewBtn" class="streetViewBtn">
                                    <img src="https://maps.googleapis.com/maps/api/streetview?location={{$markerDetails['lat']}},{{$markerDetails['lon']}}&size=400x400
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