'use strict';
var i,
    map,
    bounds = null,
    my_position = null,
    marker,
    marker_perso = null,
    markers = [],
    html_struct = '',
    mc,
    infowindow = null;

jQuery(function () {
    /*
    function getPosPerso(){
        
        navigator.geolocation.getCurrentPosition(
            function(position){
                
                my_position = {
                    lat : position.coords.latitude,
                    lng : position.coords.longitude
                }
                var latlngPerso = new google.maps.LatLng(my_position.lat,my_position.lng);
                
                if( null === marker_perso ){
                    marker_perso = new google.maps.Marker({
                        icon : {
                            url : template_directory_uri + '/images/pictos/markers/mark-user.png',
                            scaledSize: new google.maps.Size(35, 46)
                        },
                        position : latlngPerso,
                        map : map
                    });
                }
                else{
                    marker_perso.setPosition( latlngPerso );
                }
                
                if( spots.length == 1 ){
                    bounds.extend(marker_perso.position);
                    map.fitBounds(bounds);
                }
                else{
                    map.setZoom(15);
                    map.setCenter(marker_perso.position);
                }
            },
            function(err){
                alert("La localisation n'est pas disponible pour votre configuration.\n(Vérifiez que la localisation est activée)");
            },{ enableHighAccuracy : true }
        );
    }
	*/
    function runMap() {
		var mapOptions = {
                zoom : 6,
                center : new google.maps.LatLng(47.901964, 1.908861),
                mapTypeId : google.maps.MapTypeId.ROADMAP,
                mapTypeControl : false
            },
            markerOptions,
            iconMarker = '',
            spot,
            styles = [
                {
                    featureType: "poi",
                    stylers: [
                        { visibility: "off" }
                    ]
                }
            ];
        
		map = new google.maps.Map(document.getElementById('gmap'), mapOptions);
        map.setOptions({styles : styles});

		infowindow = new google.maps.InfoWindow({
            content : "Veuillez patienter",
            pixelOffset : new google.maps.Size(0, 20)
        });

        if ("undefined" !== typeof spots && spots.length > 0) {
            bounds = new google.maps.LatLngBounds();

            for (i = 0; i < spots.length; i++) {
                var spot = spots[i],
                default_spot = {name : '', adress : '', zipcode : '', tel : '', img : '', link : ''};
                spot = $.extend(default_spot, spot);

                if(spot.gps_lat != '' && spot.gps_lng != ''){
                    // default icon
                    if("undefined" === typeof spot.icon){
                        spot.icon = {
                            size : [27, 35],
                            url : RESSOURCE_URL + '/images/pictos/marker-kerbea.png'
                        }
                    }

                    var img = '';

                    if(spot.img != ''){
                        if(spot.img.match(/^http/)){
                            img = '<img src="'+spot.img+'" style="margin-right:10px; width:90px; height:50px;"/>';
                        }
                        else{
                            img = '<img src="' + RESSOURCE_URL + '/medias/offre/galerie/vignette/'+spot.img+'" style="margin-right:10px; width:90px; height:50px;"/>';
                        }
                    }

                    html_struct = [
                        '<div class="fix_gmap">',
                            '<h1 style="font-size: 12px; font-weight: 600; text-transform: uppercase; color:#141414;">' + spot.name + '</h1>',
                            '<div class="info-spot-container" style="float:left;">',
                                '<div>'+img+'</div>',
                                '<div>'+ spot.adress + '<br />' + spot.zipcode + '<br />'+ spot.tel +'<br />' + '</div>',
                            '</div>'];

                        if(spot.link != ''){
                            html_struct.push('<div class="spot-container" style="float:right;"><br/><a href="' + spot.link + '"style="margin-right: 10px; display:inline-block; height: 32px; border-radius:8px; width:90px; background-color:#E30613;color:#fff;margin-top: 10px;text-align: center;line-height: 32px;">Détail</a></div>');
                        }

                        html_struct.push('</div>');

                    html_struct = html_struct.join('');

                    markerOptions = {
                        map : map,
                        icon : {
                            scaledSize: new google.maps.Size(spot.icon.size[0], spot.icon.size[1]),
                            url : spot.icon.url
                        },
                        index_spot : i,
                        html_content : html_struct,
                        position : new google.maps.LatLng(spot.gps_lat, spot.gps_lng)
                    };
                    marker = new google.maps.Marker(markerOptions);

                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.setContent(this.html_content);
                        infowindow.open(map, this);
                    });

                    bounds.extend(marker.position);
                    markers.push(marker);
                }
            }
            if( spots.length == 1 && 'undefined' !== typeof marker){
                map.setCenter( marker.position);
                map.setZoom(17);
            }
            else if( spots.length > 1){
                map.fitBounds(bounds);
                map.setCenter(bounds.getCenter());
            }
            else{
                map.setCenter(new google.maps.LatLng(47.100, 1.915));
                map.setZoom(5);
            }
        }

        var styles_cluster = [{
            width: 54,
            height: 54,
            url: RESSOURCE_URL + "/images/pictos/cluster.png",
            textColor: "#ffffff",
            textSize: 30,
            textFamily: "Open Sans"
        }];
        
        var mcOptions = {gridSize: 30,styles:styles_cluster, zIndex:100};
        mc = new MarkerClusterer(map,markers,mcOptions);

        /*
        jQuery( '.my-position' ).click( function (){
            getPosPerso();
        });
        */
	}

    runMap();
});
