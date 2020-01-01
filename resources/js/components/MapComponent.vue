
<template>
    <div class = "w-100">
        <div id = "map" style = "height: 70vh;" >
        </div>
        <location-details-modal :type="typeOfFigure" :centroid = "centroid">
        </location-details-modal>
    </div>
</template>

<script>
  
import { latLng, Icon, icon, Polygon } from 'leaflet'
//import '@geoman-io/leaflet-geoman-free';
//import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
//import FreeDraw, { ALL, CREATE, EDIT, DELETE, NONE} from 'leaflet-freedraw';
//import 'leaflet-easybutton'
import 'leaflet-draw'
import 'leaflet-draw/dist/leaflet.draw.css'
import * as turf from '@turf/turf'
import 'plotty';
import GeoTIFF from 'geotiff';
import 'leaflet-geotiff/leaflet-geotiff';
import 'leaflet-geotiff/leaflet-geotiff-plotty';
//import 'esri-leaflet'
//import 'esri-leaflet-geocoder'
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';
export default {
    data () {
        return {
            map: null,
            typeOfFigure: null,
            centroid: null,
            layerActive: 'Satellite',
            isDrawing: false
        }
    },
    mounted() {
        //Test linear solver
        var solver = require("javascript-lp-solver/src/solver"),
        results,
        model = {
            "optimize": "capacity",
            "opType": "max",
            "constraints": {
                "c1": {"max": 100},
                "c2": {"max": 30},
                "c3": {"max": 50}
            },
            "variables": {
                "x": {
                    "capacity": 20,
                    "c1": 10,
                    "c2": 1,
                    "c3": 0
                },
                "y": {
                    "capacity": 30,
                    "c1": 1,
                    "c2": 0,
                    "c3": 1
                }
            },
            "ints": {"x": 1, "y": 1}
        };

        results = solver.Solve(model);
        console.log(results);
        //end test
        let vm = this
        
        var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });

        var googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });

        //var mqi = L.tileLayer("http://{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {subdomains: ['otile1','otile2','otile3','otile4']});

        var tiles =  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png', {
                attribution: '©OpenStreetMap, ©CartoDB'
        })
        
        var labels = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
                attribution: '©OpenStreetMap, ©CartoDB',
        })

        const solar = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif', {
            renderer: new L.LeafletGeotiff.Plotty({
                colorScale: 'rainbow',
                clampLow: false,
                clampHigh: false,
                displayMin: 600,
                displayMax: 2000,
            })
        })
        let legendSolar = null
        let legendEolic = null
        solar.on('load',()=>{
            
        })
        const eolic = new L.leafletGeotiff('CUB_wind-speed_50m.tif', {
            opacity: .1,
            renderer: new L.LeafletGeotiff.Plotty({
                colorScale: 'rainbow',
                clampLow: false,
                clampHigh: false,
                displayMin: 0,
                displayMax: 10,
            })
            
        })
        
        var southWest = L.latLng(23.1886107447, -74.1780248685),
        northEast = L.latLng(19.8554808619, -84.9749110583),
        bounds = L.latLngBounds(southWest, northEast);
        
        Vue.prototype.$map = L.map('map', {
            center: bounds.getCenter(), 
            zoom: 6.4, 
            minZoom: 6.4,
            layers: [googleSat,labels]
        });

        Vue.prototype.$map.setMaxBounds(bounds);

        var baseMaps = {
            "Satellite": googleSat,
            "Streets": tiles,
            "Solar": solar,
            "Eolic": eolic
        };

        var overlayMaps = {
            'Labels': labels
        };

        Vue.prototype.$map.on('baselayerchange', (e) => {
            this.layerActive = e.name
            if(this.layerActive == "Eolic" || this.layerActive == "Solar"){
                this.disableMap()
                if( this.layerActive == "Solar" ){
                    if(legendEolic)
                        Vue.prototype.$map.removeControl(legendEolic)
                    legendSolar = L.control({position: 'bottomleft'});
                    legendSolar.onAdd = function (map) {
                        const div = L.DomUtil.create('div','solar legend');
                        let mn = solar.options.renderer.options.displayMin;
                        let mx = solar.options.renderer.displayMax;
                        div.innerHTML += '<img id="colorScaleImage" src=' + solar.options.renderer.colorScaleData + " style='vertical-align: middle; height:20px; width:300px;'/>";
                        div.innerHTML += '<br>'
                        for (let index = 0; index < 10; index++) {
                            div.innerHTML += '<span style = "writing-mode: vertical-rl; text-orientation: mixed;margin-right: 12px; margin-top: 2px;" >' +  mn + '</span>'
                            mn += 140;
                        }
                        div.innerHTML += '<span style = "margin-right: 12px; margin-top: 2px;" >kWh/kWp </span>'
                        return div;
                    };
                    legendSolar.onRemove = function (map){
                        let div = L.DomUtil.remove('solar legend');
                        return ;
                    };
                    legendSolar.addTo(Vue.prototype.$map)
                }else{
                    if(legendSolar)
                        Vue.prototype.$map.removeControl(legendSolar)
                    legendEolic = L.control({position: 'bottomleft'});
                    legendEolic.onAdd = function (map) {
                        const div = L.DomUtil.create('div','eolic legend');
                        let mn = eolic.options.renderer.options.displayMin;
                        div.innerHTML += '<img id="colorScaleImage" src=' + eolic.options.renderer.colorScaleData + " style='vertical-align: middle; height:20px; width:300px;'/>";
                        div.innerHTML += '<br>'
                        for (let index = 0; index < 10; index++) {
                            div.innerHTML += '<span style = "writing-mode: vertical-rl; text-orientation: mixed;margin-right: 12px; margin-top: 2px;" >' +  mn + '</span>'
                            mn += 1;
                        }
                        div.innerHTML += '<span style = "margin-right: 12px; margin-top: 2px;" >m/s </span>'
                        return div;
                    };
                    legendEolic.onRemove = function (map){
                        let div = L.DomUtil.remove('eolic legend');
                        return ;
                    };
                    legendEolic.addTo(Vue.prototype.$map)
                }
            }else{
                if(legendEolic)
                        Vue.prototype.$map.removeControl(legendEolic)
                if(legendSolar)
                        Vue.prototype.$map.removeControl(legendSolar)
                this.enableMap()
            }
        });

        Vue.prototype.$map.on('click',(e)=>{
            if(this.layerActive == 'Solar' && this.isDrawing == false){
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent("Photovoltaic power output: " + solar.getValueAtLatLng(e.latlng.lat,e.latlng.lng) + " kWh/kWp" )
                    .openOn(Vue.prototype.$map)
            }else if(this.layerActive == 'Eolic' && this.isDrawing == false){
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent("Wind velocity: " + eolic.getValueAtLatLng(e.latlng.lat,e.latlng.lng) + " m/s" )
                    .openOn(Vue.prototype.$map) 
            }
        })

        

        L.control.layers(baseMaps, overlayMaps).addTo(Vue.prototype.$map);

        const provider = new OpenStreetMapProvider();

        const searchControl = new GeoSearchControl({
            showMarker: false,
            provider: provider,
        })

        Vue.prototype.$map.addControl(searchControl);

        Vue.prototype.$drawnItems = new L.FeatureGroup();

        Vue.prototype.$map.addLayer(Vue.prototype.$drawnItems);

        var options = {
            position: 'topright',
            draw: {
                marker: false,
                circlemarker: false,
                polyline: false,
                polygon: {
                    showArea: true,
                    showLength: true,
                    allowIntersection: false, // Restricts shapes to simple polygons
                    drawError: {
                        color: '#e1e100', // Color the shape will turn when intersects
                        message: '<strong>Oh snap!<strong> you can\'t draw that!' // Message that will show when intersect
                    },
                    shapeOptions: {
                        color: '#bada55'
                    }
                },
                circle: false, // Turns off this drawing tool
                rectangle: {
                    shapeOptions: {
                        color: '#bada55'
                    }
                }
            },
            edit: {
                featureGroup: Vue.prototype.$drawnItems, //REQUIRED!!
            }
        };

        var drawControl = new L.Control.Draw(
            options
        );

        Vue.prototype.$map.addControl(drawControl);

        Vue.prototype.$map.on(L.Draw.Event.DRAWSTART, (e) => {
            this.isDrawing = true
        })
        Vue.prototype.$map.on(L.Draw.Event.EDITSTART, (e) => {
            this.isDrawing = true
        })

        Vue.prototype.$map.on(L.Draw.Event.DRAWSTOP, (e) => {
            this.isDrawing = false
        })
        Vue.prototype.$map.on(L.Draw.Event.EDITSTOP, (e) => {
            this.isDrawing = false
        })

        Vue.prototype.$map.on(L.Draw.Event.CREATED, (e) => {
            var type = e.layerType
            var layer = e.layer;
            Vue.prototype.$drawnItems.addLayer(layer);
            console.log(Vue.prototype.$drawnItems)
            var polygon = layer.toGeoJSON();
            //var points1 = vm.generatePositions(polygon,45)
            //var points = turf.featureCollection(points1)
            //var hull = turf.convex(points);
            //L.geoJSON(hull).addTo(vm.map);
            /*
            var bbox = turf.bbox(polygon);
            var points = turf.randomPoint(25, {bbox: bbox})
            //console.log(bbox);
            var centroid = turf.centroid(polygon)
            L.geoJSON(points).addTo(vm.map);
            L.geoJSON(centroid).addTo(vm.map);
            /*
            for (let index = 0; index < 50; index++) {
                var pointOnPolygon = turf.pointOnFeature(polygon);
                L.geoJSON(pointOnPolygon).addTo(vm.map);
            }
            */
            //console.log(pointOnPolygon)
            layer.on('click', () => {
                if(this.layerActive != 'Solar' && this.layerActive != 'Eolic'){
                    vm.typeOfFigure = type;
                    var centroid = turf.centroid(polygon)
                    vm.centroid = centroid.geometry.coordinates
                    Vue.nextTick( function () {
                        vm.$bvModal.show('modal-center')
                    })
                }
            });
        });

        /*
        const freeDraw = new FreeDraw({ mode: NONE });
        Vue.prototype.$map.addLayer(freeDraw);
        freeDraw.on('markers', event => {
            console.log(event)
            var polygon = L.polygon(event.latLngs[0])
            polygon.on('click',function(){
                console.log('clicked')
            })
        });
        var editButton = L.easyButton({
            id: 'edit',
            states: [{
                stateName: 'edit',        // name the state
                icon:      'fa-pencil-alt',               // and define its properties
                title:     'Edit geometries',      // like its title
                onClick: function(btn, map) {       // and its callback
                    freeDraw.mode(ALL);
                    btn.state('cancel-edit');    // change state on click!
                }
            }, 
            {
                stateName: 'cancel-edit',
                icon:      '<span><i class="fas fa-times fa-xs" style="color:Tomato"></i><i class="fas fa-pencil-alt"></i></span>',
                title:     'Cancel edit',
                onClick: function(btn, map) {
                    freeDraw.mode(NONE);
                    btn.state('edit');
                }
            }]
        }).addTo( Vue.prototype.$map );

        var addDetailsButton = L.easyButton({
            id: 'add-details',
            states: [{
                stateName: 'add-details',        // name the state
                icon:      'fa-solar-panel',               // and define its properties
                title:     'Add details',      // like its title
                onClick: function(btn, map) {       // and its callback
                    
                    btn.state('cancel-add-details');    // change state on click!
                }
            }, 
            {
                stateName: 'cancel-add-details',
                icon:      '<span><i class="fas fa-times fa-xs" style="color:Tomato"></i><i class="fas wind-turbine"></i></span>',
                title:     'Cancel add details',
                onClick: function(btn, map) {
                    btn.state('add-details');
                }
            }]
        }).addTo( Vue.prototype.$map );
        */
    },
    methods: {
        disableMap(){
            Vue.prototype.$map._handlers.forEach(function(handler) {
                handler.disable();
            });
        },
        enableMap(){
            Vue.prototype.$map._handlers.forEach(function(handler) {
                handler.enable();
            });
        },
        generatePositions(polygon, angle){
            var initialPoint = turf.centroid(polygon)
            var queue = []
            var see = []
            var positions = []
            queue.push({
                p: initialPoint,
                x: 0,
                y: 0
            })
            positions.push(initialPoint)
            see.push({
                x:0,
                y:0
            })
            var cn = 0 
            while( queue.length > 0 ){
                cn++
                //if(cn == 1000)break
                var item = queue.shift()
                var options = {units: 'meters'};
                //Rigth
                if(!see.some(obj => obj.x == item.x+1 && obj.y == item.y)){
                    var rigth = turf.destination(item.p, 2 , angle, options);
                    angle += 90
                    if( Math.abs(angle) > 180 ){
                        angle -= 360
                    }
                    see.push({
                        x: item.x+1,
                        y: item.y
                    })
                    if( turf.booleanPointInPolygon(rigth, polygon) ){
                        //L.geoJSON(rigth).addTo(Vue.prototype.$map);
                        queue.push({
                            p: rigth,
                            x: item.x+1,
                            y: item.y
                        })
                        positions.push(rigth)
                    }
                }
                //Left
                if(!see.some(obj => obj.x == item.x-1 && obj.y == item.y)){
                    var left = turf.destination(item.p, 2 , angle, options);
                    angle += 90
                    if( Math.abs(angle) > 180 ){
                        angle -= 360
                    }
                    see.push({
                        x: item.x-1,
                        y: item.y
                    })
                    if( turf.booleanPointInPolygon(left, polygon) ){
                        //L.geoJSON(left).addTo(Vue.prototype.$map);
                        queue.push({
                            p: left,
                            x: item.x-1,
                            y: item.y
                        })
                        positions.push(left)
                    }
                }
                //up
                if(!see.some(obj => obj.x == item.x && obj.y == item.y+1)){
                    var up = turf.destination(item.p, 2 , angle, options);
                    angle += 90
                    if( Math.abs(angle) > 180 ){
                        angle -= 360
                    }
                    see.push({
                        x: item.x,
                        y: item.y+1
                    })
                    if( turf.booleanPointInPolygon(up, polygon) ){
                        //L.geoJSON(up).addTo(Vue.prototype.$map);
                        queue.push({
                            p: up,
                            x: item.x,
                            y: item.y+1
                        })
                        positions.push(up)
                    }
                }
                //down
                if(!see.some(obj => obj.x == item.x && obj.y == item.y-1)){
                    var down = turf.destination(item.p, 2 , angle, options);
                    angle += 90
                    if( Math.abs(angle) > 180 ){
                        angle -= 360
                    }
                    see.push({
                        x: item.x,
                        y: item.y-1
                    })
                    if( turf.booleanPointInPolygon(down, polygon) ){
                        //L.geoJSON(down).addTo(Vue.prototype.$map);
                        queue.push({
                            p: down,
                            x: item.x,
                            y: item.y-1
                        })
                        positions.push(down)
                    }
                }
                
                //console.log( item.x + " " + item.y )
            }
            return positions
        }
    }
}
</script>

<style>

@import "~leaflet/dist/leaflet.css";
@import "~leaflet-geosearch/assets/css/leaflet.css";

#map {
    width: 100%;
}
.rotateimg90 {
  -webkit-transform:rotate(90deg);
  -moz-transform: rotate(90deg);
  -ms-transform: rotate(90deg);
  -o-transform: rotate(90deg);
  transform: rotate(90deg);
}
/*
#map.mode-create {
    cursor: crosshair !important;
}

.leaflet-edge {
    background-color: #95bc59;
    box-shadow: 0 0 0 2px white, 0 0 10px rgba(0, 0, 0, .35);
    border-radius: 50%;
    cursor: move;
    outline: none;
    transition: background-color .25s;
}

.leaflet-polygon {
    fill: #b4cd8a;
    stroke: #50622b;
    stroke-width: 2;
    fill-opacity: .75;
    cursor: url( '/icons/times.svg' ), auto;
}
*/
</style>
