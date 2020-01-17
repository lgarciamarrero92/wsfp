<template>
    <b-card 
        no-body
    >
        <b-tabs v-model="tabIndex" card>
            <b-tab title="Zones">
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
                                    <div class="col-5 ">
                                        <div class = "row">
                                            <b-dropdown 
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
                                        </div>
                                        <div class="row">
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
                                                        src="icon_eolic.png" 
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
                                </div>
                            </b-tab>
                        </b-tabs>
                    </b-card>
                </div>
                <b-alert v-else show variant="info"> Draw an area in Map for start!!</b-alert>
            </b-tab>
            <b-tab title="Results">
                <b-alert v-if="Object.keys(results).length == 0" show variant="info"> Nothing to show</b-alert>
                <div v-if="Object.keys(results).length > 0">
                    <h5>
                        <b-badge variant="success">
                            Energy generated: 
                            <span>
                                {{results["energy"].toFixed(2)}} kWh/year
                            </span>
                        </b-badge>
                    </h5>
                    <h5>
                        <b-badge variant="success">
                            Total costs: 
                            <span>
                                $ {{results["costs"]}}
                            </span>
                        </b-badge>
                    </h5>
                    <b-card no-body>
                        <b-tabs pills card>
                            <b-tab v-for="(item,index) in zoneID" :key = index :title='"Zone " + (index+1)' @click="zoomZone(item)" >
                                <div v-if="results[item]">
                                    <h5>
                                        <b-badge variant="primary">
                                            Renewable source: 
                                            <span>
                                                {{results[item]["source"]}}
                                            </span>
                                        </b-badge>
                                    </h5>
                                    <div v-if="results[item]['source']=='solar'">
                                        <h5>
                                            <b-badge variant="primary">
                                                Install panel model: 
                                                <span>
                                                    {{results[item]['technology'].model}}
                                                </span>
                                            </b-badge>
                                        </h5>
                                        <h5>
                                            <b-badge variant="primary">
                                                Number of panels: 
                                                <span>
                                                    {{results[item]['number']}}
                                                </span>
                                            </b-badge>
                                        </h5>
                                    </div>
                                    <div v-if="results[item]['source']=='eolic'"> 
                                        <h5>
                                            <b-badge variant="primary">
                                                Install turbine model: 
                                                <span>
                                                    {{results[item]['technology'].model}}
                                                </span>
                                            </b-badge>
                                        </h5>
                                        <h5>
                                            <b-badge variant="primary">
                                                Number of turbines: 
                                                <span>
                                                    {{results[item]['number']}}
                                                </span>
                                            </b-badge>
                                        </h5>
                                    </div>
                                </div>
                                <div v-else>
                                    <b-alert show variant="info"> Nothing to show</b-alert>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </b-card>
                </div>
            </b-tab>
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
            <b-button variant="primary" @click="makeModel()" > Run simulations</b-button>
        </b-card-footer>
    </b-card>
</template>

