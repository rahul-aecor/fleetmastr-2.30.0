<div id="markerDetailsModal" class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-header">
            abc
        </div>
        <div class="modal-content">
            <div class="modal-body">
                <table class="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>
                                <table class="table table-striped table-hover">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%">Vehicle:</td>
                                            <td><a title="" href="/vehicles/{{ $markerDetails['vehicleId'] }}" class="font-blue" target="_blank" rel="noopener">{{ $markerDetails['registration'] }}</a></td>
                                        </tr>
                                        <tr>
                                            <td>Driver:</td>
                                            <td>{{ $markerDetails['driver'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Address:</td>
                                            <td>{{ $markerDetails['street'] }}, {{ $markerDetails['town'] }}, {{ $markerDetails['postcode'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Speed:</td>
                                            <td>{{ $markerDetails['speed'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Direction:</td>
                                            <td>{{ $markerDetails['direction'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Last updated:</td>
                                            <td>{{ Carbon\Carbon::parse($markerDetails['last_update'])->format('h:i:s d M Y')  }}</td>
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
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <table class="table table-striped table-hover">
                                    <tbody>                            
                                        <tr>
                                            <td><img src="https://maps.googleapis.com/maps/api/streetview?location={{$markerDetails['lat']}},{{$markerDetails['lon']}}&size=120x120
                                            &key={{ env('GOOGLE_MAP_KEY') }}" /></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>