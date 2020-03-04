var SssSp100 = { aMarkerDB: [], dLayersOverlay: {}, layers: {}, layersBookmarks: {}, aMarkers: [] };

//console.log('version 20200304_0231');

SssSp100.addControlLayer = function() {

	var dLayersBase;

	// add layer control

	dLayersBase = {
		//'Background': this.layers.tiledBackground
	};

	this.dLayersOverlay = {
		'Current Position':		this.layers.groupCurrentPos,
		'Goto':					this.layers.groupGoto,
	};

	// TODO: dynamically add bookmarks layers

	this.oLeafletLayerControl = L.control.layers(dLayersBase, this.dLayersOverlay);
	this.oLeafletLayerControl.addTo(this.oMap);

	// also make overlay layers visible... this is only needed when not add them in initMapIn()
/*
	var sKey, oLayer;
	for (sKey in dLayersOverlay) {

		// important check to avoid prototype properties...maybe this is one of the rare cases where it doesn't matter....just do it to avoid missing it when it does count.
		if (dLayersOverlay.hasOwnProperty(sKey)) {
			oLayer = dLayersOverlay[sKey];
			oLayer.addTo(this.oMap);
		} // if is a key we want

	} // loop all overlay layers
*/

} // addControlLayer


SssSp100.addControlMouse = function() {

	this.oMouseControl = L.control.mousePosition({
		separator: ' | ',
		numDigits: 0,
		lngFirst: true
	});
	this.oMouseControl.addTo(this.oMap);

} // addControlMouse


SssSp100.buildLayers = function() {

	this.layers.groupCurrentPos = L.layerGroup();
	this.layers.groupGoto = L.layerGroup();

	// TODO: build bookmark layers

} // buildLayers


SssSp100.clearMarkers = function() {

	this.layers.groupCurrentPos.clearLayers();
	this.layers.groupGoto.clearLayers();

	// TODO: clear bookmark layers

} // clearMarkers


SssSp100.createMarkers = function() {

	var iCount, lMarker;
	var oMarker, oPos, oPosDir;

	// create markers

	for(iCount = 0; iCount < this.aMarkerDB.length; iCount++) {

		lMarker = this.aMarkerDB[iCount];

		dLayerIcon = this.layerForMarkerType(lMarker.typeID);

		if (dLayerIcon.hasCustomIcon) {
			lMarker.options['icon'] = this.gtaVCicon(lMarker.typeID);
		} // if got icon

		oPos = this.coordTransformMineTestToLeaflet(lMarker.posX, lMarker.posY);

		oMarker = L.marker(oPos, lMarker.options);
		//oMarker.bindPopup(this.popupFormForID(iCount));

		//oMarker.on('dragend', this.updateCoordinatesOfMarkersPopup, this);
		//oMarker.on('mouseover', this.onMarkerMouseEntered, this);
		//oMarker.on('mouseout', this.onMarkerMouseExited, this);

		this.aMarkers[iCount] = oMarker;

	} // loop all markers adding them to their layer group

	this.newestMarker = oMarker;

} // createMarkers


SssSp100.executeIn = function(sElementID) {

	this.mapCenter = [ 0, 0 ];
	this.mapBounds = [[ -32000, -32000 ], [ 32000, 32000 ]];
	this.mapBoundsExtended = [[ -32500, -32500 ], [ 32500, 32500 ]];

	this.buildLayers();
	this.initMapIn(sElementID);
	this.createMarkers();
	this.addControlLayer();
	this.addControlMouse();

	this.redraw();

	window.onload = function(e){SssSp100.initLocalStorage();};

	return true;

} // executeIn


SssSp100.haveLocalStorage = function() {

	try {

		return 'localStorage' in window && window['localStorage'] !== null;

	} catch(e){

		return false;

	} // catch exception e.g. FireFox without cookies

} // haveLocalStorage


