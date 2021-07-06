<template>
    <div>
        <busy-modal></busy-modal>
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
                            :label="__('Population size')"
                        >
                            <b-form-input 
                                id="size" 
                                size="sm" 
                                type="number" 
                                min="10" 
                                max="500" 
                                v-model="populationSize"
                                :state="(!(populationSize<10 || populationSize>500))?null:false"
                            >

                            </b-form-input>
                            <b-form-invalid-feedback :force-show="populationSize<10 || populationSize>500">
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
                                @click="getResults()" 
                                variant="primary" 
                                size="sm"
                                :disabled="(generations<20 || generations>2000) || (populationSize<10 || populationSize>500)"
                            >
                                {{__('Run')}}
                            </b-button>
                        </b-form-group>

                    </b-form>
                </b-tab>
                <b-tab :title="__('Results')">
                    <b-alert :show ="results.length == 0" variant="info">{{ __('Run simulations first') }}</b-alert>
                    <results-table v-show="results.length > 0" :items="results" :idealPoint="idealPoint" :nadirPoint="nadirPoint"/>
                </b-tab>
                <b-tab :title="__('Charts')">
                    <b-alert :show ="results.length == 0" variant="info">{{ __('Run simulations first') }}</b-alert>
                    <div id="par-coords-plot">
                    </div>
                </b-tab>
            </b-tabs>
            <b-card-footer>
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
                        ©{{new Date().getFullYear()}} Luis E. Garcia Marrero
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
    import cfc from '../scripts/coefficients/main.js'
    import nsga2 from '../scripts/nsga2/main.js'

    export default {
        data () {
            return {
                populationSize: 100,
                generations: 100,
                tabIndex: 0,
                model: {},
                results: [],
                mE: 0.0,//Maximun Energy that can be produced
                mC: 0.0,//Maximun Costs
                mSA: 0.0,//Maximun solar area
                mWA: 0.0,//Maximun wind area
                nadirPoint: {},
                idealPoint: {},
                cfc: new cfc(),
                nsga: new nsga2()
            }
        },
        props:{
        },
        created(){
            Vue.prototype.$user_id = this.user_id
        },

        mounted() {
            this.cfc.listen.on('progress', (val)=>{
                this.$root.$emit('coefficients-progress',val)
            })
            this.nsga.listen.on('progress', (val)=>{
                this.$root.$emit('generation-progress',val)
            })
        },
        methods: {
            downloadObjectAsJson(exportObj, exportName){
                var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportObj));
                var downloadAnchorNode = document.createElement('a');
                downloadAnchorNode.setAttribute("href",     dataStr);
                downloadAnchorNode.setAttribute("download", exportName + ".json");
                document.body.appendChild(downloadAnchorNode); // required for firefox
                downloadAnchorNode.click();
                downloadAnchorNode.remove();
            },
            async getModel(){
                
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
                //this.downloadObjectAsJson(model,'data')
                let zones = Object.keys(model)                
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
                this.idealPoint = {
                    'energy': this.mE,
                    'costs': 0,
                    'solarArea': 0,
                    'windArea': 0
                }
                this.nadirPoint = {
                    'energy': 0,
                    'costs': this.mC,
                    'solarArea': this.mSA,
                    'windArea': this.mWA
                }
                this.model = model
                return model
            },
            async getResults(){
                this.$bvModal.show('busy');
                console.log('START')
                var error = false
                let mdl = await this.getModel().catch((e)=>{
                    error = true
                    this.$bvModal.hide('busy');
                    this.$bvToast.toast('Something wrong happen. Please, run simulations again.',{
                        title: 'Confirmation',
                        variant: 'danger',
                        autoHideDelay: 50000,
                        solid: true
                    })
                })
                console.log('END')
                this.$root.$on('generation-progress', (val) => {     
                    if(val.gen == this.generations ){
                        this.showParCoordsPlot(val)
                    }
                });
                this.results = await this.nsga.solve(Number(this.populationSize),Number(this.generations),mdl).catch((e)=>{
                    if(!error){
                        error = true
                        this.$bvModal.hide('busy');
                        this.$bvToast.toast('Something wrong happen. Please, run simulations again.',{
                            title: 'Confirmation',
                            variant: 'danger',
                            autoHideDelay: 50000,
                            solid: true
                        })
                    }
                })
                if(!error){
                    this.$bvModal.hide('busy');
                    this.$bvToast.toast( `${this.__('Active Results and Charts tabs to inspect results')}`,{
                        title: `${this.__('Information')}`,
                        variant: 'success',
                        autoHideDelay: 5000,
                        solid: true
                    })
                }
            },
            showParCoordsPlot(val){
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
                            label: this.__('Impacted area (panels) (ha)'),
                            values: []
                        },
                        {
                            range: [0, this.mWA/10000],
                            label: this.__('Impacted area (turbines) (ha)'),
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
                var config = {
                    toImageButtonOptions: {
                        format: 'svg', // one of png, svg, jpeg, webp
                        filename: 'parcoords_plot',
                        height: 540,//540
                        width: 630,//630
                        scale: 1 // Multiply title/legend/axis/canvas sizes by this factor
                    }
                };
                var layout = {
                    
                };
                Plotly.newPlot('par-coords-plot',[trace],layout, config);
            }
        }
    }
</script>
