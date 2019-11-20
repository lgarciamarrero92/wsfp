
<template>
  <div id = "map" style = "height: 500px;" >
  </div>

</template>

<script>
  
  import { latLng, Icon, icon } from 'leaflet'
  import '@geoman-io/leaflet-geoman-free';
  import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
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
      this.map.pm.addControls({
        position: 'topleft'
      });
    },
  }
</script>

<style>
 
  @import "~leaflet/dist/leaflet.css";

  html, body {
    height: 100%;
    margin: 0;
  }
  a.leaflet-bar-part.leaflet-bar-part-single{
    display: flex;
    align-items: center;
    justify-content: center;
  }
  span.fa-map-marker.fa{
    font-size: 20px;
     color: black;
  }
  span.fa-spinner.fa-spin.fa{
     font-size: 20px;
     color: black;
  }
  .leaflet-control-geosearch a.reset{
    color: black !important;
  }
  .loading{
    height: 100%;
    width: 100%;
    position: absolute;
    margin: 0;
    opacity: 0.6;
    z-index: 1001;
    background-color: black;
  }
</style>