SssSp100.initLocalStorage = function() {

	if ( ! this.haveLocalStorage()) {

		this.oLStorage = null;
		return;

	} // if no local storage functionality

	//console.log('haveLocalStorageCapability');

	/* HTML5 localStorage in a nutshell
	 * Store:
	 * 		localStorage.lastname = "Smith";
	 * Retrieve:
	 * 		localStorage.lastname;
	 * Remove:
	 * 		localStorage.removeItem("lastname");
	 *
	 * more dynamic syntax:
	 * Store:
	 * 		localStorage.setItem("lastname", "Smith");
	 * Retrieve:
	 * 		localStorage.getItem("lastname");
	 */

	var aOverlaysVisible, oLatLng, iIndex, oLayer, sKey;

	this.oLStorage = window.localStorage;

	if ( ! this.oLStorage.SssSp100overlaysVisible) {

		aOverlaysVisible = Array.apply(null, new Array(Object.keys(this.dLayersOverlay).length)).map(Number.prototype.valueOf,1);

		this.oLStorage.SssSp100overlaysVisible = aOverlaysVisible.join(',');

	} else {

		aOverlaysVisible = this.oLStorage.SssSp100overlaysVisible.split(',');
		iIndex = 0;
		for (sKey in this.dLayersOverlay) {

			if (this.dLayersOverlay.hasOwnProperty(sKey)) {

				if ('0' == aOverlaysVisible[iIndex]) {

					oLayer = this.dLayersOverlay[sKey];

					if (oLayer)	this.oMap.removeLayer(oLayer);

				} // if turn off

				iIndex++;

			} // if an actual property of this instance

		} // loop all layers

	} // if got overlaysVisible

	if ( ! this.oLStorage.SssSp100mapZoomTiled) {

		this.oLStorage.SssSp100mapZoomTiled = this.oMap.getZoom();

	} else {

		if ( ! this.oLStorage.SssSp100mapCentreTiled) {

			oLatLng = this.oMap.getCenter();
			this.oLStorage.SssSp100mapCentreTiled = oLatLng.lat.toString() + ',' + oLatLng.lng.toString();

		} else {

			// got centre and zoom
			oLatLng = L.latLng(this.oLStorage.SssSp100mapCentreTiled.split(','));
			this.oMap.setView(oLatLng , Number(this.oLStorage.SssSp100mapZoomTiled, { reset: true }));

		} // if got map centre setting

	} // if got map zoom setting

/*
	this.oMap.on('zoomend', this.onMapViewChanged, this);
	this.oMap.on('moveend', this.onMapViewChanged, this);
	this.oMap.on('overlayadd', this.onMapOverlayShown, this);
	this.oMap.on('overlayremove', this.onMapOverlayHidden, this);

	this.redraw();
*/

} // initLocalStorage


SssSp100.initMapIn = function(sElementID) {

	// initialize the map on the "map" div with a given center and zoom

	this.oMap = L.map(sElementID, {
		center: this.mapCenter,
		zoom: -6,
		minZoom: -10,
		maxZoom: 6,
		layers: [
			this.layers.groupCurrentPos,
			this.layers.groupGoto,
			// TODO: add bookmark layers
		],
		maxBounds: this.mapBoundsExtended,
		crs: L.CRS.Simple
	});

	/*
	this.oMap.setView([ 0,0 ], -6);
	var southWest = this.oMap.unproject([ -512, this.mapHeight + 512 ], this.oMap.getMaxZoom());
	var northEast = this.oMap.unproject([ this.mapWidth + 512, -512 ], this.oMap.getMaxZoom());
	var oLLB = new L.LatLngBounds(southWest, northEast);

	this.oMap.setMaxBounds(oLLB);
	*/

} // initMapIn


SssSp100.redraw = function() {
//console.log('redraw triggered');

	// clear all markers
	//this.clearMarkers();
//console.log('redraw DONE------------------------------------------')
	return this;

} // redraw
/*
			var dOptions = {
				alt: '',
				riseOnHover: true,
				title: '-32k,-32k',
				zIndexOffset: 0
			};
			var oMarker0 = L.marker([-32000, -32000], dOptions).addTo(oLayerGoto);
			dOptions.title = '-31.999k,-31.999k';
			var oMarker3 = L.marker([-31999, -31999], dOptions).addTo(oLayerGoto);
			dOptions.title = '-32k,-31.999k';
			var oMarker3 = L.marker([-32000, -31999], dOptions).addTo(oLayerGoto);
			dOptions.title = '32k,-32k';
			var oMarker1 = L.marker([32000, -32000], dOptions).addTo(oLayerGoto);
			dOptions.title = '32k,32k';
			var oMarker2 = L.marker([32000, 32000], dOptions).addTo(oLayerGoto);
			dOptions.title = '-32k,32k';
			var oMarker3 = L.marker([-32000, 32000], dOptions).addTo(oLayerGoto);
*/
