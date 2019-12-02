
<template>
    <div class = "w-100">
        <div id = "map" style = "height: 500px;" >
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
//import 'esri-leaflet'
//import 'esri-leaflet-geocoder'
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';
export default {
    data () {
        return {
            map: null,
            typeOfFigure: null,
            centroid: null
        }
    },
    mounted() {

        let vm = this
        
        var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });

        var googleTerrain = L.tileLayer('http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });

        var tiles = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png?api_key=2cf458ad-eab8-465d-95dd-7b7d2f098322', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
        })

        this.map = L.map('map', {
            center: [0,0], 
            zoom: 2, 
            layers: [googleSat]
        });

        const provider = new OpenStreetMapProvider();

        const searchControl = new GeoSearchControl({
            showMarker: false,
            provider: provider,
        })

        this.map.addControl(searchControl);

        var drawnItems = new L.FeatureGroup();

        this.map.addLayer(drawnItems);

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
                featureGroup: drawnItems, //REQUIRED!!
            }
        };

        var drawControl = new L.Control.Draw(
            options
        );

        this.map.addControl(drawControl);

        this.map.on(L.Draw.Event.CREATED, function (e) {
            var type = e.layerType
            var layer = e.layer;
            drawnItems.addLayer(layer);
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
            layer.on('click',function(){
                vm.typeOfFigure = type;
                var centroid = turf.centroid(polygon)
                vm.centroid = centroid.geometry.coordinates
                Vue.nextTick( function () {
                    vm.$bvModal.show('modal-center')
                })
            });
        });

        /*
        const freeDraw = new FreeDraw({ mode: NONE });
        this.map.addLayer(freeDraw);
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
        }).addTo( this.map );

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
        }).addTo( this.map );
        */
    },
    methods: {
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
                        //L.geoJSON(rigth).addTo(this.map);
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
                        //L.geoJSON(left).addTo(this.map);
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
                        //L.geoJSON(up).addTo(this.map);
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
                        //L.geoJSON(down).addTo(this.map);
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