<script>
    import * as turf from '@turf/turf'
    import 'leaflet-geotiff/leaflet-geotiff';
    export default {
        data () {
            return {
                tabIndex: 0,
                results: {},
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
            this.pvout = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif')
            
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
            decodeSolution(sol,wt,sp){
                this.results = {}
                let keys = Object.keys(sol)
                for (let index = 0; index < keys.length; index++) {
                    const key = keys[index];
                    if(key.includes("$1$") && !key.includes("beta")){
                        let model = key.split("$1$")[1]
                        let zid = key.split("$1$")[2]
                        let id = key.split("$1$")[0]
                        this.maxPanels(zid,sp[id].width,sp[id].height,1,sol[key])
                        this.results[zid] = {}
                        this.results[zid]["source"] = "solar"
                        this.results[zid]["technology"] = sp[id]
                        this.results[zid]["number"] = sol[key]
                        //console.log("In zone " + zid + " Install " + sol[key] + " items of model " + model + " of solar panels")
                    }
                    if(key.includes("$2$") && !key.includes("beta")){
                        let model = key.split("$2$")[1]
                        let zid = key.split("$2$")[2]
                        let id = key.split("$2$")[0]
                        this.maxTurbines(zid,wt[id].rotor_diameter,1,sol[key])
                        this.results[zid] = {}
                        this.results[zid]["source"] = "eolic"
                        this.results[zid]["technology"] = wt[id]
                        this.results[zid]["number"] = sol[key]
                        //console.log("In zone " + zid + " Install " + sol[key] + " items of model " + model + " of wind turbines")
                    }
                }
                this.results['energy'] = sol["energy"]
                this.results['costs'] = sol["costs"]
                //console.log(this.results)
                //console.log("It will produce "+  + "kWh " + "and costs "+sol["costs"])
            },
            async makeModel(){
                var solver = require("javascript-lp-solver/src/solver")
                //Save all facilities
                let sp = {}
                let wt = {}
                await axios.get('/solar_panels').then( (response) => {
                    for (let index = 0; index < response.data.length; index++) {
                        const element = response.data[index];
                        sp[element.id] = element
                    }
                })
                await axios.get('/wind_turbines').then( (response) => {
                    for (let index = 0; index < response.data.length; index++) {
                        const element = response.data[index];
                        wt[element.id] = element
                    }
                })
                var model = {
                    "optimize": {
                        "energy": "max",
                        "costs": "min"
                    },
                }
                model["constraints"] = {}
                model["variables"] = {}
                model["ints"] = {}
                for (let i = 0; i < this.zoneID.length; i++) {
                    const item = this.zoneID[i]
                    const oneConstName = '$oneConstName$' + item
                    model["constraints"][oneConstName] = {"max":1}
                    if(this.solarPanelsSelected[item]){
                        for (let j = 0; j < this.solarPanelsSelected[item].length; j++) {
                            const spid = this.solarPanelsSelected[item][j]
                            if(sp[spid]){
                                const varName = sp[spid].id + '$1$' + sp[spid].model + '$1$' + item
                                const betaVarName = '$beta$' + varName
                                const beta1ConstName = '$beta1c$' + varName 
                                model["constraints"][beta1ConstName] = {"min": 0}
                                const beta2ConstName = '$beta2c$' + varName
                                model["constraints"][beta2ConstName] = {"max": 0}
                                const beta3ConstName = '$beta3c$' + varName
                                model["constraints"][beta3ConstName] = {"max": 1}
                                const mx = this.maxPanels(item,sp[spid].width,sp[spid].height,0)
                                const mxFacilinZoneName = '$mxFacilinZoneName$' + varName
                                model["constraints"][mxFacilinZoneName] = {"max": mx}
                                model["variables"][varName] = {}
                                model["variables"][varName]["energy"] = this.getSolarEnergy(item,sp[spid])
                                model["variables"][varName]["costs"] = sp[spid].invest_cost
                                model["variables"][varName][beta1ConstName] = -1
                                model["variables"][varName][beta2ConstName] = -1
                                model["variables"][varName][mxFacilinZoneName] = 1
                                model["variables"][betaVarName] = {}
                                model["variables"][betaVarName][beta1ConstName] = mx+1
                                model["variables"][betaVarName][beta2ConstName] = 1
                                model["variables"][betaVarName][beta3ConstName] = 1
                                model["variables"][betaVarName][oneConstName] = 1
                                model["ints"][varName] = 1
                                model["ints"][betaVarName] = 1
                            }
                        }
                    }
                    if(this.windTurbinesSelected[item]){
                        for (let j = 0; j < this.windTurbinesSelected[item].length; j++) {
                            const spid = this.windTurbinesSelected[item][j]
                            if(wt[spid]){
                                const varName = wt[spid].id + '$2$' + wt[spid].model + '$2$' + item
                                const betaVarName = '$beta$' + varName
                                const beta1ConstName = '$beta1c$' + varName 
                                model["constraints"][beta1ConstName] = {"min": 0}
                                const beta2ConstName = '$beta2c$' + varName
                                model["constraints"][beta2ConstName] = {"max": 0}
                                const beta3ConstName = '$beta3c$' + varName
                                model["constraints"][beta3ConstName] = {"max": 1}
                                const mx = this.maxTurbines(item,wt[spid].rotor_diameter,0)
                                const mxFacilinZoneName = '$mxFacilinZoneName$' + varName
                                model["constraints"][mxFacilinZoneName] = {"max": mx}
                                model["variables"][varName] = {}
                                model["variables"][varName]["energy"] = this.getWindEnergy(item,wt[spid])
                                model["variables"][varName]["costs"] = wt[spid].invest_cost
                                model["variables"][varName][beta1ConstName] = -1
                                model["variables"][varName][beta2ConstName] = -1
                                model["variables"][varName][mxFacilinZoneName] = 1
                                model["variables"][betaVarName] = {}
                                model["variables"][betaVarName][beta1ConstName] = mx+1
                                model["variables"][betaVarName][beta2ConstName] = 1
                                model["variables"][betaVarName][beta3ConstName] = 1
                                model["variables"][betaVarName][oneConstName] = 1
                                model["ints"][varName] = 1
                                model["ints"][betaVarName] = 1
                            }
                        }
                    }
                }
                //console.log(model)
                let results = solver.Solve(model);
                this.decodeSolution(results["vertices"][0],wt,sp)
                //console.log(results);
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
            getSolarEnergy(item,tec){
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
                var mean = this.pvout.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0])
                for( var i = 0 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    mean += this.pvout.getValueAtLatLng(p[1],p[0])
                }
                mean /= (grid.features.length+1)
                
                return mean*(tec.nominal_power/1000.0)
            },
            getWindEnergy(item,tec){
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
                var layer = null
                if(tec.iec_class == 1){
                    layer = this.cfiec1
                }else if(tec.iec_class == 2){
                    layer = this.cfiec2
                }else{
                    layer = this.cfiec3
                }
                var mean = layer.getValueAtLatLng(centroid.geometry.coordinates[1],centroid.geometry.coordinates[0])
                for( var i = 0 ; i < grid.features.length ; i++ ){
                    var p = grid.features[i].geometry.coordinates
                    mean += layer.getValueAtLatLng(p[1],p[0])
                }
                mean /= (grid.features.length+1)
                
                return mean*tec.nominal_power*8760.0
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
            maxTurbines(item,diameter,see,maxT=0){//mind in kilometers
                var mind = (5.0*diameter)/1000
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
                if(see){
                    var windTurbineIcon = L.icon({
                        iconUrl: 'icon_eolic.png',
                        iconSize: [50, 50],
                        iconAnchor: [25,50]
                    })
                    for( var i = 0 ; i < (locations.length) && (i<maxT); i++ ){
                        var p = locations[i].geometry.coordinates
                        L.marker([p[1],p[0]],{icon: windTurbineIcon}).addTo(Vue.prototype.$map)
                    }
                }
                return (locations.length)
            },
            maxPanels(item,w,h,see,maxP = 0){
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
                                
                                var cr = turf.destination(lp, h/1000.0, 0);
                                var cl = turf.destination(fp, h/1000.0, 0);
                                var points = turf.featureCollection([fp,lp,cl,cr]);
                                var bbPolygon = turf.convex(points);
                                if(see && numberOfPanels < maxP )
                                    collection.push(L.geoJSON(bbPolygon).bindPopup(Math.min(numberInRow+2,maxP-numberOfPanels) + ' panels').addTo(Vue.prototype.$map));
                                numberOfPanels += (numberInRow+2)
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
                        
                        var cr = turf.destination(lp, h/1000.0, 0);
                        var cl = turf.destination(fp, h/1000.0, 0);
                        var points = turf.featureCollection([fp,lp,cl,cr]);
                        var bbPolygon = turf.convex(points);
                        if(see && numberOfPanels < maxP )
                            collection.push(L.geoJSON(bbPolygon).bindPopup(Math.min(numberInRow+2,maxP-numberOfPanels) + ' panels').addTo(Vue.prototype.$map));
                        numberOfPanels += (numberInRow+2)
                        numberInRow = 0;
                    }
                }
                this.distributions[item] = collection
                return (numberOfPanels)
            }
        }
    }
</script>
