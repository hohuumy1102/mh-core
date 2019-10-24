<?php use Cake\Core\Configure; ?>
<?php $longtitude = !empty($value['longtitude']) ? $value['longtitude'] : Configure::read('GoogleMap.DefaultCoordinate.Longtitude'); ?>
<?php $latitude = !empty($value['latitude']) ? $value['latitude'] : Configure::read('GoogleMap.DefaultCoordinate.Latitude'); ?>
<input class="form-control" type="text" id="suggest_<?php echo $field; ?>" name="suggest_<?php echo $field; ?>" />
<input type="hidden" id="longtitude_<?php echo $field; ?>" name="longtitude_<?php echo $field; ?>" value="<?php echo $longtitude; ?>" />
<input type="hidden" id="latitude_<?php echo $field; ?>" name="latitude_<?php echo $field; ?>" value="<?php echo $latitude; ?>" />
<style type="text/css">
    #map { height: 450px; }
</style>
<div id="map"></div>
<script type="text/javascript">
    var map;
    var autocomplete;
    var geocoder;
    var marker = null;
    function initMap() {
        geocoder = new google.maps.Geocoder();
        autocomplete = new google.maps.places.Autocomplete(document.getElementById('suggest_<?php echo $field; ?>'), {});
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            updateMap(place.geometry.location);
        });
        setTimeout(function () {
            updateMap({
                lat: <?php echo $latitude; ?>,
                lng: <?php echo $longtitude; ?>,
            });
        }, 1000);
    }

    function updateMap(center) {
        map = new google.maps.Map(document.getElementById('map'), {
            center: center,
            zoom: 18,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        google.maps.event.addListener(map, "click", function (event) {
            updateMarker(map, {
                lat: event.latLng.lat(),
                lng: event.latLng.lng(),
            });
        });
        updateMarker(map, map.center);
    }

    function updateMarker(map, location) {
        if (marker) {
            marker.setMap(null);
        }
        marker = null;
        marker = new google.maps.Marker({
            map: map,
            draggable: true,
            position: location
        });
    }


    $("#suggest_<?php echo $field; ?>").parents('form').submit(function () {
        if (marker) {
            var coor = marker.getPosition();
            $('#longtitude_<?php echo $field; ?>').val(coor.lng());
            $('#latitude_<?php echo $field; ?>').val(coor.lat());
        } else {
            showAlert("<?php echo __('Please choose coordinate for Google Map'); ?>");
            return false;
        }
    });
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleMap.ApiKey'); ?>&callback=initMap&libraries=places">
</script>

