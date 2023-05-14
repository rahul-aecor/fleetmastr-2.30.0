<div class="markerDetailsModal" tabindex="-1" data-background="static">
    <h4>Zone Alert</h4>
    
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
                                <td>User:</td>
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
                                <td>{{ $markerDetails['max_acceleration'] }}</td>
                            </tr>
                            <tr>
                                <td>Direction:</td>
                                <td>{{ $markerDetails['direction'] }}</td>
                            </tr>
                            <tr>
                                <td>Address:</td>
                                <td>{{ $markerDetails['address'] }}</td>
                            </tr>
                            <tr>
                                <td>Lat/Long:</td>
                                <td>{{$markerDetails['lat']}}, {{$markerDetails['lon']}}</td>
                            </tr>
                            <tr>
                                <td>Date/Time:</td>
                                <td>{{ Carbon\Carbon::parse($markerDetails['start_time'])->setTimezone(config('config-variables.format.displayTimezone'))->format('H:i:s d M Y')  }}</td>
                            </tr>
                            
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="table table-striped table-hover">
                        <tbody>                            
                            <tr>
                                <td class="text-center">
                                    <button name="streetViewBtn" class="streetViewBtn">
                                        <img src="https://maps.googleapis.com/maps/api/streetview?location={{$markerDetails['lat']}},{{$markerDetails['lon']}}&size=120x120
                                &key={{ env('GOOGLE_MAP_KEY') }}" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                {{-- <td>
                    <table class="table margin-bottom0 mapInfoModal">
                        <tbody>                            
                            <tr>
                                <td class="text-center">
                                    <input type="hidden" id="markerDetailsLatitude" value="{{$markerDetails['lat']}}">
                                    <input type="hidden" id="markerDetailsLongitude" value="{{$markerDetails['lon']}}">
                                    <input type="hidden" id="markerDetailsBounds" value="{{$markerDetails['bounds']}}">
                                    <button name="streetViewBtn" class="streetViewBtn">
                                    <img src="https://maps.googleapis.com/maps/api/streetview?location={{$markerDetails['lat']}},{{$markerDetails['lon']}}&size=120x120
                                &key={{ env('GOOGLE_MAP_KEY') }}" /></button>
                                    </td>
                            </tr>
                        </tbody>
                    </table>
                </td> --}}
            </tr>
        </tbody>
    </table>
</div>