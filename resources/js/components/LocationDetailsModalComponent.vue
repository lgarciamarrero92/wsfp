<template>
    <b-modal 
        id="modal-center" 
        centered 
        title="Set details for this area"
        no-close-on-esc
        no-close-on-backdrop
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
                    Select which energy sources can be used
                    <span class="required">*</span>
                </template>
                <b-form-checkbox-group
                    v-model="form.energies"
                    :options="energyOptions"
                ></b-form-checkbox-group>
            </b-form-group>
        </div>

        <div id = "solar" class = "row">
            <div class = "col-12 p-0 my-3">
                Select details for solar source
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
        </div>
        
        <div id = "eolic" class = "row">
            <div class = "col-12 p-0 my-3">
                Select details for eolic source
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
        </div>


    </div>

  </b-modal>
</template>
<script>
export default {
    data() {
        return {
            saving: false,
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
                    azimuth: 180
                }
            }
        };
    },
    computed: {
        isBusy: function(){
            return this.saving
        }
    },
    props: {
        type: String
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
            console.log(this.type)
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
    },
    mounted(){
        
        var config = {
            headers: {
                'Authorization' : 'Token f7b818b71178a1dfc8c6756aa5957e88389e73b8'
            }
        };
        axios.get('https://cors-anywhere.herokuapp.com/https://www.renewables.ninja/api/data/wind?&lat=56&lon=-3&date_from=2018-01-01&date_to=2018-02-28&capacity=1&dataset=merra2&height=100&turbine=Vestas+V80+2000&format=json&local_time=true',config).then( function(response){
            console.log(response.data)
        })
    }
}
</script>