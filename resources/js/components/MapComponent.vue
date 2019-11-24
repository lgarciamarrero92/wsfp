
<template>

  <div id = "map" style = "height: 500px;" >
  </div>

</template>

<script>
  
import { latLng, Icon, icon, Polygon } from 'leaflet'
//import '@geoman-io/leaflet-geoman-free';
//import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import FreeDraw, { ALL, CREATE, EDIT, DELETE, NONE} from 'leaflet-freedraw';
import 'leaflet-easybutton'
export default {
    data () {
        
        return {
        map: null,
        }
    },
    mounted() {
        //console.log(this.locations)
        //Map Setup
        
        var tiles = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png?api_key=2cf458ad-eab8-465d-95dd-7b7d2f098322', {
        maxZoom: 18,
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
        })

        this.map = L.map('map', {
            center: [0,0], 
            zoom: 1.5, 
            layers: [tiles]
        });

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

    },
}
</script>

<style>

@import "~leaflet/dist/leaflet.css";
#map {
    width: 100%;
}
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
</style>
