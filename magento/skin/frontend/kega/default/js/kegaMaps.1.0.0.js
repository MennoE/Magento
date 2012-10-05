
function Map() {

	//=====================================================
	// Variables                                   ========
	//=====================================================

	// Targets
	var target = "map";
	var targetDirections = "";
	var targetLocator = "locator";

	// Map
	var map;
	var defaultZoomLevel = 7;
	var findZoomLevel = 10;
	var centerLon = 52.146973;
	var centerLat = 5.537109;
	var that = this;
	var infow;

	// Directions
	var direction;
	var directionsHtml;

	// marker icons
	var homeMarkerIcon = '/skin/frontend/kega/default/images/kega/marker_mapHuis.png';
	var defaultMarkerIcon = '/skin/frontend/kega/default/images/kega/marker_maplogo.png';
	var defaultMarkerGreyIcon = '/skin/frontend/kega/default/images/kega/marker_maplogo.png';
	var defaultMarkerIconWidth = 23;
	var defaultMarkerIconHeight = 19;
	var markerOffsetX = 20;
	var markerOffsetY = 5;

	// Route
	var route;
	var route_color= "#e63138";
	var route_weight = 4;
	var route_opacity = 0.8;

	// Arrays
	var areas = [];
	var markers = [];

	// GDirections
	var travelMode = 'driving';
	var avoidHighways = false;

	// active marker
	var activemarker;

	//=====================================================
	// Setters                                     ========
	//=====================================================

	/**
	 * @param target string;
	 * @return void;
	 */
	this.setTarget = function(tar){
		target = tar;
	}

	/**
	 * @param targetDirections string;
	 * @return void;
	 */
	this.setTargetDirections = function(tDirections){
		targetDirections = tDirections;
	}

	/**
	 * @param defaultZoomLevel string;
	 * @return void;
	 */
	this.setDefaultZoomlevel = function(zLevel){
		defaultZoomLevel = zLevel;
	}

	/**
	 * @param findZoomLevel string;
	 * @return void;
	 */
	this.setFindZoomlevel = function(zLevel){
		findZoomLevel = zLevel;
	}

	/**
	 * @param centerLon string;
	 * @return void;
	 */
	this.setCenterLon = function(cLon){
		centerLon = cLon;
	}

	/**
	 * @param centerLat string;
	 * @return void;
	 */
	this.setCenterLat = function(cLat){
		centerLat = cLat;
	}

	/**
	 * @param travelMode string;
	 * @return void;
	 */
	this.setTravelMode = function(tMode){
		travelMode = tMode == 'walking'
			? 'G_TRAVEL_MODE_WALKING'
			: 'G_TRAVEL_MODE_DRIVING'
		;
	}

	/**
	 * @param avoidHighway boolean;
	 * @return void;
	 */
	this.setTravelMode = function(aHighways){
		avoidHighWays = aHighways;
	}

	/**
	 * @param color string;
	 * @return void;
	 */
	this.setRouteColor = function(color){
		route_color = color;
	}

	/**
	 * @param weight number;
	 * @return void;
	 */
	this.setRouteWeight = function(weight){
		route_weight = weight;
	}

	/**
	 * @param opacity number;
	 * @return void;
	 */
	this.setRouteOpacity = function(opacity){
		route_opacity = opacity;
	}

	/**
	 * @param color;
	 * @return void;
	 */
	this.setRouteColor = function(color){
		route_color = color;
	}

	/**
	 * @param icon url;
	 * @return void;
	 */
	this.setHomeMarkerIcon= function(icon){
		homeMarkerIcon = icon;
	}
	/**
	 * @param icon url;
	 * @return void;
	 */
	this.setDefaultMarkerIcon= function(icon){
		defaultMarkerIcon = icon;
	}
	/**
	 * @param icon url;
	 * @return void;
	 */
	this.setDefaultMarkerGreyIcon= function(icon){
		defaultMarkerGreyIcon = icon;
	}


	//=====================================================
	// Getters                                     ========
	//=====================================================

	/**
	 * @param void;
	 * @return map Object;
	 */
	this.getMap = function(){
		return map;
	}

	/**
	 * @param void;
	 * @return directionsHtml HTML;
	 */
	this.getDirectionsHtml = function(){
		return directionsHtml;
	}

	//=====================================================
	// Public functions                            ========
	//=====================================================

	this.init = function() {
		map = new GMap(document.getElementById(target));

		var patt, results, chunk;
		patt = new RegExp(/settings{([^}]+)}/);
		results = patt.exec(document.getElementById(target).className);

		results = results[1].split(";");
		if(results){
			for(i=0, il=results.length; i<il;i++) {
				chunk = results[i].split(":");

				switch(chunk[0])
				{
					case "long":
						centerLon = parseFloat(chunk[1]);
						break;

					case "lat":
						centerLat = parseFloat(chunk[1]);
						break;

					case "zoom":
						defaultZoomLevel = parseInt(chunk[1]);
						break;
				}
			}
		}

		map.setCenter(new GLatLng(centerLon,centerLat), defaultZoomLevel);
		map.addMapType(G_PHYSICAL_MAP);

		infow = new KegaInfoWindow(map);
      	map.addOverlay(infow);
	}

	// Add controls to the map
	this.addControl = function(sort, type){

		if (sort == 'show'){
			if (type == "small"){
				map.addControl(new GSmallMapControl());
			}else if(type == "smallZoom"){
				map.addControl(new GSmallZoomControl());
			}else if(type == "large"){
				map.addControl(new GLargeMapControl());
			}
		}else if(sort == 'type'){
			if (type == "normal"){
				map.addControl(new GMapTypeControl());
			}else if(type == "menu"){
				map.addControl(new GMenuMapTypeControl());
			}else if(type == "hierarchiecal"){
				map.addControl(new GHierarchicalMapTypeControl());
			}
		}
	}

	// Add markers to the map
	this.addMarker = function(arr) {


		var icon = new GIcon();
     	icon.image = defaultMarkerIcon;

      	icon.iconSize = new GSize(defaultMarkerIconWidth, defaultMarkerIconHeight);
      	icon.iconAnchor = new GPoint(markerOffsetY, markerOffsetX);
      	icon.infoWindowAnchor = new GPoint(9, 2);
     	icon.infoShadowAnchor = new GPoint(18, 25);

     	var point = new GPoint(arr['lon'],arr['lat']);
		var marker = new GMarker(point, icon);

	    marker.x = arr['lon'];
		marker.y = arr['lat'];

		marker.name = arr['name'];
		marker.type = arr['type'];
		marker.property = arr['prop'];
		marker.place = arr['place'];
		marker.postcode = arr['postcode'];
		marker.housenumber = arr['housenumber'];
		marker.url = arr['url'];

		// pure denhaag <- ???
        var areaId = pointInArea(arr['lon'], arr['lat']);
        if (areaId >= 0){
			marker.area = areas[areaId].name;
		}

		GEvent.addListener(marker, "mouseover", function() {
            marker.openInfoWindowHtml(arr['markerHTML']);
        });

        markers.push(marker);
	    map.addOverlay(marker);
	}

	/*
	this.addMarkerOld = function(name, lon, lat, prop, markerHTML) {
		var point = new GPoint(lon,lat);
	    var marker = new GMarker(point);
	    marker.name = name;
	    marker.x = lon;
	    marker.y = lat;
	    marker.property = prop;

        var areaId = pointInArea(lon, lat);
        if (areaId >= 0){
			marker.area = areas[areaId].name;
		}

		GEvent.addListener(marker, "click", function() {
            //marker.openInfoWindowHtml(markerHTML);
            infow.open();
        });
        markers.push(marker);
	    map.addOverlay(marker);
	}
	*/

	this.center = function(lon, lat, zoom){
		map.setCenter(new GLatLng(lat, lon), zoom);
	}


	this.findStores = function(adr, radius){

		geocoder = new GClientGeocoder();
	 	geocoder.getLatLng(adr, function(point) {
	      if (!point) {
	        alert(adr + " niet gevonden");
	      } else {

	    	// go to location on map
	    	map.setCenter(point, findZoomLevel);

	    	// home icon
	    	var icon = new GIcon();
	       	icon.image = homeMarkerIcon;
	        icon.iconSize = new GSize(19, 24);
	        icon.iconAnchor = new GPoint(9, 24);
	        icon.infoWindowAnchor = new GPoint(9, 2);
	       	icon.infoShadowAnchor = new GPoint(18, 25);

	      	// home marker
	   		var marker = new GMarker(point, icon);
	     	map.addOverlay(marker);

	     	var nearMarkers = [];

	     	var ul = document.createElement("ul");
	      	for(var i=0, il=markers.length; i<il; i++){
	      		var distance = calcDist(point.y, point.x, markers[i].y, markers[i].x);
      			markers[i].setImage(defaultMarkerIcon);		// colored marker
      			markers[i].distance = distance;
      			markers[i].markerId = i;
      			nearMarkers.push(markers[i]);
	      	}

	      	// sort by distance
	      	nearMarkers.sort(function(a,b){return a.distance - b.distance});

	      	var test = '';
	      	for (var i=0; i<nearMarkers.length; i++) {
	      		test += i+ ' -> ' +nearMarkers[i].name + '(' +nearMarkers[i].place+ ")\n";

	      		var li = document.createElement("li");

	      		li.rel = nearMarkers[i].markerId;
	      		var a = document.createElement('a');
				a.innerHTML = nearMarkers[i].name+ "\n"+nearMarkers[i].place;
				a.setAttribute('href', nearMarkers[i].url);
	      		li.appendChild(a);

				spanRoute = document.createElement("span");
				spanRoute.className = 'route'
				spanRoute.rel = nearMarkers[i].markerId;;
				spanRoute.appendChild(document.createTextNode('routebeschrijving'));

				li.appendChild(spanRoute);

				ul.appendChild(li);
	      	}

			activeMarker = markers[0];
	      	that.initRoute();

      		var steps = [];
      		steps.push(point.y +',' + point.x);
      		steps.push(markers[0].y + ',' + markers[0].x);

			that.addRoute(steps);
	      }
	    }
	  );
	}

	this.toggelProperties = function(prop, type){
		for(var i=0, il=markers.length; i<il; i++){
			if (!prop){
				this.toggleMarker(i, type);
			}else if (markers[i].property.match(":" +prop+ ":")){
				this.toggleMarker(i, type);
			}
		}
	}

	this.toggleMarker = function(i, type){
		if (type == 'hide'){
			markers[i].hide();
		}else{
			markers[i].show();
		}
	}


	// Route
	this.initRoute = function(){

		direction = new GDirections(null, null);

		GEvent.addListener(direction, "error", function() {

			if (direction.getStatus().code == G_GEO_UNKNOWN_ADDRESS) {
				alert("De locatie van het opgegeven vertrekadres kon niet worden bepaald. \nFoutcode: " + direction.getStatus().code);
			}else if (direction.getStatus().code == G_GEO_SERVER_ERROR) {
				alert("Er is iets misgegaan. Maar het is onduidelijk WAT!n Foutcode: " + direction.getStatus().code);
			}else if (direction.getStatus().code == G_GEO_MISSING_QUERY) {
				alert("Een parameter ontbreekt. Misschien was er geen vertrekpunt ingevoerd. \n Foutcode: " + direction.getStatus().code);
			}else if (direction.getStatus().code == G_GEO_BAD_KEY) {
				alert("De Google Maps sleutel is ongeldig of de gebruikte sleutel is niet geldig voor dit domein.\n Foutcode: " + direction.getStatus().code);
			}else if (direction.getStatus().code == G_GEO_BAD_REQUEST) {
				alert("De aanvraag voor het plannen van een route is mislukt. \n Foutcode: " + direction.getStatus().code);
			}else {
				alert("De route kon niet worden opgebouwd.");
			}
		});

		GEvent.addListener(direction, "load", function() {

			if (targetDirections){
				direction.setDirections();
			}

			if (route){
				map.removeOverlay(route);
			}

			route = direction.getPolyline();

			// color of the line
			route.setStrokeStyle({color:route_color,weight:route_weight,opacity:route_opacity});

			map.addOverlay(route);

			var bounds = direction.getBounds();

            map.setCenter( bounds.getCenter(),(map.getBoundsZoomLevel(bounds)) );

		});

	}

	this.addRoute = function(steps, name) {
		direction.loadFromWaypoints(steps,{getPolyline:true, getSteps:true, travelMode:travelMode, avoidHighways:avoidHighways});
	}


	GDirections.prototype.setDirections = function(){
		var numRoutes = this.getNumRoutes();
		var distance = this.getDistance().html;
		var duration = this.getDuration().html;

		var tbl = document.createElement("table");
		tbl.setAttribute("border", "0");
		tbl.setAttribute("cellpadding", "0");
		tbl.setAttribute("cellspacing", "0");
		tbl.setAttribute("width", "100%");

		var tbody = document.createElement("tbody");

		for(var i=0, il=numRoutes; i<il; i++){
			var dir = this.getRoute(i);
			var numSteps = dir.getNumSteps();

			var row = document.createElement("tr");
			var cell = document.createElement("th");
			cell.setAttribute("colSpan", "3");
			cell.setAttribute("class", "point");
			cell.innerHTML = 'Van "' + dir.getStartGeocode().address + '" naar "' + activeMarker.name + '" <span>' + dir.getDistance().html + ' (' + dir.getDuration().html  + ')<span>';

            row.appendChild(cell);
			tbody.appendChild(row);
			var rowclass = 'even';
			for(var n=0, nl=numSteps; n<nl; n++){

				if (rowclass == 'even'){
					rowclass = 'uneven';
				}else{
					rowclass = 'even';
				}

				var row = document.createElement("tr");
				row.setAttribute("class", rowclass);

				var cell = document.createElement("td");
				cell.rel = n;
				cell.innerHTML =(n+1) + ':';
				cell.setAttribute("class", "nr");
           		row.appendChild(cell);

           		var cell = document.createElement("td");
           		cell.innerHTML = dir.getStep(n).getDescriptionHtml();
           		cell.rel = n;
           		row.appendChild(cell);

           		var cell = document.createElement("td");
           		cell.rel = n;
           		cell.innerHTML = dir.getStep(n).getDistance().html  + ' ' + dir.getStep(n).getDuration().html;
           		row.appendChild(cell);
           		tbody.appendChild(row);

			}

			if (i == (numRoutes-1)){
				var row = document.createElement("tr");

				var cell = document.createElement("th");
				cell.rel = n;
				cell.setAttribute("colSpan", "3");
				cell.innerHTML = 'U heeft uw bestemming (' + activeMarker.name + ', ' + dir.getEndGeocode().address + ' ' + activeMarker.place + ') bereikt.';
            	cell.setAttribute("class", "point");

           		row.appendChild(cell);
				tbody.appendChild(row);

			}
		}
		tbl.appendChild(tbody);
		/*
		GEvent.addDomListener(tbl,"click",function(e){
			var td = e.target;
			while (td.nodeName.toLowerCase() != 'td' && td.parentNode){
				td = td.parentNode;
			}

			alert(td.rel);
			//alert(dir.getStep(e.target.rel).getLatLng());
		});
		*/

		// show printputtons
		//document.getElementById('print-btn-top').className = '';
		//document.getElementById('print-btn-bottom').className = '';

		document.getElementById(targetDirections).appendChild(tbl);

	}

	//=====================================================
	// Kega info window      					   ========
	//=====================================================


	function KegaInfoWindow(map){
		this.map=map;
		this.visible = false;
		this.prototype = new GOverlay();
	}



	KegaInfoWindow.prototype.initialize = function(){

		var infowCont = document.createElement("div");
	    infowCont.style.position = "absolute";

	    var kiwContainer = document.createElement("div");
	    kiwContainer.id = 'kiwContainer';

	    var kiwLeftTop = document.createElement("div");
	    kiwLeftTop.className = 'kiwLeftTop';
	    kiwContainer.appendChild(kiwLeftTop);

	    var kiwTop = document.createElement("div");
	    kiwTop.className = 'kiwTop';
	    kiwContainer.appendChild(kiwTop)

	   	var kiwRightTop = document.createElement("div");
	    kiwRightTop.className = 'kiwRightTop';
	    kiwContainer.appendChild(kiwRightTop);

	    var kiwLeftBottom = document.createElement("div");
	    kiwLeftBottom.className = 'kiwLeftBottom';
	    kiwContainer.appendChild(kiwLeftBottom);

	    var kiwLeft = document.createElement("div");
	    kiwLeft.className = 'kiwLeft';
	    kiwContainer.appendChild(kiwLeft);

	    var kiwRightBottom = document.createElement("div");
	    kiwRightBottom.className = 'kiwRightBottom';
	    kiwContainer.appendChild(kiwRightBottom);

	    var kiwRight = document.createElement("div");
	    kiwRight.className = 'kiwRight';
	    kiwContainer.appendChild(kiwRight);

	   	var kiwBottom = document.createElement("div");
	    kiwBottom.className = 'kiwBottom';
	    kiwContainer.appendChild(kiwBottom);

	    var kiwHtml = document.createElement("div");
	    kiwHtml.className = 'kiwHtml';
	    kiwContainer.appendChild(kiwHtml);

	    infowCont.appendChild(kiwContainer);

	    map.getPane(G_MAP_FLOAT_PANE).appendChild(infowCont);
	    this.infowCont = infowCont;
	    this.hide();

	};

	KegaInfoWindow.prototype.open = function(marker){

		var vx = (marker.getIcon().iconAnchor.x - marker.getIcon().infoWindowAnchor.x-60);
        var vy = (marker.getIcon().iconAnchor.y - marker.getIcon().infoWindowAnchor.y-22);

        this.offset = new GPoint(vx,vy)||new GPoint(0,0);
        this.point = marker.getPoint();
      	this.visible = true;
      	this.show();
      	this.redraw(true);
      	this.repositionMap();
	}

	KegaInfoWindow.prototype.redraw = function(c){
		if (!this.visible) {return;}
		var p = this.map.fromLatLngToDivPixel(this.point);
	    this.infowCont.style.left   = (p.x + this.offset.x) + "px";
	    this.infowCont.style.bottom = (-p.y + this.offset.y) + "px";

	    GEvent.addListener(map, "click", function(overlay,point) {
	    	if (!overlay) {
	          infow.hide();
	        }
	    });

	};

	KegaInfoWindow.prototype.show = function() {
		this.infowCont.style.display="";
		this.visible = true;
	}

	KegaInfoWindow.prototype.hide = function() {
		this.infowCont.style.display="none";
		this.visible = false;
	}

	KegaInfoWindow.prototype.repositionMap = function(){

		var mapNE = map.fromLatLngToDivPixel(map.getBounds().getNorthEast());
		var mapSW = map.fromLatLngToDivPixel(map.getBounds().getSouthWest());

		var kiwWidth = this.infowCont.offsetWidth;
		var kiwHeight = this.infowCont.offsetHeight;

		var markerPosition = map.fromLatLngToDivPixel(this.point);

		var difW = ((markerPosition.x + kiwWidth)-60);

		if (mapNE.x < difW){
			var panX = -(difW - mapNE.x);
		}else{
		    if(mapSW.x > (markerPosition.x - 60)){
		    	var panX = mapSW.x - (markerPosition.x - 60);
		    }else{
		    	var panX = 0;
		    }
		}

		var difH = (markerPosition.y - (-kiwHeight )+ 90);
		if (mapSW.y > difH){
			var panY = mapSW.y - difH;
		}else{
			var panY = 0;
		}

		map.panBy(new GSize(panX,panY));

	};

	GMarker.prototype.closeExtInfoWindow = function(map) {
		alert('test');
	};
	//=====================================================
	// Puur denhaag functions                      ========
	//=====================================================


	this.addAreas = function(name, pts, color) {
		var points = [];

		for(var i=0, il=pts.length/2; i<il; i++){
			var pt = new GLatLng(pts[i * 2], pts[i * 2 + 1])
			points.push(pt);
		}
		points.push(points[0]);

		var polygon = new GPolygon(points, color, 2, 1, color,  0.25, true);
		polygon.name = name;

		areas.push(polygon);
		map.addOverlay(polygon);

		GEvent.addListener(map, "mousemove", function (p) {
//			var areaId = pointInArea(p.lng(), p.lat());
//			if (areaId >= 0){
//				document.getElementById("message").innerHTML = "--> " + areas[areaId].name;
//			}
		});
	}

	this.toggelAreas = function(area, type){

		for(var i=0, il=markers.length; i<il; i++){
			if (!area){
				this.toggleMarker(i, type);
			}else if (markers[i].area == area){
				this.toggleMarker(i, type);
			}else if (area == 'out' && !markers[i].area){
				this.toggleMarker(i, type);
			}
		}
	}


	//=====================================================
	// Private functions                           ========
	//=====================================================


	function pointInArea(x, y){
		for(var q=0, ql=areas.length; q<ql; q++){
			var j=0;
			var oddNodes = false;
			for(var i=0, il=areas[q].getVertexCount(); i<il; i++){
				j++;
				if (j == areas[q].getVertexCount()) {
					j = 0;
				}
				if (((areas[q].getVertex(i).lat() < y) && (areas[q].getVertex(j).lat() >= y)) || ((areas[q].getVertex(j).lat() < y) && (areas[q].getVertex(i).lat() >= y))) {
					if ( areas[q].getVertex(i).lng() + (y - areas[q].getVertex(i).lat()) / (areas[q].getVertex(j).lat()-areas[q].getVertex(i).lat()) *  (areas[q].getVertex(j).lng() - areas[q].getVertex(i).lng())<x ) {
					  oddNodes = !oddNodes
					}
				}
			}
			if (oddNodes){return q;}
		}
	}

	// Calculate dictance

	function toRad(x) {  // convert degrees to radians
		return x * Math.PI / 180;
	}


	function calcDist(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var dLat = toRad(lat2-lat1);
		var dLon = toRad(lon2-lon1);
		lat1 = toRad(lat1), lat2 = toRad(lat2);

		var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
		      Math.cos(lat1) * Math.cos(lat2) *
		      Math.sin(dLon/2) * Math.sin(dLon/2);
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
		var d = R * c;
		return d;
	}
}









//
//	this.addJSheader = function (api){
//		var headID = document.getElementsByTagName("head")[0];
//		var newScript = document.createElement('script');
//		newScript.type = 'text/javascript';
//		newScript.src = "http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAe1_1F11hJI72f7AeVHt2dhTYDFUCrjNtoGxZn-FxKkIQx8MitRQuandJKNDQAzviZ8hs1MZZS7R6EQ";
//		headID.appendChild(newScript);
//	}

