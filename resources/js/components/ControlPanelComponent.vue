<template>
    <b-card no-body>
        <b-tabs card>
            <b-tab title="Zones" active>
                <div v-if="zoneID.length > 0" >
                    <b-card no-body>
                        <b-tabs pills card>
                            <b-tab v-for="(item,index) in zoneID" :key = index :title='"Zone " + (index+1)' @click="zoomZone(item)" >
                                <h5>
                                    <b-badge variant="primary">
                                        Area: 
                                        <span v-html="convert(area(item))">
                                            
                                        </span>
                                    </b-badge>
                                </h5>
                                <h5>
                                    <b-badge variant="primary">
                                        Solar potential: 
                                        <span v-html="solarPotential(item)">
                                            
                                        </span>
                                    </b-badge>
                                </h5>
                                <h5>
                                    <b-badge variant="primary">
                                        Wind velocity: 
                                        <span v-html="windVelocity(item)">
                                            
                                        </span>
                                    </b-badge>
                                </h5>
                            </b-tab>
                        </b-tabs>
                    </b-card>
                </div>
                <b-alert v-else show variant="info"> Draw an area in Map for start!!</b-alert>
            </b-tab>
            <b-tab title="Results"><b-card-text>Tab contents 2</b-card-text></b-tab>
            <b-tab title="Custom data" disabled><b-card-text>Tab contents 3</b-card-text></b-tab>
        </b-tabs>
    </b-card>
</template>

<script>
    import * as turf from '@turf/turf'
    import 'leaflet-geotiff/leaflet-geotiff';
    export default {
        data () {
            return {
                zoneID: [],
                solar: null,
                eolic: null
            }
        },
        mounted() {
            this.solar = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/GTI.tif')
            this.eolic = new L.leafletGeotiff('CUB_wind-speed_50m.tif')

            Vue.prototype.$map.on(L.Draw.Event.CREATED, (e) => {
                this.update()
            })
            
            Vue.prototype.$map.on(L.Draw.Event.EDITED, (e) => {
                this.update()
            })
            
            Vue.prototype.$map.on(L.Draw.Event.DELETED, (e) => {
                this.update()
            })
        },
        methods: {
            update(){
                this.zoneID = Object.keys(Vue.prototype.$drawnItems._layers)
            },
            zoomZone(item){
                let bounds = Vue.prototype.$drawnItems._layers[item]._bounds
                Vue.prototype.$map.fitBounds(bounds);
            },
            area(item){
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var area = turf.area(polygon);
                return area;
            },
            centroid(item){
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var centroid = turf.centroid(polygon);
                return centroid;
            },
            convert(area){
                if(area <= 10000){
                    return `${area.toFixed(0)} m<sup>2</sup>`
                }else{
                    return (area/10000.0).toFixed(2) + ' ha'
                }
            },
            solarPotential(item){
                var centroid = this.centroid(item)
                return `${this.solar.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0]).toFixed(2)} kWh/m<sup>2</sup>`
            },
            windVelocity(item){
                var centroid = this.centroid(item)
                return `${this.eolic.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0]).toFixed(2)} m/s`
            }
        }
    }
</script>
