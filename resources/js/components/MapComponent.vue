
<template>
    <div class = "w-100">
        <div id = "map" style = "height: 70vh;" >
            <div v-if="mapLoading" id = "mapLoading" class="d-flex justify-content-center align-items-center">
                <b-spinner variant="primary"></b-spinner>
            </div>
        </div>
    </div>
</template>

<script>
  
import { latLng, Icon, icon, Polygon } from 'leaflet'
import 'leaflet-draw'
import 'leaflet-draw/dist/leaflet.draw.css'
import area from '@turf/area'
import TILES from '@/tiles'
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';

export default {
    
    data () {
        return {
            map: null,
            typeOfFigure: null,
            centroid: null,
            mapLoading: false,
            layerActive: 'Satellite',
            isDrawing: false
        }
    },
    mounted() {
        let vm = this
        var southWest = L.latLng(23.1886107447, -74.1780248685),
        northEast = L.latLng(19.8554808619, -84.9749110583),
        bounds = L.latLngBounds(southWest, northEast);
        const mapLoading = Vue.observable({ mapLoading: {} })
        Object.defineProperty(Vue.prototype, '$mapLoading', {
            get () {
                return mapLoading.mapLoading
            },
            
            set (value) {
                mapLoading.mapLoading = value
            }
        })
        Vue.prototype.$map = L.map('map', {
            center: bounds.getCenter(),
            zoomControl: false, 
            zoom: 6.4, 
            minZoom: 6.4,
            layers: [TILES['googleSat'],TILES['labels']]
        });

        L.control.zoom({
            zoomInTitle: this.__('Zoom in'),
            zoomOutTitle: this.__('Zoom out')
        }).addTo(this.$map)

        Vue.prototype.$map.setMaxBounds(bounds);
        Vue.prototype.$zone_layer = {} 
        var baseMaps = {
            "Satellite": TILES['googleSat'],
            'Tiles': TILES['openStreetMap'] 
        };

        var overlayMaps = {
            'Labels': TILES['labels']
        };

        Vue.prototype.$map.on('baselayerchange', (e) => {
            this.layerActive = e.name
        });
        
        L.control.layers(baseMaps, overlayMaps).addTo(this.$map);
        
        this.$root.$on('show-map-loading',()=>{
            this.mapLoading = true
        });
        this.$root.$on('hide-map-loading',()=>{
            this.mapLoading = false
        });
        this.initDraw()
    },
    methods: {
        async initDraw(){
            
            L.drawLocal.draw.toolbar.buttons.polygon = this.__('Draw a polygon')
            L.drawLocal.draw.toolbar.buttons.rectangle = this.__('Draw a rectangle')
            L.drawLocal.edit.toolbar.buttons.edit = this.__('Edit zones')
            L.drawLocal.edit.toolbar.buttons.remove = this.__('Delete zones')
            
            L.drawLocal.edit.toolbar.actions.save={
                text: this.__('Save'),
                title: this.__('Save changes')
            }
            L.drawLocal.edit.toolbar.actions.cancel={
                text: this.__('Cancel'),
                title: this.__('Cancel editing, discards all changes'),
            }
            L.drawLocal.edit.handlers.edit.tooltip = {
                text: this.__('Drag handles or markers to edit features.'),
                subtext: this.__('Click cancel to undo changes.')
            }
            L.drawLocal.edit.handlers.remove.tooltip = {
                text: this.__('Click on a feature to remove.')
            }
            L.drawLocal.draw.toolbar.actions = {
                text: this.__('Cancel'),
                title: this.__('Cancel drawing')
            }

            L.drawLocal.draw.toolbar.finish = {
                text: this.__('Finish'),
                title: this.__('Finish drawing')
            }

            L.drawLocal.draw.toolbar.undo = {
                text: this.__('Delete last point'),
                title: this.__('Delete last point')
            }
                
            
            L.drawLocal.draw.handlers.polygon.tooltip = {
                start: this.__('Click to start drawing shape.'), 
                cont: this.__('Click to continue drawing shape.'),
                end: this.__('Click first point to close this shape.'),
            }
            L.drawLocal.draw.handlers.rectangle.tooltip = {
                start: this.__('Click and drag to draw rectangle.')
            }
            L.drawLocal.draw.handlers.simpleshape.tooltip = {
                end: this.__('Release mouse to finish drawing.')
            }
            
            Vue.prototype.$drawnItems = new L.FeatureGroup();
            this.$map.addLayer(this.$drawnItems);
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
                    remove: false
                }
            };

            var drawControl = new L.Control.Draw(
                options
            );

            this.$map.addControl(drawControl);
            
            await axios.get('/zones').then(response=>{
                if(response.data.length){
                    response.data.forEach(element => {
                        var data = JSON.parse(element.feature)
                        var geometry = L.geoJSON(data,{
                            onEachFeature: (feature,layer) => {
                                layer.zone_id = element.id
                                layer.options.color = "#bada55"
                                layer.options.weight = 4
                                layer.options.opacity = 0.5
                                layer.options.fillOpacity = 0.2
                                /*
                                layer.bindTooltip(element.name,{
                                    permanent: true,
                                    direction: 'center'
                                })
                                */
                                this.$drawnItems.addLayer(layer)
                                this.$zone_layer[element.id] = layer._leaflet_id
                                layer.on('click', (e) => {
                                    this.$root.$emit('zoneEdited',element.id);
                                    this.$bvModal.show('add-edit-zone'); 
                                })
                            }
                        })
                    })
                }
            })
    
            this.$map.on(L.Draw.Event.CREATED, (e) => {
                var type = e.layerType
                var layer = e.layer;
                var polygon = layer.toGeoJSON();
                this.$root.$emit('zoneCreated',polygon)
                this.$bvModal.show('add-edit-zone')
            });
            this.$map.on(L.Draw.Event.EDITED, (e) => {
                var edit = Object.keys(e.layers._layers)
                for(var i = 0 ; i < edit.length ; i++ ){
                    var layer = e.layers._layers[edit[i]];
                    var zone_id = e.layers._layers[edit[i]].zone_id;
                    var polygon = layer.toGeoJSON();
                    var polygon_for_db = JSON.stringify(polygon);
                    axios.put(`/zones/${zone_id}`,{'feature' : polygon_for_db,'area': area(polygon)}).then((response)=>{
                        //layer._leaflet_id = response.data
                        layer.zone_id = response.data
                        this.$bvToast.toast( `${this.__('Data edited successfully')}`,{
                            title: `${this.__('Confirmation')}`,
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
        }
    },
    watch: {
        
    },
    computed:{
        
    }
}
</script>

<style>

@import "~leaflet/dist/leaflet.css";
@import "~leaflet-geosearch/assets/css/leaflet.css";
#mapLoading{
    background-color: black;
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1001;
    opacity: 0.6;
}
#map {
    width: 100%;
}
.leaflet-retina .leaflet-control-layers-toggle{
    height: 26px;
    width: 26px;
    background-size: 15px 15px;
}
.rotateimg90 {
  -webkit-transform:rotate(90deg);
  -moz-transform: rotate(90deg);
  -ms-transform: rotate(90deg);
  -o-transform: rotate(90deg);
  transform: rotate(90deg);
}
</style>
