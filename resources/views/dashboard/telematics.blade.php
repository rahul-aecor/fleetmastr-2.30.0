@extends('layouts.default')

@section('plugin-scripts')
    <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyDsD41irpLIoy8iqlZci8BdUN68YSwaXws"></script> 
    <script src="{{ elixir('js/bundles/telematics.bundle.js') }}" type="text/javascript"></script>
@endsection

@section('scripts')
<!--     <script>
    
</script>
<script src="//maps.googleapis.com/maps/api/js?key=AIzaSyDsD41irpLIoy8iqlZci8BdUN68YSwaXws&callback=initialize"></script> -->
@endsection

@section('content')
    <div id="">
        {{-- New section --}}
        <div class="row">
            <div class="col-md-12">
                <h4 class="block dashboard-section-name">Telematics</h4>
            </div>
        </div>
        <div id="map_wrapper">
            <div id="map_canvas" class="mapping"></div>
        </div>
        <table class="table table-striped table-hover">
            <tbody>
                <tr>
                    <td>
                        <table class="table table-striped table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 50%">Number of vehicles tracked:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Total miles driven:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Total number of journeys:</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table class="table table-striped table-hover">
                            <tbody>                            
                                <tr>
                                    <td>Average journey length:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Average speed:</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Fuel cost:</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection