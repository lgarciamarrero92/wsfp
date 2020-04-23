<template>
    <div>
        <busy-modal :progress="progress"></busy-modal>
        <b-card 
            no-body
        >
            <b-tabs v-model="tabIndex" card >
                <b-tab :title="__('Zones')">
                    <zone-table />
                </b-tab>
                <b-tab :title="__('Solar panels')">
                    <solar-table />
                </b-tab>
                <b-tab :title="__('Wind turbines')">
                    <wind-table />
                </b-tab>
                <b-tab :title="__('Run simulations')">
                    <b-form class = "row">
                        <b-form-group
                            class="col-6"
                            id="size-group"
                            :label="__('Poblation size')"
                        >
                            <b-form-input 
                                id="size" 
                                size="sm" 
                                type="number" 
                                min="10" 
                                max="500" 
                                v-model="poblationSize"
                                :state="(!(poblationSize<10 || poblationSize>500))?null:false"
                            >

                            </b-form-input>
                            <b-form-invalid-feedback :force-show="poblationSize<10 || poblationSize>500">
                                {{__('Type a number between 10 and 500')}}
                            </b-form-invalid-feedback>
                        </b-form-group>

                        <b-form-group
                            class="col-6"
                            id="generations-group"
                            :label="__('How many generations?')"
                        >
                            <b-form-input 
                                id="generations" 
                                size="sm" 
                                type="number" 
                                min="20" 
                                max="2000" 
                                v-model="generations"
                                :state="(!(generations<20 || generations>2000))?null:false"
                            >

                            </b-form-input>
                            <b-form-invalid-feedback :force-show="(generations<20 || generations>2000)">
                                {{__('Type a number between 20 and 2000')}}
                            </b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group
                            class="col-6"
                        >
                            <b-button 
                                @click="makeModel()" 
                                variant="primary" 
                                size="sm"
                                :disabled="(generations<20 || generations>2000) || (poblationSize<10 || poblationSize>500)"
                            >
                                {{__('Run')}}
                            </b-button>
                        </b-form-group>

                    </b-form>
                </b-tab>
                <b-tab :title="__('Results')">
                    <results-table :items="results"/>
                </b-tab>
                <b-tab :title="__('Charts')">
                    <b-alert :show ="results.length == 0" variant="info">{{ __('Run simulations first') }}</b-alert>
                    <div id = "myDiv">
                    </div>
                </b-tab>
            </b-tabs>
            <b-card-footer>
                <!--b-button variant="primary" @click="$bvModal.show('add-panel')"> {{__('Add solar panel')}} </b-button>
                <b-button variant="primary" @click="$bvModal.show('add-turbine')"> {{__('Add eolic turbine')}} </b-button>
                <b-button variant="primary" @click="makeModel()" > {{__('Run simulations')}} </b-button-->
                <div class="text-center" style="line-height: 1; margin-bottom: 2px;">
                    <div>
                        <small class="text-muted">
                            Solar GIS Data obtained from the
                        </small>
                        <small 
                            class="text-primary"
                            v-b-tooltip.hover
                            title="A free, web-based application developed, and operated by the company Solargis s.r.o. on behalf of the World Bank Group, utilizing Solargis data, with funding provided by the Energy Sector Management Assistance Program (ESMAP). For additional information: https://globalsolaratlas.info"
                        >
                            Global Solar Atlas 2.0
                        </small>
                    </div>
                    <div>
                        <small class="text-muted">
                            Eolic GIS Data obtained from the
                        </small>
                        <small 
                            class="text-primary"
                            v-b-tooltip.hover
                            title="A free, web-based application developed, owned and operated by the Technical University of Denmark (DTU). The Global Wind Atlas 3.0 is released in partnership with the World Bank Group, utilizing data provided by Vortex, using funding provided by the Energy Sector Management Assistance Program (ESMAP). For additional information: https://globalwindatlas.info"
                        >
                            Global Wind Atlas 3.0
                        </small>
                    </div>
                </div>
                <div class="text-center" style="line-height: 1;">
                    <small class="text-muted">
                        ©2020 Luis E. Garcia Marrero
                    </small>
                </div>
                <div class="text-center" style="line-height: 1;">
                    <small>
                        <a href="mailto:lgarciamarrero92@gmail.com"> lgarciamarrero92@gmail.com </a>
                    </small>
                </div>

            </b-card-footer>
        </b-card>
    </div>
</template>

<script>
    
    import * as turf from '@turf/turf'
    import 'leaflet-geotiff/leaflet-geotiff';
    import {spawn,Worker} from 'threads'
    import cfc from '../scripts/coefficients/main.js'
    import nsga2 from '../scripts/nsga2/main.js'

    const GeoTIFF = require('geotiff') 
    export default {
        data () {
            return {
                poblationSize: 100,
                generations: 100,
                tabIndex: 0,
                model: {},
                results: [],
                mE: 0.0,//Maximun Energy that can be produced
                mC: 0.0,//Maximun Costs that can be achieved
                mSA: 0.0,//Maximun solar area
                mWA: 0.0,//Maximun wind area
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
                cfiec1: 'CUB_capacity-factor_IEC1.tif',
                cfiec2: 'CUB_capacity-factor_IEC2.tif',
                cfiec3: 'CUB_capacity-factor_IEC3.tif',
                pvout: 'PVOUT.tif',
                progress : 20,
                tiff: null,
                zonesPartition: [],
                cfc: new cfc(),
                nsga: new nsga2()
            }
        },
        props:{
        },
        created(){
            /*
            this.readGisFiles = new Promise( async (resolve,reject) =>{
                await spawn(new Worker('../scripts/gis/gis.js')).then((data)=>{
                    data('hello world').then((val)=>{
                        resolve(val)
                    })
                })
            })
            this.readGisFiles.then((data)=>{
                console.log(data)
            })
            */
            /*
            this.readGisFiles = new Promise( async (resolve,reject) =>{
                var tiff = null
                tiff = await GeoTIFF.fromUrl('CUB_capacity-factor_IEC1.tif')
                this.cfiec1 = await tiff.getImage()
                tiff = await GeoTIFF.fromUrl('CUB_capacity-factor_IEC2.tif')
                this.cfiec2 = await tiff.getImage()
                tiff = await GeoTIFF.fromUrl('CUB_capacity-factor_IEC3.tif')
                this.cfiec3 = await tiff.getImage()
                tiff = await GeoTIFF.fromUrl('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/PVOUT.tif')
                this.pvout = await tiff.getImage()
                resolve()
            })
            */
            Vue.prototype.$user_id = this.user_id
        },

        mounted() {
            this.cfc.listen.on('progress', (val)=>{
                this.$root.$emit('coefficients-progress',val)
            })
            this.nsga.listen.on('progress', (val)=>{
                this.$root.$emit('generation-progress',val)
            })
            let options = {
                bounds: [[20.752307,-76.758902],[20.875548,-76.675165]]
            }

            /*
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'CUB_power-density_50m.tif' , true);
                xhr.responseType = 'arraybuffer';
                xhr.onload = function(e) {
                var tiff = GeoTIFF.parse(this.response);
                var image = tiff.getImage();
                console.log(image)
            // ...
            }
            xhr.send();
            */
            //this.solar = new L.leafletGeotiff('/Cuba_GISdata_LTAy_YearlyMonthlyTotals_GlobalSolarAtlas-v2_GEOTIFF/GTI.tif')
            //this.eolic = new L.leafletGeotiff('CUB_power-density_50m.tif', options )
            //console.log(this.eolic)
            
            /*
            
           
            */
            //this.readGISFiles()
            /*
            this.$map.on('click', async (e) => {
                console.log( this.tiff.getValueAtLatLng(e.latlng.lat, e.latlng.lng) )
            })
            */
           /*
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
            */
           /*
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
            */
        },
        methods: {
            async partitionZone(item,image){
                return new Promise( async (resolve,reject) => {
                    var polygon = this.$drawnItems._layers[item].toGeoJSON();
                    var bbox = turf.bbox(polygon);
                    var area = this.calcArea(item)
                
                    var d = 1 //Grid length in meters
                    if(area > 10000){ //No more than 1000 points in a grid
                        d = Math.sqrt(area)/100.0
                    }
                    /*
                        Make a grid with length d in this zone
                    */
                    var options = {units: 'meters',mask: polygon};
                    var grid = turf.pointGrid(bbox, d , options);
                    /*
                        Calculate bbox in pixel coordinates
                    */
                    bbox = [
                        Math.min(bbox[0],bbox[2]),
                        Math.max(bbox[1],bbox[3]),
                        Math.max(bbox[0],bbox[2]),
                        Math.min(bbox[1],bbox[3]),
                    ]
                    const [oX, oY] = image.getOrigin();
                    const [imageResX, imageResY] = image.getResolution();
                    let wnd = [
                        Math.floor((bbox[0] - oX) / imageResX),
                        Math.floor((bbox[1] - oY) / imageResY),
                        Math.ceil((bbox[2] - oX) / imageResX),
                        Math.ceil((bbox[3] - oY) / imageResY),
                    ];
                    /*
                        Read Gis Data in that window
                    */
                    const data = await image.readRasters({
                        window: wnd,
                    });
                    /*
                        Iterate grid points and fill with gis value
                    */
                    var points = []
                    for( var i = 0 ; i < grid.features.length ; i++ ){
                        
                        var p = grid.features[i].geometry.coordinates
                        
                        let pc = [
                            Math.floor((p[0] - oX) / imageResX)-wnd[0],
                            Math.floor((p[1] - oY) / imageResY)-wnd[1]
                        ]

                        let ind = pc[1]*data.width + pc[0]
                        let val = data[0][ind]

                        points.push({
                            'point': grid.features[i],
                            'value' : val
                        })
                    }
                    resolve(points);
                });
            },
            test(item){
                let energy = 0.0,costs = 0.0, solarArea = 0.0, windArea = 0.0;
                let fc = Object.keys(item.details)
                for(let i = 0 ; i < fc.length; i++ ){
                    energy += item.details[fc[i]].energy
                    costs += item.details[fc[i]].costs
                    solarArea += item.details[fc[i]].solarArea
                    windArea += item.details[fc[i]].windArea
                }
                let eps = 1e-4
                if( Math.abs(energy - item.energy) >= eps || Math.abs(costs - item.costs) >= eps || Math.abs(solarArea - item.solarArea) >= eps || Math.abs(windArea - item.windArea) >= eps){
                    console.log('Details')
                    console.log(energy + ' ' + item.energy )
                    console.log(costs + ' ' + item.costs )
                    console.log(solarArea + ' ' + item.solarArea )
                    console.log(windArea + ' ' + item.windArea )
                    return false
                }
                    
                return true
            },
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
            async getCoefficients(){
                
                let data = []
                let sp = []
                let wt = []

                await axios.get('/solar_panels').then( (response) => {
                    for (let index = 0; index < response.data.length; index++) {
                        const element = response.data[index];
                        sp.push(element) 
                    }
                })
                await axios.get('/wind_turbines').then( (response) => {
                    for (let index = 0; index < response.data.length; index++) {
                        const element = response.data[index];
                        wt.push(element)
                    }
                })

                await axios.get('/zones').then( async (response) => {
                    for (let index = 0; index < response.data.length; index++) {
                        const zone = response.data[index];
                        if(zone.type == 'eolic'){
                            let obj = {
                                'zone': zone,
                                'wind_turbines': wt
                            }
                            data.push(obj)
                        }
                        if(zone.type == 'solar'){
                            let obj = {
                                'zone': zone,
                                'solar_panels': sp
                            }
                            data.push(obj)
                        }
                    }
                })
                
                let model = await this.cfc.get(data)
                let zones = Object.keys(model)

                for (let i = 0; i < zones.length; i++) {
                    const item = zones[i]
                    let fc = Object.keys(model[item])
                    let del = []
                    for(let j = 0 ; j < fc.length ; j++ ){
                        let belongs = true
                        for(let k = 0 ; (k < fc.length && k != j); k++){
                            if(model[item][fc[k]]['energy'] >= model[item][fc[j]]['energy'] && model[item][fc[k]]['costs'] <= model[item][fc[j]]['costs']){
                                belongs = false
                                break
                            }
                        }
                        if(belongs == false){
                            del.push(fc[j])
                        }
                    }
                    for(let j = 0 ; j < del.length ; j++ ){
                        //delete model[item][del[j]]
                    }
                }

                this.mE = this.mC = this.mSA = this.mWA = 0.0
                for (let i = 0; i < zones.length; i++) {
                    let mE = 0.0,mC = 0.0,mSA = 0.0, mWA = 0.0;
                    const item = zones[i]
                    let fc = Object.keys(model[item])
                    for(let j = 0 ; j < fc.length ; j++ ){
                        if(model[item][fc[j]]['energy'] >= mE){
                            mE = model[item][fc[j]]['energy']
                        }
                        if(model[item][fc[j]]['costs'] >= mC){
                            mC = model[item][fc[j]]['costs']
                        }
                        if(model[item][fc[j]]['solarArea'] >= mSA){
                            mSA = model[item][fc[j]]['solarArea']
                        }
                        if(model[item][fc[j]]['windArea'] >= mWA){
                            mWA = model[item][fc[j]]['windArea']
                        }
                    }
                    this.mE += mE
                    this.mC += mC
                    this.mSA += mSA
                    this.mWA += mWA    
                }
                this.model = model
                console.log(model)
                return model
            },
            trueParetoFront(){
                let zones = Object.keys(this.model)
                var tot = Math.pow(13,zones.length)
                let all = []
                for(var i = 0 ; i < tot ; i++ ){
                    var n = i
                    let p = 0
                    let s = {
                        'energy': 0,
                        'costs': 0,
                        'solarArea': 0,
                        'windArea': 0
                    }
                    while(n){
                        var t = n%13
                        //console.log(zones[p])
                        let facilities = Object.keys(this.model[zones[p]])
                        
                        if(t<facilities.length){
                            //console.log(t)
                            //console.log(facilities)
                            s.energy += this.model[zones[p]][facilities[t]].energy
                            s.costs += this.model[zones[p]][facilities[t]].costs
                            s.solarArea += this.model[zones[p]][facilities[t]].solarArea
                            s.windArea += this.model[zones[p]][facilities[t]].windArea
                        }
                        n = parseInt(n/13)
                        p++
                    }
                    var belongs = true
                    let nw = []
                    while(all.length){
                        let it = all.pop()
                        if(!this.domine(s,it)){
                            nw.push(it)
                        }
                        if(this.domine(it,s)){
                            belongs = false
                        }
                    }
                    all = nw
                    if(belongs)
                        all.push(s)
                    if(i%100000 == 0 ){
                        console.log(i + ' ' + all.length)
                    }
                }
                console.log(all.length)
                return all;
            },
            generateRandomSolution(model){
                let zones = Object.keys(model)
                let sol = {
                    'energy': 0.0,
                    'costs': 0.0,
                    'solarArea': 0.0,
                    'windArea': 0.0,
                    'details': {

                    }
                }
                let test = {}
                for (let i = 0; i < zones.length; i++) {
                    const item = zones[i]
                    let fc = Object.keys(model[item])
                    let r = Math.floor(Math.random()*fc.length)
                    let idt = fc[r]
                    sol.energy += model[item][idt].energy
                    sol.costs += model[item][idt].costs
                    sol.solarArea += model[item][idt].solarArea
                    sol.windArea += model[item][idt].windArea
                    test[item] = {
                        'energy': model[item][idt].energy,
                        'costs': model[item][idt].costs,
                        'facility': idt
                    }
                    sol.details[item] = model[item][idt]
                }
                let fc = Object.keys(test)
                let dominated = []
                for(var i = 0 ; i < fc.length; i++ ){
                    var belongs = true
                    for(var j = 0 ; (j < fc.length && j != i); j++ ){
                        if( (test[fc[j]].energy >= test[fc[i]].energy && test[fc[j]].costs < test[fc[i]].costs) ||
                            (test[fc[j]].energy > test[fc[i]].energy && test[fc[j]].costs <= test[fc[i]].costs)
                        ){
                            belongs = false
                            break
                        }
                    }
                    if(belongs == false ){
                        dominated.push({
                            'zone': fc[i],
                            'facility': test[fc[i]].facility
                        })
                    }
                }
                /*
                if(dominated.length){
                    for(var i = 0 ; i < dominated.length; i++ ){
                        delete model[dominated[i].zone][dominated[i].facility]
                    }
                }
                */
                return sol;

            },
            domine(m1,m2){
                if(m1["energy"] > m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
                if(m1["energy"] >= m2["energy"] && m1["costs"] < m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
                if(m1["energy"] >= m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] < m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
                if(m1["energy"] >= m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] < m2["windArea"]) return true;
                return false;
            },
            //Begin NSGA-II utility functions
            fastNonDominatedSort(P){
                let F = []
                P.forEach(p=>{
                    p["domineSet"] = []
                    p["domination"] = 0
                    P.forEach(q=>{
                        if(this.domine(p,q)){
                            p["domineSet"].push(q)
                        }else if(this.domine(q,p)){
                            p["domination"]+=1
                        }
                    })
                    if(p["domination"] == 0){
                        p["rank"] = 1
                        F.push(p)
                    }
                })
                let i = 1
                while(F.length){
                    let Q = []
                    F.forEach(p=>{
                        p["domineSet"].forEach(q=>{
                            q["domination"] -= 1
                            if(q["domination"] == 0){
                                q["rank"] = i+1
                                Q.push(q)
                            }
                        })
                    })
                    i = i + 1
                    F = Q
                }
            },
            crowdingDistanceAssignment(P){
                let l = P.length
                const oo = 1000000000000000000
                P.forEach(p=>{
                    p["distance"] = 0.0
                })
                //Energy
                P.sort((a,b)=>{
                    return (a.energy - b.energy)
                })
                P[0].distance = P[l-1].distance = oo
                for( let i = 1 ; i < l-1 ; i++ ){
                    if(P[i].distance != oo )
                        P[i].distance += (P[i+1].energy - P[i-1].energy)/this.mE
                }
                //Costs
                P.sort((a,b)=>{
                    return (a.costs - b.costs)
                })
                P[0].distance = P[l-1].distance = oo
                for( let i = 1 ; i < l-1 ; i++ ){
                    if(P[i].distance != oo )
                        P[i].distance += (P[i+1].costs - P[i-1].costs)/this.mC
                }
                
                //solarArea
                P.sort((a,b)=>{
                    return (a.solarArea - b.solarArea)
                })
                P[0].distance = P[l-1].distance = oo
                for( let i = 1 ; i < l-1 ; i++ ){
                    if(P[i].distance != oo )
                        P[i].distance += (P[i+1].solarArea - P[i-1].solarArea)/this.mSA
                }
                //windArea
                P.sort((a,b)=>{
                    return (a.windArea - b.windArea)
                })
                P[0].distance = P[l-1].distance = oo
                for( let i = 1 ; i < l-1 ; i++ ){
                    if(P[i].distance != oo )
                        P[i].distance += (P[i+1].windArea - P[i-1].windArea)/this.mWA
                }
                
            },
            betterByFitness(s1,s2){
                if(s1.rank != s2.rank ){
                    return s1.rank < s2.rank;
                }
                return s1.distance > s2.distance;
            },
            binaryTournamentSelection(P){
                let Q = []
                for(let i = 0 ; i < P.length; i++ ){
                    let l = Math.floor(Math.random()*P.length)
                    let r = Math.floor(Math.random()*P.length)
                    if(this.betterByFitness(P[l],P[r])){
                        Q.push(P[l])
                    }else{
                        Q.push(P[r])
                    }
                }
                //console.log('Binary Tournament works!')
                return Q;
            },
            crossover(p1,p2){

                let fc = Object.keys(p1.details)
                let l = Math.floor(Math.random()*fc.length)

                let c1 = $.extend(true,{},p1)
                let c2 = $.extend(true,{},p2)
                /*
                for(let i = 0 ; i <= l ; i++ ){
                    c1.details[fc[i]] = p2.details[fc[i]]
                }
                for(let i = l+1 ; i < fc.length ; i++ ){
                    c2.details[fc[i]] = p1.details[fc[i]]
                }
                */
                
                for(let i = 0 ; i < fc.length ; i++ ){
                    if(Math.random() >= 0.5 ){
                        c2.details[fc[i]] = p1.details[fc[i]]
                        c1.details[fc[i]] = p2.details[fc[i]]
                    }
                }
                
                //Recalculate objectives
                c1.energy = c2.energy = c1.costs = c2.costs = c1.solarArea = c2.solarArea = c1.windArea = c2.windArea = 0
                for(let i = 0 ; i < fc.length ; i++ ){
                    c1.energy += c1.details[fc[i]].energy
                    c2.energy += c2.details[fc[i]].energy   
                    c1.costs += c1.details[fc[i]].costs
                    c2.costs += c2.details[fc[i]].costs 
                    c1.solarArea += c1.details[fc[i]].solarArea
                    c2.solarArea += c2.details[fc[i]].solarArea 
                    c1.windArea += c1.details[fc[i]].windArea
                    c2.windArea += c2.details[fc[i]].windArea        
                }
                //console.log('Crossover works!')
                return [c1,c2];
            },
            mutate(children){
                let fc = Object.keys(children[0].details)
                let mutated = []
                children.forEach(element=>{
                    
                    let child = $.extend(true,{},element)
                   
                    for(let l = 0 ; l < fc.length ; l++ ){
                        if(Math.random() <= 1.0/fc.length ){
                            const item = fc[l]
                            let ft = Object.keys(this.model[item])
                            let r = Math.floor(Math.random()*ft.length)
                            let idt = ft[r]
                            
                            child.energy -= child.details[item].energy
                            child.costs -= child.details[item].costs
                            child.solarArea -= child.details[item].solarArea
                            child.windArea -= child.details[item].windArea

                            child.energy += this.model[item][idt].energy
                            child.costs += this.model[item][idt].costs
                            child.solarArea += this.model[item][idt].solarArea
                            child.windArea += this.model[item][idt].windArea
                            child.details[item] = $.extend(true,{},this.model[item][idt])
                        }
                    }
                    mutated.push(child)
                })
                //console.log('Mutate works!')
                return mutated;
            },
            makeNewPop(P){
                let Parents = this.binaryTournamentSelection(P)
                let Q = []
                for(let i = 0 ; i < Parents.length ; i+=2 ){
                    var children = {}
                    if(i+1<Parents.length){
                        children = this.crossover(Parents[i],Parents[i+1])
                    }
                    else{
                        children = this.crossover(Parents[i],Parents[i])
                    }
                    let mutated = this.mutate(children)
                    Q.push(mutated[0])
                    Q.push(mutated[1])
                }
                return Q;
            },
            nsga2(n,gen){
                var I = []
                for(let i = 0 ; i < n ; i++ ){
                    I.push(this.generateRandomSolution(this.model))
                }
                this.fastNonDominatedSort(I)
                this.$root.$emit('generation', {gen:0,front: I} )
                I.sort((a,b)=>{
                    return (a.rank - b.rank)
                })
                let P = []
                P.push(I[0])
                for(let i = 1 ; i < I.length; i++ ){
                    if(I[i].rank != I[i-1].rank){
                        this.crowdingDistanceAssignment(P)
                        P = []
                    }
                    P.push(I[i])
                }
                this.crowdingDistanceAssignment(P)
                var Q = this.makeNewPop(I)
                for( let i = 0 ; i < gen ; i++ ){
                    let I0 = []
                    for(let j = 0 ; j < I.length; j++ ){
                        I0.push(I[j])
                    }
                    let R = I.concat(Q)
                    this.fastNonDominatedSort(R)
                    R.sort((a,b)=>{
                        return (a.rank - b.rank)
                    })
                    let P = []
                    P.push(R[0])
                    for(let j = 1 ; j < R.length; j++ ){
                        if(R[j].rank != R[j-1].rank){
                            this.crowdingDistanceAssignment(P)
                            P = []
                        }
                        P.push(R[j])
                    }
                    this.crowdingDistanceAssignment(P)
                    R.sort((a,b)=>{
                        if(this.betterByFitness(a,b))return -1
                        else return 1
                    })
                    I = []
                    for(let j = 0 ; j < n ; j++ ){
                        I.push(R[j])
                    }
                    Q = this.makeNewPop(I)
                    
                    this.$root.$emit('generation', {gen:i+1,front: I} )
                }
                return I;
            },
            //End NSGA-II utility functions
           
            generateParetoFrontRandom(n){
                let mdl =  this.model
                let copy = $.extend(true,{},mdl)

                let R = []
                
                for(let i = 0 ; i < 10000 ; i++ ){
                    R.push(this.generateRandomSolution(mdl))
                }
                this.fastNonDominatedSort(R)
                R.sort((a,b)=>{
                    return (a.rank - b.rank)
                })
                let P = []
                P.push(R[0])
                for(let i = 1 ; i < R.length; i++ ){
                    if(R[i].rank != R[i-1].rank){
                        this.crowdingDistanceAssignment(P)
                        P = []
                    }
                    P.push(R[i])
                }
                this.crowdingDistanceAssignment(P)
                R.sort((a,b)=>{
                    if(this.betterByFitness(a,b))return -1
                    else return 1
                })
                let I = []
                for(let j = 0 ; j < n ; j++ ){
                    I.push(R[j])
                }
                //return sol
                /*
                let noDomitated = []
                let domintatedCont = 0;
                for(let i = 0 ; i < sol.length; i++ ){
                    let belongs = true
                    for( let j = 0 ; (j != i  && j < sol.length); j++){
                        if(this.domine(sol[j],sol[i])){
                            belongs = false
                            domintatedCont++
                            break;
                        }
                    }
                    if(belongs == true)
                        noDomitated.push(sol[i])
                }
                console.log(noDomitated.length)
                //console.log(mdl)
                //console.log(copy)\
                */
                return I
            },
            //Metrics
            differentsSolutions(P){
                let tot = P.length
                let eps = 1
                for(let i = 0 ; i < P.length ; i++ ){
                    for(let j = i+1 ; j < P.length ; j++ ){
                        if(Math.abs(P[i].energy - P[j].energy) <= eps && Math.abs(P[i].costs - P[j].costs) <= eps){
                            tot -= 1
                            break
                        }
                    }
                }
                return tot
            },
            generationalDistance(trueFront, P){
                let sol = 0
                P.forEach(element=>{
                    let mn = 1000000000000000000
                    trueFront.forEach(item=>{
                        if( Math.sqrt(Math.pow( (element.energy-item.energy)/this.mE,2) + Math.pow( (element.costs-item.costs)/this.mC,2)) <= mn )
                            mn = Math.sqrt(Math.pow( (element.energy-item.energy)/this.mE,2) + Math.pow( (element.costs-item.costs)/this.mC,2))
                    })
                    sol += mn*mn
                })
                return Math.sqrt(sol)/P.length;
            },
            errorRatio(trueFront, P){
                let eps = 1
                let noBelongsCount = 0
                P.forEach(element=>{
                    let belongs = false
                    trueFront.forEach(item=>{
                        if(Math.abs(element.energy-item.energy) <= eps && Math.abs(element.costs-item.costs) <= eps){
                            belongs = true
                        }
                    })
                    if(!belongs){
                        noBelongsCount += 1
                    }
                })
                return noBelongsCount/P.length;
            },
            igendist(trueFront, P){
                let sol = 0
                trueFront.forEach(element=>{
                    let minDis = 1000000000000000000
                    let id = -1
                    for(let i = 0 ; i < P.length ; i++){
                        if( Math.sqrt(Math.pow( Math.max( 0, (-P[i].energy + element.energy)/this.mE), 2 ) +  Math.pow( Math.max((P[i].costs - element.costs)/this.mC,0), 2 )) < minDis ){
                            minDis = Math.sqrt(Math.pow( Math.max( 0, (-P[i].energy + element.energy)/this.mE), 2 ) +  Math.pow( Math.max((P[i].costs - element.costs)/this.mC,0), 2 ))
                            id = i
                        }
                    }
                    sol += minDis
                })
                return sol/trueFront.length;
            },
            distribute(trueFront, P){
                let dis = []
                P.forEach(element=>{
                    dis.push(0)
                })
                trueFront.forEach(element=>{
                    let minDis = 1000000000000000000
                    let id = -1
                    for(let i = 0 ; i < P.length ; i++){
                        if( Math.sqrt(Math.pow( (P[i].energy - element.energy)/this.mE, 2 ) +  Math.pow( (P[i].costs - element.costs)/this.mC , 2 )) < minDis ){
                            minDis = Math.sqrt(Math.pow( (P[i].energy - element.energy)/this.mE, 2 ) +  Math.pow( (P[i].costs - element.costs)/this.mC , 2 ))
                            id = i
                        }
                    }
                    dis[id] = Math.max(dis[id],minDis)
                })
                let mx = 0

                let promedio = 0,varianza = 0
                for(let i = 0 ; i < dis.length ; i++ ){
                    mx = Math.max(mx,dis[i])
                    promedio += dis[i]
                }
                return mx;
                promedio /= dis.length
                for(let i = 0 ; i < dis.length ; i++ ){
                    varianza += (promedio - dis[i])*(promedio - dis[i])
                }
                varianza /= dis.length
                return Math.sqrt(varianza)
            },
            epsIndicator(trueFront,P){
                let eps = 0.0
                trueFront.forEach(element=>{
                    let mn = 10000000000000
                    if( element.costs > 0 ){
                        P.forEach(item=>{
                            if(item.energy > 0 ){
                                mn = Math.min(mn,Math.max(element.energy/item.energy,item.costs/element.costs))
                            }
                        })
                        eps = Math.max(eps,mn)
                    }
                    
                })
                return eps;
            },
            setCoverage(P,Q){
                let cont = 0,cont2 = 0
                Q.forEach(q=>{
                    let d = false
                    P.forEach(p=>{
                        if(this.domine(p,q)){
                            d = true
                        }
                    })
                    if(d)cont++
                })
                P.forEach(p=>{
                    let d = false
                    Q.forEach(q=>{
                        if(this.domine(q,p)){
                            d = true
                        }
                    })
                    if(d)cont2++
                })
                return [cont,cont2]
            },
            crowdingDistanceMetrics(P){
                let mean = 0
                let mn = 1000000000000000000
                let mx = 0
                let cn = 0
                P.forEach(p=>{
                    if(p.distance == 1000000000000000000 ){
                        cn++
                    }else{
                        mean += p.distance
                        mn = Math.min(p.distance,mn)
                        mx = Math.max(p.distance,mx)
                    }
                        
                })
                return [mn,mean/(P.length-cn),mx]
            },
            spacing(P){
                let dis = []
                for(let i = 0 ; i < P.length ; i++ ){
                    dis.push(1000000000000000000)
                    for(let j = 0 ; j < P.length; j++ ){
                        if(i == j )continue
                        let sm = Math.abs( (P[j].energy - P[i].energy) )+
                        Math.abs( (P[j].costs - P[i].costs) )+
                        Math.abs( (P[j].solarArea - P[i].solarArea) )+
                        Math.abs( (P[j].windArea - P[i].windArea) )
                        dis[i] = Math.min(dis[i],sm)          
                    }
                }
                let disMean = 0
                for(let i = 0 ; i < P.length ; i++ ){
                    disMean += dis[i]
                }
                disMean /= P.length
                let sum = 0
                for(let i = 0 ; i < P.length ; i++ ){
                    sum += (dis[i]-disMean)*(dis[i]-disMean)
                }
                return Math.sqrt(sum/(P.length-1))
            },
            epsilonAdditive(P,Q){
                let Ip = -1000000000000000000
                let Iq = -1000000000000000000
                Q.forEach(q=>{
                    let mn = 1000000000000000
                    P.forEach(p=>{
                        mn = Math.min(mn,Math.max( -p.energy+q.energy,p.costs-q.costs,p.solarArea-q.solarArea,p.windArea-q.windArea))
                    })
                    Ip = Math.max(mn,Ip)
                })
                P.forEach(p=>{
                    let mn = 1000000000000000
                    Q.forEach(q=>{
                        mn = Math.min(mn,Math.max( -q.energy+p.energy,q.costs-p.costs,q.solarArea-p.solarArea,q.windArea-p.windArea))
                    })
                    Iq = Math.max(mn,Iq)
                })
                return [Ip,Iq]
            },
            //Do better in each zone
            doBetter(){
                let fc = Object.keys(this.model)
                let res = [0,0,0,0]
                for(let i = 0 ; i < fc.length ; i++ ){
                    let ft = Object.keys(this.model[fc[i]])
                    let mn = 1000000000
                    let objt = {}
                    for(let j = 0 ; j < ft.length ; j++ ){
                        let obj = this.model[fc[i]][ft[j]]
                        if(obj.energy > 0 ){
                            if( (obj.costs/obj.energy) < mn ){
                                mn = (obj.costs/obj.energy)
                                objt = obj
                            }
                        }
                    }
                    res[0] += objt.energy
                    res[1] += objt.costs
                    res[3] += objt.solarArea
                    res[2] += objt.windArea
                }
                res[0] /= 1000000
                res[1] /= 1000000
                res[2] /= 10000
                res[3] /= 10000
                res.push(res[1]/res[0])
                console.log(res)
            },
            async compare(n){
                let mdl = await this.getCoefficients()
                //let trueFront = this.trueParetoFront()
                
                let mdr = [0,0], epsi = [0,0]
                
                for(let i = 0 ; i < n ; i++ ){
                    let rdm = this.generateParetoFrontRandom(100)
                    let nsga = this.nsga2(100,100)
                    let mdra = this.setCoverage(nsga,rdm)
                    let epsip = this.epsilonAdditive(nsga,rdm)
                    
                    mdr[0] += mdra[0]
                    mdr[1] += mdra[1]
                    epsi[0] += epsip[0]
                    epsi[1] += epsip[1]

                    console.log('Finish ' + i)
                }
                console.log('Mutual D Rate ' + mdr[0]/n + ' ' + mdr[1]/n )
                console.log('Epsilon ' + epsi[0]/n + ' ' + epsi[1]/n)
                        
            },

            async makeModel(){
                this.$bvModal.show('busy');
                console.log('START')
                let mdl = await this.getCoefficients()
                console.log('END')
                /*
                let trueFront = this.trueParetoFront()
                //this.compare(50)
                
                trueFront.sort((a,b)=>{
                    return b.energy - a.energy;
                })
                let mE = trueFront[0].energy

                trueFront.sort((a,b)=>{
                    return b.costs - a.costs;
                })
                let mC = trueFront[0].costs

                trueFront.sort((a,b)=>{
                    return b.solarArea - a.solarArea;
                })
                let mSA = trueFront[0].solarArea

                trueFront.sort((a,b)=>{
                    return b.windArea - a.windArea;
                })
                let mWA = trueFront[0].windArea
                */
                var data = []
                /*
                var energyCosts = {
                    name: 'Frente de Pareto',
                    x: [],
                    y: [],
                    mode: 'markers',
                    marker: {
                        size: 5,
                        color: '00CC63',
                        opacity: 0.5,
                    },
                    type: 'scatter'
                }

                trueFront.forEach(element=>{
                    energyCosts.x.push(element.energy/1000000)
                    energyCosts.y.push(element.costs/1000000)
                })
                
                data.push(energyCosts)
                */
                var minCrowding = {
                    name: '',
                    x: [],
                    y: [],
                    type: 'scatter'
                }
                var meanCrowding = {
                    name: '',
                    x: [],
                    y: [],
                    type: 'scatter'
                }
                var maxCrowding = {
                    name: '',
                    x: [],
                    y: [],
                    type: 'scatter'
                }
                var domin = {
                    name: '',
                    x: [],
                    y: [],
                    type: 'scatter'
                }
                var nsga = {
                    name: '',
                    x: [],
                    y: [],
                    mode: 'markers',
                    type: 'scatter'
                } 
                let lg = []
                let sc = []
                let mc = []
                let isFinish = false
                this.$root.$on('generation-progress', (val) => {
                    //console.log(val.progress)
                    //console.log(this.spacing(val.front))
                    let cr = this.crowdingDistanceMetrics(val.front)
                    minCrowding.x.push(val.gen)
                    minCrowding.y.push( cr[0] )
                    meanCrowding.x.push(val.gen)
                    meanCrowding.y.push( cr[1] )
                    //Maximum
                    mc.push(cr[1])
                    let v = 0
                    let t = 0
                    for(let i = mc.length-1; i >= 0 && i >= mc.length-21; i-- ){
                        v += mc[i]
                        t++
                    }
                    v /= t
                    let de = 0
                    for(let i = mc.length-1; i >= 0 && i >= mc.length-21; i-- ){
                        de += (v-mc[i])*(v-mc[i])
                    }
                    de /= t
                    maxCrowding.x.push(val.gen)
                    maxCrowding.y.push( Math.sqrt(de) )
                    if(val.gen > 0 ){
                        sc.push(this.setCoverage(val.front,lg))
                        let v = 0
                        let t = 0
                        for(let i = sc.length-1; i >= 0 && i >= sc.length-21; i-- ){
                            v += sc[i]
                            t++
                        }
                        v /= t
                        /*
                        let de = 0
                        for(let i = sc.length-1; i >= 0 && i >= sc.length-21; i-- ){
                            de += (sc[i]-v)*(sc[i]-v)
                        }
                        de /= t
                        */
                        if(v <= 0.01 ){
                            isFinish = true
                        }
                        if(!isFinish ){
                            domin.x.push(val.gen)
                            domin.y.push(v)
                        }
                        
                    }
                    lg = val.front
                    /*
                    if( val.gen == 0 ){
                        console.log('Generation distance ' + this.generationalDistance(trueFront,val.front))
                        console.log('Error ratio ' + this.errorRatio(trueFront,val.front))
                        console.log('Inverted Gen Dist ' + this.igendist(trueFront,val.front))
                        console.log('Distribution ' + this.distribute(trueFront,val.front))
                        console.log('Diferents Solutions ' + this.differentsSolutions(val.front))
                        console.log('Eps indicator ' + this.epsIndicator(trueFront, val.front))
                        var energyCosts = {
                            name: 'Generación '+ val.gen,
                            x: [],
                            y: [],
                            mode: 'markers',
                            marker: {
                                size: 4,
                                //opacity: 0.5
                            },
                            type: 'scatter'
                        }

                        val.front.forEach(element=>{
                            energyCosts.x.push(element.energy/1000000)
                            energyCosts.y.push(element.costs/1000000)
                        })
                        
                        data.push(energyCosts)
                    }
                    if( val.gen == 100 ){
                        console.log('Generation distance ' + this.generationalDistance(trueFront,val.front))
                        console.log('Error ratio ' + this.errorRatio(trueFront,val.front))
                        console.log('Inverted Gen Dist ' + this.igendist(trueFront,val.front))
                        console.log('Distribution ' + this.distribute(trueFront,val.front))
                        console.log('Diferents Solutions ' + this.differentsSolutions(val.front))
                        console.log('Eps indicator ' + this.epsIndicator(trueFront, val.front))
                        var energyCosts = {
                            name: 'Generación '+ val.gen,
                            x: [],
                            y: [],
                            mode: 'markers',
                            marker: {
                                color: 'rgb(0,0,0)',
                                size: 4,
                                //opacity: 0.5
                            },
                            type: 'scatter'
                        }

                        val.front.forEach(element=>{
                            energyCosts.x.push(element.energy/1000000)
                            energyCosts.y.push(element.costs/1000000)
                        })
                        
                        data.push(energyCosts)
                    }
                    */        
                    if(val.gen == 100 ){
                        var trace = {
                            type: 'parcoords',
                            line: {
                                color: 'blue'
                            },
                            dimensions: [
                                {
                                    range: [0, this.mE/1000000],
                                    label: this.__('Energy (GWh/year)'),
                                    values: []
                                },
                                {
                                    range: [0, this.mC/1000000],
                                    label: this.__('Costs (Millions of dolars/year)'),
                                    values: []
                                },
                                {
                                    range: [0, this.mSA/10000],
                                    label: this.__('Panels impacted area (ha)'),
                                    values: []
                                },
                                {
                                    range: [0, this.mWA/10000],
                                    label: this.__('Turbines impacted area (ha)'),
                                    values: []
                                },
                            ]
                        }
                        val.front.forEach(element=>{
                            trace.dimensions[0]['values'].push(element.energy/1000000)
                            trace.dimensions[1]['values'].push(element.costs/1000000)
                            trace.dimensions[2]['values'].push(element.solarArea/10000)
                            trace.dimensions[3]['values'].push(element.windArea/10000)
                        })
                        data.push(trace)
                    }
                });
                /*
                this.results = this.generateParetoFrontRandom(100)
                console.log('Generation distance ' + this.generationalDistance(trueFront,this.results))
                console.log('Error ratio ' + this.errorRatio(trueFront,this.results))
                console.log('Inverted Gen Dist ' + this.igendist(trueFront,this.results))
                console.log('Distribution ' + this.distribute(trueFront,this.results))
                console.log('Eps indicator ' + this.epsIndicator(trueFront, this.results))
                var layout = {
                    xaxis: {title: 'Energía (GWh)' ,autorange: 'reversed'},
                    yaxis: {title: 'Costos (Millones de dólares)'}
                };
                var layout2 = {
                    xaxis: {title: 'Energía (GWh)' ,range: [1200,600]},
                    yaxis: {title: 'Costos (Millones de dólares)',range: [10,40]}
                };
                */
                //this.doBetter()
                this.results = await this.nsga.solve(Number(this.poblationSize),Number(this.generations),mdl )
                //console.log('Begin Random')
                //await this.compare(3)
                /*
                let rnd = this.generateParetoFrontRandom(100)
                console.log('Comparation')
                console.log(this.setCoverage(this.results,rnd))
                console.log(this.epsilonAdditive(this.results,rnd))
                */
            
                var config = {
                    toImageButtonOptions: {
                        format: 'svg', // one of png, svg, jpeg, webp
                        filename: 'custom_image',
                        //height: 500,
                        //width: 700,
                        scale: 1 // Multiply title/legend/axis/canvas sizes by this factor
                    }
                };
                var layout = {
                    
                };
                Plotly.newPlot('myDiv', data,layout, config);
                //Plotly.newPlot('myDiv2', [domin],layout, config);
                for(let i = 0 ; i < this.results.length; i++ ){
                    if(!this.test(this.results[i])){
                        console.log('fail!!')
                    }
                }
                
                /*
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
                //model["options"] = {"timeout": 10000}
                //model["options"] = {"tolerance": 50}
                model["ints"] = {}
                for (let i = 0; i < this.zoneID.length; i++) {
                    const item = this.zoneID[i]
                    const oneConstName = '$oneConstName$' + item
                    model["constraints"][oneConstName] = {"max":1}
                    model["constraints"]["costs"] = {"max":1000000}
                    model["constraints"]["energy"] = {"min":253684}
                    if(this.solarPanelsSelected[item]){
                        for (let j = 0; j < this.solarPanelsSelected[item].length; j++) {
                            const spid = this.solarPanelsSelected[item][j]
                            if(sp[spid]){
                                const varName = sp[spid].id + '$1$' + sp[spid].model + '$1$' + item
                                // const betaVarName = '$beta$' + varName
                                // const beta1ConstName = '$beta1c$' + varName 
                                // model["constraints"][beta1ConstName] = {"min": 0}
                                // const beta2ConstName = '$beta2c$' + varName
                                // model["constraints"][beta2ConstName] = {"max": 0}
                                // const beta3ConstName = '$beta3c$' + varName
                                // model["constraints"][beta3ConstName] = {"max": 1}
                                const mx = this.maxPanels(item,sp[spid].width,sp[spid].height,0)
                                //const mxFacilinZoneName = '$mxFacilinZoneName$' + varName
                                //model["constraints"][mxFacilinZoneName] = {"max": mx}
                                model["variables"][varName] = {}
                                model["variables"][varName]["energy"] = this.getSolarEnergy(item,sp[spid])*mx
                                model["variables"][varName]["costs"] = sp[spid].invest_cost*mx
                                model["variables"][varName][oneConstName] = 1
                                //model["variables"][varName][beta1ConstName] = -1
                                //model["variables"][varName][beta2ConstName] = -1
                                //model["variables"][varName][mxFacilinZoneName] = 1
                                //model["variables"][betaVarName] = {}
                                //model["variables"][betaVarName][beta1ConstName] = mx+1
                                //model["variables"][betaVarName][beta2ConstName] = 1
                                //model["variables"][betaVarName][beta3ConstName] = 1
                                //model["variables"][betaVarName][oneConstName] = 1
                                model["ints"][varName] = 1
                                //model["ints"][betaVarName] = 1
                            }
                        }
                    }
                    if(this.windTurbinesSelected[item]){
                        for (let j = 0; j < this.windTurbinesSelected[item].length; j++) {
                            const spid = this.windTurbinesSelected[item][j]
                            if(wt[spid]){
                                const varName = wt[spid].id + '$2$' + wt[spid].model + '$2$' + item
                                //const betaVarName = '$beta$' + varName
                                //const beta1ConstName = '$beta1c$' + varName 
                                //model["constraints"][beta1ConstName] = {"min": 0}
                                //const beta2ConstName = '$beta2c$' + varName
                                //model["constraints"][beta2ConstName] = {"max": 0}
                                //const beta3ConstName = '$beta3c$' + varName
                                //model["constraints"][beta3ConstName] = {"max": 1}
                                const mx = this.maxTurbines(item,wt[spid].rotor_diameter,0)
                                //const mxFacilinZoneName = '$mxFacilinZoneName$' + varName
                                //model["constraints"][mxFacilinZoneName] = {"max": mx}
                                model["variables"][varName] = {}
                                model["variables"][varName]["energy"] = this.getWindEnergy(item,wt[spid])*mx
                                model["variables"][varName]["costs"] = wt[spid].invest_cost*mx
                                model["variables"][varName][oneConstName] = 1
                                //model["variables"][varName][beta1ConstName] = -1
                                //model["variables"][varName][beta2ConstName] = -1
                                //model["variables"][varName][mxFacilinZoneName] = 1
                                //model["variables"][betaVarName] = {}
                                //model["variables"][betaVarName][beta1ConstName] = mx+1
                                //model["variables"][betaVarName][beta2ConstName] = 1
                                //model["variables"][betaVarName][beta3ConstName] = 1
                                //model["variables"][betaVarName][oneConstName] = 1
                                model["ints"][varName] = 1
                                //model["ints"][betaVarName] = 1
                            }
                        }
                    }
                }
                console.log(model)
                let results = solver.Solve(model);
                //this.decodeSolution(results["vertices"][0],wt,sp)
                console.log(results);
                */
                this.$bvModal.hide('busy');
                this.$bvToast.toast( `${this.__('Active Results and Charts tabs to inspect results')}`,{
                    title: `${this.__('Information')}`,
                    variant: 'success',
                    autoHideDelay: 5000,
                    solid: true
                })
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
            getSolarCoefficients(item,tec){
                var polygon = this.$drawnItems._layers[item].toGeoJSON();
                var bbox = turf.bbox(polygon);
                var area = this.calcArea(item)
                var options = {units: 'meters',mask: polygon};
                return this.gis.gridZoneGISValue(polygon,item,this.pvout).then((data)=>{
                    //Calculate mean solar potential
                    var mean = 0
                    for( var i = 0 ; i < data.length ; i++ ){
                        mean += data[i].value
                    }
                    mean /= (data.length)
                    //Calculate solar panels distribution
                    let w = tec.width
                    let h = tec.height
                    var grid = turf.pointGrid(bbox, h*1.6*2 , options);
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
                    var distribution = L.geoJSON();
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
                                    var cr = turf.destination(lp, h*1.88/1000.0, 0);
                                    var cl = turf.destination(fp, h*1.88/1000.0, 0);
                                    var points = turf.featureCollection([fp,lp,cl,cr]);
                                    var bbPolygon = turf.convex(points);
                                    distribution.addLayer(L.geoJSON(bbPolygon,{
                                        style: {
                                            "color": "black",
                                            "opacity": 0.9,
                                            "weight": 1
                                        }
                                    }).bindPopup(numberInRow+2 + ' panels'));
                                    numberOfPanels += (numberInRow+2)
                                    numberInRow = 0;
                                }
                                fp = -1;
                                lp = -1;
                            }
                            var distance = w/1000.0; //Kilometers
                            var bearing = 90;
                            p = turf.destination(p, distance, bearing);
                        }
                        if( lp != fp ){
                            var cr = turf.destination(lp, h*1.88/1000.0, 0);
                            var cl = turf.destination(fp, h*1.88/1000.0, 0);
                            var points = turf.featureCollection([fp,lp,cl,cr]);
                            var bbPolygon = turf.convex(points);
                            distribution.addLayer(L.geoJSON(bbPolygon,{
                                style: {
                                    "color": "black",
                                    "opacity": 0.9,
                                    "weight": 1
                                }
                            }).bindPopup(numberInRow+2 + ' panels'));
                            numberOfPanels += (numberInRow+2)
                            numberInRow = 0;
                        }
                    }
                    let results = {
                        'type': 'solar',
                        'model': tec.model,
                        'number': numberOfPanels,
                        'energy': (numberOfPanels*tec.nominal_power*mean)/1000.0,
                        'costs': (tec.invest_cost*numberOfPanels + tec.om_cost_per_year*20.0*numberOfPanels)/20.0,
                        'distribution': distribution,
                        'solarArea': area,
                        'windArea': 0.0
                    }
                    return results;
                })
            },
            getWindCoefficients(item,tec){
                var polygon = this.$drawnItems._layers[item].toGeoJSON();
                var area = this.calcArea(item)
                var mind = (5.0*tec.rotor_diameter)/1000
                var layer = null
                if(tec.iec_class == 1){
                    layer = this.cfiec1
                }else if(tec.iec_class == 2){
                    layer = this.cfiec2
                }else{
                    layer = this.cfiec3
                }
                return this.gis.gridZoneGISValue(polygon,item,layer).then((data)=>{
                    let points = data
                    points.sort((a,b)=>{
                        return (b.value - a.value)
                    })
                    var locations = []
                    var sum = points[0].value
                    locations.push(points[0].point)
                    for( var i = 1 ; i < points.length ; i++ ){
                        var p = points[i].point
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
                            sum += points[i].value
                        }
                    }
                    var distribution = L.geoJSON();
                    var windTurbineIcon = L.icon({
                        iconUrl: 'icon_eolic.png',
                        iconSize: [50, 50],
                        iconAnchor: [25,50]
                    })
                    for( var i = 0 ; i < (locations.length); i++ ){
                        var p = locations[i].geometry.coordinates
                        distribution.addLayer(L.marker([p[1],p[0]],{icon: windTurbineIcon}))
                    }
                    let results = {
                        'type': 'eolic',
                        'model': tec.model,
                        'number': locations.length,
                        'energy': sum*tec.nominal_power*8760.0,
                        'costs': (tec.invest_cost*locations.length + tec.om_cost_per_year*20.0*locations.length)/20.0,
                        'distribution': distribution,
                        'solarArea': 0.0,
                        'windArea': area,
                        'capacity_factor': sum/locations.length
                    }
                    return results;
                })
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
