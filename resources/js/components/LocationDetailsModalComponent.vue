<template>
    <b-modal 
        id="modal-center" 
        centered 
        title="Set area details"
        no-close-on-esc
        no-close-on-backdrop
        size="lg"
        @ok.prevent="onSave"
        @hidden="resetModal"
        @show="showModal"
    >
    <template slot="modal-footer" slot-scope="{ ok, cancel }">
        <b-button variant="secondary" :disabled="isBusy" @click="cancel()">Cancel</b-button>
        <b-button variant="primary" :disabled="isBusy" @click="ok()">
        <template v-if="!isBusy">Save</template>
        <template v-if="isBusy">
            <b-spinner small></b-spinner>
            <span class="ml-2">Saving...</span>
        </template>
      </b-button>
    </template>
    
    <div id = "form" class = "container" >
        <div id = "energy-types" class = "row">
            <b-form-group>
                <template slot="label">
                    <strong>Select which energy sources can be used</strong>
                    <span class="required">*</span>
                </template>
                <b-form-checkbox-group
                    v-model="form.energies"
                    :options="energyOptions"
                ></b-form-checkbox-group>
            </b-form-group>
        </div>

        <div v-if="form.energies.includes('solar')" id = "solar" class = "row">
            <div class = "col-12 p-0 my-3">
                <strong>Select details for solar source</strong>
                <span class="required">*</span>
            </div>
            <div class = "col-12 p-0"> 
                <b-form-group
                    label-cols="3"
                    description="How far the panel is inclined from the horizontal, in degrees. A tilt of 0° is a panel facing directly upwards, 90° is a panel installed vertically, facing sideways."
                    label="Tilt (°):"
                >
                    <b-form-input
                        v-model="form.solar.tilt"
                    >

                    </b-form-input>
                </b-form-group>
            </div>
            <div class = "col-12 p-0"> 
                <b-form-group
                    label-cols="3"
                    description="Compass direction the panel is facing (clockwise). An azimuth angle of 180 degrees means poleward facing, so for latitudes >=0 is interpreted as southwards facing, else northwards facing."
                    label="Azimuth (°):"
                >
                    <b-form-input 
                        v-model="form.solar.azimuth"
                    ></b-form-input>
                </b-form-group>
            </div>
            <button class = "btn btn-primary" :disabled="solarBusy" @click="createSolarGraph">
                <div v-if="!solarBusy">Show charts</div>
                <div v-else> 
                    <b-spinner small></b-spinner>
                    <span class="ml-2">Creating...</span>
                </div>
            </button>
            
        </div>
       <div v-show="form.energies.includes('solar')" class = "row">
            <div class = "col-5"></div>
            <div class = "col-2">
                <b-spinner v-if="solarBusy" style="position: relative; top: 175px;" small></b-spinner>
            </div>
            <div class = "col-5"></div>
            <div v-show="showSolarGraph" class = "col-12" id="solarchart" style="height: 350px;"></div>
        </div>
        <div v-if="form.energies.includes('eolic')" id = "eolic" class = "row">
            <div class = "col-12 p-0 my-3">
                <strong>Select details for eolic source</strong>
                <span class="required">*</span>
            </div>
            <div class = "col-12 p-0"> 
                <b-form-group
                    label-cols="3"
                    description="The height of the turbine's tower, that is, how far the blades are centred above the ground. Hub heights are limited to between 10 and 150 m."
                    label="Hub height (m):"
                >
                    <b-form-input
                        v-model="form.eolic.height"
                    >

                    </b-form-input>
                </b-form-group>
            </div>
            <button class = "btn btn-primary" :disabled="eolicBusy" @click="createEolicGraph">
                <div v-if="!eolicBusy">Show charts</div>
                <div v-else> 
                    <b-spinner small></b-spinner>
                    <span class="ml-2">Creating...</span>
                </div>
            </button>
            
        </div>
        <div v-show="form.energies.includes('eolic')" class = "row">
            <div class = "col-5"></div>
            <div class = "col-2">
                <b-spinner v-if="eolicBusy" style="position: relative; top: 175px;" small></b-spinner>
            </div>
            <div class = "col-5"></div>
            <div v-show="showEolicGraph" class = "col-12" id="myfirstchart" style="height: 350px;">
                
            </div>
        </div>
    </div>

  </b-modal>
