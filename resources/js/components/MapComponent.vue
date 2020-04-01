
<template>
    <div class = "w-100">
        <div id = "map" style = "height: 70vh;" >
        </div>
        <!--location-details-modal :type="typeOfFigure" :centroid = "centroid">
        </location-details-modal-->
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
import area from '@turf/area'
//import * as turf from '@turf/turf'
//import 'plotty';
//import GeoTIFF from 'geotiff';
//import 'leaflet-geotiff/leaflet-geotiff';
//import 'leaflet-geotiff/leaflet-geotiff-plotty';
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
            "optimize": {
                "capacity": "max",
                "energy": "min"
            },
            "constraints": {
                "c1": {"max": 100},
                "c2": {"max": 30},
                "c3": {"max": 50}
            },
            "variables": {
                "x": {
                    "energy": 10,
                    "capacity": 20,
                    "c1": 10,
                    "c2": 1,
                    "c3": 0
                },
                "y": {
                    "energy": 5,
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
            attribution: 'Map data ©2020 Google',
            subdomains:['mt0','mt1','mt2','mt3']
        });

        var googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });

        //var mqi = L.tileLayer("http://{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {subdomains: ['otile1','otile2','otile3','otile4']});
        var tiles = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });
        /*
        var tiles =  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '©OpenStreetMap'
        })
        */
        var labels = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
                attribution: '©OpenStreetMap, ©CartoDB',
        })
        /*
        const solar = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif', {
            renderer: new L.LeafletGeotiff.Plotty({
                colorScale: 'rainbow',
                clampLow: false,
                clampHigh: false,
                displayMin: 600,
                displayMax: 2000,
            })
        })
        */
        let legendSolar = null
        let legendEolic = null
        /*
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
        */
        
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
            'Tiles': tiles
        };

        var overlayMaps = {
            'Labels': labels
        };

        Vue.prototype.$map.on('baselayerchange', (e) => {
            this.layerActive = e.name
        });
        

        L.control.layers(baseMaps, overlayMaps).addTo(Vue.prototype.$map);

        const provider = new OpenStreetMapProvider();

        const searchControl = new GeoSearchControl({
            showMarker: false,
            provider: provider,
        })

        Vue.prototype.$map.addControl(searchControl);
        
        this.initDraw()
    },
    methods: {
        async initDraw(){
            
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
            
            await axios.get('/zones').then(response=>{
                if(response.data.length){
                    response.data.forEach(element => {
                        var data = JSON.parse(element.feature)
                        var geometry = L.geoJSON(data,{
                            onEachFeature: (feature,layer) => {
                                layer._leaflet_id = element.id
                                layer.options.color = "#bada55"
                                layer.options.weight = 4
                                layer.options.opacity = 0.5
                                layer.options.fillOpacity = 0.2
                                Vue.prototype.$drawnItems.addLayer(layer)
                                this.$root.$emit('zonesCreated',layer._leaflet_id)
                            }
                        })
                    })
                }
            })

            

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
                var polygon = layer.toGeoJSON();
                this.$root.$emit('zoneCreated',polygon)
                this.$bvModal.show('add-edit-zone')
            });
            Vue.prototype.$map.on(L.Draw.Event.EDITED, (e) => {
                var edit = Object.keys(e.layers._layers)
                for(var i = 0 ; i < edit.length ; i++ ){
                    var layer = e.layers._layers[edit[i]];
                    var polygon = layer.toGeoJSON();
                    var polygon_for_db = JSON.stringify(polygon);
                    axios.put(`/zones/${edit[i]}`,{'feature' : polygon_for_db,'area': area(polygon)}).then((response)=>{
                        layer._leaflet_id = response.data
                        this.$bvToast.toast( 'Data edited successfully',{
                            title: 'Confirmation',
                            variant: 'success',
                            autoHideDelay: 2000,
                            solid: true
                        })
                    }).catch( e => {
                        if(e.response.data.errors.area){
                            this.$bvToast.toast( e.response.data.errors.area[0] + ' Please, reload this page.',{
                                title: 'Confirmation',
                                variant: 'danger',
                                autoHideDelay: 50000,
                                solid: true
                            })
                        }else{
                            this.$bvToast.toast( 'An error has ocurred. Please, reload this page.',{
                                title: 'Confirmation',
                                variant: 'danger',
                                autoHideDelay: 50000,
                                solid: true
                            })
                        }
                    });
                }
            });
            Vue.prototype.$map.on(L.Draw.Event.DELETED, (e) => {
                var del = Object.keys(e.layers._layers)
                for(var i = 0 ; i < del.length ; i++ ){
                    axios.delete(`/zones/${del[i]}`).then((response)=>{
                        this.$root.$emit('bv::refresh::table','zone-table')
                        this.$bvToast.toast( 'Data deleted successfully',{
                            title: 'Confirmation',
                            variant: 'success',
                            autoHideDelay: 2000,
                            solid: true
                        })
                    }).catch( e => {
                        this.$bvToast.toast( 'An error has ocurred',{
                            title: 'Confirmation',
                            variant: 'danger',
                            autoHideDelay: 2000,
                            solid: true
                        })
                    });
                }
            });
        },
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
