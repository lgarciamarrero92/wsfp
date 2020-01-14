<template>
    <b-card 
        no-body
    >
        <b-tabs card>
            <b-tab title="Zones" active>
                <div v-if="zoneID.length > 0" >
                    <b-card no-body>
                        <b-tabs pills card>
                            <b-tab v-for="(item,index) in zoneID" :key = index :title='"Zone " + (index+1)' @click="zoomZone(item)" >
                                <div class="row">
                                    <div class="col-7">
                                        <h5>
                                            <b-badge variant="primary">
                                                Area: 
                                                <span v-html="convert(area[item])">
                                                    
                                                </span>
                                            </b-badge>
                                        </h5>
                                        <h5>
                                            <b-badge variant="primary">
                                                Mean solar potential: 
                                                <span v-html="solarPot[item]">
                                                    
                                                </span>
                                            </b-badge>
                                        </h5>
                                        <h5>
                                            <b-badge variant="primary">
                                                Mean wind potential: 
                                                <span v-html="eolicPot[item]">
                                                    
                                                </span>
                                            </b-badge>
                                        </h5>
                                    </div>
                                    <div class="col-5 text-center">
                                        <b-dropdown 
                                        class="w-100"
                                            id="solar" 
                                            no-caret 
                                            lazy 
                                            variant="link" 
                                            v-b-tooltip.hover 
                                            title="Solar panels to be considered in this zone"
                                        >
                                            <template v-slot:button-content >
                                                <img 
                                                    src="icon_solar.png" 
                                                    style="width: 30px; height: 30px;"
                                                >
                                            </template>
                                            <b-dropdown-form>
                                                <div v-if="dropDownBusy" class = "text-center">
                                                    <b-spinner small variant="primary" ></b-spinner>
                                                </div>
                                                <b-form-checkbox-group v-if="!dropDownBusy" v-model="solarPanelsSelected[item]" :options="solarPanels">

                                                </b-form-checkbox-group>
                                            </b-dropdown-form>
                                        </b-dropdown>

                                        <b-dropdown 
                                            id="eolic" 
                                            no-caret 
                                            lazy 
                                            variant="link" 
                                            v-b-tooltip.hover 
                                            title="Wind turbines to be considered in this zone"
                                        >
                                            <template v-slot:button-content >
                                                <img 
                                                    src="icon_eolic.jpg" 
                                                    style="width: 30px; height: 30px;"
                                                >
                                            </template>
                                            <b-dropdown-form>
                                                <div v-if="dropDownBusy" class = "text-center">
                                                    <b-spinner small variant="primary" ></b-spinner>
                                                </div>
                                                <b-form-checkbox-group v-if="!dropDownBusy" v-model="windTurbinesSelected[item]" :options="windTurbines">

                                                </b-form-checkbox-group>
                                            </b-dropdown-form>
                                        </b-dropdown>
                                    </div>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </b-card>
                </div>
                <b-alert v-else show variant="info"> Draw an area in Map for start!!</b-alert>
            </b-tab>
            <b-tab title="Results"><b-card-text>Is coming....</b-card-text></b-tab>
            <b-tab title="Solar panels">
                <solar-table />
            </b-tab>
            <b-tab title="Wind turbines">
                <wind-table />
            </b-tab>
        </b-tabs>
        <b-card-footer>
            <b-button variant="primary" @click="$bvModal.show('add-panel')"> Add solar panel</b-button>
            <b-button variant="primary" @click="$bvModal.show('add-turbine')"> Add eolic turbine</b-button>
            <b-button variant="primary"> Run simulations</b-button>
        </b-card-footer>
    </b-card>
</template>