</template>
<script>
import { Bar } from 'vue-chartjs'
export default {
    data() {
        return {
            saving: false,
            showEolicGraph: false,
            showSolarGraph: false,
            eolicBusy: false,
            solarBusy: false,
            energyOptions: [
                {text: 'Solar', value: 'solar'},
                {text: 'Eolic', value: 'eolic'},
                {text: 'Biomass', value: 'biomass'},
                {text: 'Hydraulic', value: 'hydraulic'},
            ],
            form: {
                energies: [],
                solar: {
                    tilt: 35,
                    azimuth: 45
                },
                eolic: {
                    height: 80
                }
            },
        };
    },
    computed: {
        isBusy: function(){
            return this.saving
        }
    },
    props: {
        type: String,
        centroid: Array
    },
    methods: {
        onSave(){
            let vm = this
            this.saving = true
            setTimeout(function(){
                vm.saving = false
                vm.$bvModal.hide('modal-center')
                vm.showMsgBoxOk()
            },2000)
        },
        resetModal(){
        },
        showModal(){
            setTimeout( () => {
                this.eolicGraph = new Morris.Line({
                    element: 'myfirstchart',
                    xkey: 'day',
                    // A list of names of data record attributes that contain y-values.
                    ykeys: ['wind_speed'],
                    // Labels for the ykeys -- will be displayed when you hover over the
                    // chart.
                    labels: ['Wind Speed']
                })
            },1000)
            
        },
        showMsgBoxOk() {
            this.$bvModal.msgBoxOk(
                "Data saved successfully.",
                {
                    title: "Confirmation",
                    size: "sm",
                    okVariant: "success",
                    headerClass: "p-2 border-bottom-0",
                    footerClass: "p-2 border-top-0",
                    centered: true
                }
            ).then( () => {
               
            });
        },
        createSolarGraph(){
            this.solarBusy = true
            this.showSolarGraph = true
            let vm = this
            var config = {
                headers: {
                    'Authorization' : 'Token f7b818b71178a1dfc8c6756aa5957e88389e73b8'
                }
            };
            axios.get(`https://cors-anywhere.herokuapp.com/https://www.renewables.ninja/api/data/pv?&lat=${this.centroid[1]}&lon=${this.centroid[0]}&date_from=2018-01-01&date_to=2018-12-31&capacity=1&dataset=merra2&system_loss=0.1&tracking=0&format=json&local_time=true&raw=true&mean=month&tilt=${this.form.solar.tilt}&azim=${this.form.solar.azimuth}`,config).then( function(response){
                console.log(response.data)
                var x = Object.keys(response.data.data)
                //console.log(response.data.data)
                var y = []
                var data = []
                x.forEach(element => {
                    data.push({
                        day: element,
                        irradiance_direct: response.data.data[element].electricity
                    })
                });
                $("#solarchart").empty();
                new Morris.Line({
                    // ID of the element in which to draw the chart.
                    element: 'solarchart',
                    // Chart data records -- each entry in this array corresponds to a point on
                    // the chart.
                    data: data,
                    postUnits: 'kW/m²',
                    // The name of the data record attribute that contains x-values.
                    xkey: 'day',
                    // A list of names of data record attributes that contain y-values.
                    ykeys: ['irradiance_direct'],
                    // Labels for the ykeys -- will be displayed when you hover over the
                    // chart.
                    labels: ['Irradiance direct']
                });
                
                vm.solarBusy = false
            })

        },
        createEolicGraph(){
            this.eolicBusy = true
            this.showEolicGraph = true
            let vm = this
            var config = {
                headers: {
                    'Authorization' : 'Token f7b818b71178a1dfc8c6756aa5957e88389e73b8'
                }
            };
            axios.get(`https://cors-anywhere.herokuapp.com/https://www.renewables.ninja/api/data/wind?&lat=${this.centroid[1]}&lon=${this.centroid[0]}&date_from=2018-01-01&date_to=2018-12-31&capacity=1&dataset=merra2&height=${this.form.eolic.height}&turbine=Vestas+V80+2000&format=json&local_time=true&raw=true&mean=month`,config).then( function(response){
                var x = Object.keys(response.data.data)
                //console.log(response.data.data)
                var y = []
                var data = []
                x.forEach(element => {
                    data.push({
                        day: element,
                        wind_speed: response.data.data[element].wind_speed
                    })
                });
                $("#myfirstchart").empty();
                var eolicGraph = new Morris.Line({
                    // ID of the element in which to draw the chart.
                    element: 'myfirstchart',
                    // Chart data records -- each entry in this array corresponds to a point on
                    // the chart.
                    data: data,
                    // The name of the data record attribute that contains x-values.
                    xkey: 'day',
                    postUnits: 'm/s',
                    // A list of names of data record attributes that contain y-values.
                    ykeys: ['wind_speed'],
                    // Labels for the ykeys -- will be displayed when you hover over the
                    // chart.
                    labels: ['Wind Speed']
                });
                //C89w9a7UrQKd2DE
                vm.eolicBusy = false
            })

        }
    },
    mounted(){
        
        
    }
}
</script>