<script>
    import * as turf from '@turf/turf'
    import 'leaflet-geotiff/leaflet-geotiff';
    export default {
        data () {
            return {
                solarPanelsSelected: {},
                solarPanels: [],
                windTurbinesSelected: {},
                windTurbines: [],
                dropDownBusy: false,
                zoneID: [],
                distributions: {},
                solarPot: {},
                eolicPot: {},
                area: {},
                solar: null,
                eolic: null,
                cfiec1: null,
                cfiec2: null,
                cfiec3: null,
                pvout: null
            }
        },
        props:{
        },
        created(){
            Vue.prototype.$user_id = this.user_id
        },
        mounted() {
            
            this.$root.$on('bv::dropdown::show', evt => {
                if(evt.componentId == "solar"){
                    this.getSolarPanels()
                }
                if(evt.componentId == "eolic"){
                    this.getWindTurbines()
                }
            })

            this.solar = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/GTI.tif')
            this.eolic = new L.leafletGeotiff('CUB_power-density_50m.tif')
            this.cfiec1 = new L.leafletGeotiff('CUB_capacity-factor_IEC1.tif')
            this.cfiec2 = new L.leafletGeotiff('CUB_capacity-factor_IEC2.tif')
            this.cfiec3 = new L.leafletGeotiff('CUB_capacity-factor_IEC3.tif')
            this.pvoutput = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif')
            
            Vue.prototype.$map.on(L.Draw.Event.CREATED, (e) => {
                console.log(e)
                var id = e.layer._leaflet_id
                this.zoneID = Object.keys(Vue.prototype.$drawnItems._layers)
                this.update(id)
            })
            
            Vue.prototype.$map.on(L.Draw.Event.EDITED, (e) => {
                var edit = Object.keys(e.layers._layers)
                this.zoneID = Object.keys(Vue.prototype.$drawnItems._layers)
                for(var i = 0 ; i < edit.length ; i++ ){
                    if(this.distributions[edit[i]])
                        for(var j = 0 ; j < this.distributions[edit[i]].length; j++ ){
                            this.distributions[edit[i]][j].removeFrom(Vue.prototype.$map)
                        }
                    this.update(edit[i])
                }
                
            })
            
            Vue.prototype.$map.on(L.Draw.Event.DELETED, (e) => {
                var del = Object.keys(e.layers._layers)
                for(var i = 0 ; i < del.length ; i++ ){
                    if(this.distributions[del[i]])
                        for(var j = 0 ; j < this.distributions[del[i]].length; j++ ){
                            this.distributions[del[i]][j].removeFrom(Vue.prototype.$map)
                        }
                }
                this.zoneID = Object.keys(Vue.prototype.$drawnItems._layers)
            })
        },
        methods: {
            getWindCoef(turbine,locationId){
                
            },
            getSolarPanels(){
                this.dropDownBusy = true
                return axios.get('/solar_panels').then( (response) => {
                    let items = []
                    response.data.forEach(element => {
                        items.push({value: element.id, text: element.model}) 
                    });
                    this.solarPanels = items
                    this.dropDownBusy = false
                    return items;
                })
            },
            getWindTurbines(){
                this.dropDownBusy = true
                return axios.get('/wind_turbines').then( (response) => {
                    let items = []
                    response.data.forEach(element => {
                        items.push({value: element.id, text: element.model}) 
                    });
                    this.windTurbines = items
                    this.dropDownBusy = false
                    return items;
                })
            },
            update(item){
                this.area[item] = this.calcArea(item)
                this.solarPot[item] = this.solarPotential(item);
                this.eolicPot[item] = this.windPotential(item);
            },
            zoomZone(item){
                let bounds = Vue.prototype.$drawnItems._layers[item]._bounds
                Vue.prototype.$map.fitBounds(bounds);
            },
            calcArea(item){
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
                var area = this.calcArea(item)
                var d = 1
                if(area > 10000){
                    d = Math.sqrt(area)/100.0
                }
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var bbox = turf.bbox(polygon);
                var options = {units: 'meters',mask: polygon};
                var grid = turf.pointGrid(bbox, d , options);
                var mean = this.solar.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0])
                for( var i = 0 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    mean += this.solar.getValueAtLatLng(p[1],p[0])
                }
                mean /= (grid.features.length+1)
                return `${mean.toFixed(2)} kWh/m<sup>2</sup>`
            },
            windPotential(item){
                //var eolicIcon = L.icon({
                //    iconUrl: 'icon_eolic.png',
                //    iconSize: [30, 30],
                //    iconAnchor: [15,30]
                //})
                //var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                //var bbox = turf.bbox(polygon);
                //var bboxPolygon = turf.bboxPolygon(bbox);
                //L.geoJSON(bboxPolygon).addTo(Vue.prototype.$map);
                //var cellSide = 5;
                //var options = {units: 'meters',mask: polygon};
                //var grid = turf.pointGrid(bbox, cellSide, options);
                /*
                for( var i = 0 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    if(i>0 && p[0] != grid.features[i-1].geometry.coordinates[0]){
                        console.log(i)
                    }
                    L.marker([p[1],p[0]],{icon: eolicIcon}).addTo(Vue.prototype.$map)
                }
                */
                //console.log(grid)
                //L.geoJSON(grid).addTo(Vue.prototype.$map);
                var centroid = this.centroid(item)
                var area = this.calcArea(item)
                var d = 1
                if(area > 10000){
                    d = Math.sqrt(area)/100.0
                }
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var bbox = turf.bbox(polygon);
                var options = {units: 'meters',mask: polygon};
                var grid = turf.pointGrid(bbox, d , options);
                var mean = this.eolic.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0])
                
                for( var i = 0 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    mean += this.eolic.getValueAtLatLng(p[1],p[0])
                }
                mean /= (grid.features.length+1)
                
                //this.maxTurbines(item, 0.15 )
                //this.maxPanels(item,0.70,1.31)
                return `${(mean*8.760).toFixed(2)} kWh/m<sup>2</sup>`
            },
            maxTurbines(item,mind){//mind in kilometers
                var centroid = this.centroid(item)
                var area = this.calcArea(item)
                var d = 1
                if(area > 10000){
                    d = Math.sqrt(area)/100.0
                }
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var bbox = turf.bbox(polygon);
                var options = {units: 'meters',mask: polygon};
                var grid = turf.pointGrid(bbox, d , options);
                var locations = []
                locations.push(grid.features[0])
                for( var i = 1 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i]
                    var n = locations.length;
                    var belongs = true
                    for( var j = n-1 ; j >= 0 ; j-- ){
                        if(turf.distance(p, locations[j]) <= mind ){
                            belongs = false;
                            break;
                        }
                    }
                    if(belongs == true ){
                        locations.push(p);
                    }
                }
                /*
                if(locations.length <= 100){
                    for( var i = 0 ; i < locations.length; i++ ){
                        var p = locations[i].geometry.coordinates
                        L.marker([p[1],p[0]]).addTo(Vue.prototype.$map)
                    }
                }
                console.log('Locations: ' + locations.length)
                */
            },
            maxPanels(item,w,h){
                var polygon = Vue.prototype.$drawnItems._layers[item].toGeoJSON();
                var bbox = turf.bbox(polygon);
                var options = {units: 'meters',mask: polygon};
                var grid = turf.pointGrid(bbox, h*1.42*2 , options);
                var rows = []
                rows.push(grid.features[0])
                for( var i = 1 ; i < grid.features.length; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    var belongs = true
                    for(var j = 0 ; j < rows.length ; j++ ){
                        if(p[1] == rows[j].geometry.coordinates[1]){
                            belongs = false;
                            break
                        }
                    }
                    if(belongs){
                        rows.push(grid.features[i])
                    }
                }
                
                var bboxpoly = turf.bboxPolygon(bbox);
                var numberOfPanels = 0
                for( var i = 0 ; i < rows.length; i++ ){
                    var p = rows[i]
                    while( turf.booleanPointInPolygon(p, bboxpoly) ){
                        if(turf.booleanPointInPolygon(p, polygon)){
                            rows[i] = p
                        }
                        var distance = w/1000.0; //Kilometers
                        var bearing = -90;
                        p = turf.destination(p, distance, bearing);
                    }
                }
                var collection = []
                for( var i = 0 ; i < rows.length; i++ ){
                    var numberInRow = 0
                    var p = rows[i]
                    var fp = p
                    var lp = p
                    while( turf.booleanPointInPolygon(p, bboxpoly) ){
                        if(turf.booleanPointInPolygon(p, polygon)){
                            lp = p
                            numberInRow += 2
                            if(fp == -1 ){
                                fp = p
                            }
                        }else{
                            if( lp != fp ){
                                numberOfPanels += (numberInRow+2)
                                var cr = turf.destination(lp, h/1000.0, 0);
                                var cl = turf.destination(fp, h/1000.0, 0);
                                var points = turf.featureCollection([fp,lp,cl,cr]);
                                var bbPolygon = turf.convex(points);
                                collection.push(L.geoJSON(bbPolygon).bindPopup(numberInRow+2 + ' panels').addTo(Vue.prototype.$map));
                                numberInRow = 0;
                            }
                            fp = -1;
                            lp = -1;
                        }
                        //   L.marker( [p.geometry.coordinates[1], p.geometry.coordinates[0] ]).addTo(Vue.prototype.$map)
                        var distance = w/1000.0; //Kilometers
                        var bearing = 90;
                        p = turf.destination(p, distance, bearing);
                    }
                    if( lp != fp ){
                        numberOfPanels += (numberInRow+2)
                        var cr = turf.destination(lp, h/1000.0, 0);
                        var cl = turf.destination(fp, h/1000.0, 0);
                        var points = turf.featureCollection([fp,lp,cl,cr]);
                        var bbPolygon = turf.convex(points);
                        collection.push(L.geoJSON(bbPolygon).bindPopup(numberInRow+2 + ' panels').addTo(Vue.prototype.$map));
                        /*
                        L.geoJSON(
                            bbPolygon,
                            {
                                onEachFeature: function(f,l){
                                    l.bindPopup(numberInRow+2 + ' panels')
                                }
                            }
                        ).addTo(Vue.prototype.$map);
                        */
                        numberInRow = 0;
                    }
                }
                this.distributions[item] = collection
                return (numberOfPanels)
            }
        }
    }
</script>
