<template>
    <b-modal 
        id="add-turbine" 
        centered 
        :title="isEdited == false?__('Add wind turbine'): __('Edit wind turbine')"
        no-close-on-esc
        no-close-on-backdrop
        size="md"
        @ok.prevent="onSave"
        @hidden="resetForm"
        @show="showModal"
    >
        <template slot="modal-footer" slot-scope="{ ok, cancel }">
            <b-button variant="secondary" :disabled="isBusy" @click="cancel()">{{__('Cancel')}}</b-button>
            <b-button variant="primary" :disabled="isBusy" @click="ok()">
                <template v-if="!isBusy">{{__('Save')}}</template>
                <template v-if="isBusy">
                    <b-spinner small></b-spinner>
                    <span class="ml-2">{{__('Busy')}}...</span>
                </template>
            </b-button>
        </template>

        <b-form class = "row">
            <b-form-group
                class="col-6"
                id="model-group"
                :label="__('Model')"
            >
                <b-form-input
                    id = "model"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.model == undefined"
                    v-model="form.model"
                    required
                    :placeholder="__('Enter model')"
                >
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.model != undefined" v-for="(item,index) in form.errors.model" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group
                class="col-6"
                id="nominal-power-group"
                :label="__('Nominal power')+' (kW)'"
            >
                <b-form-input
                    id = "nominal-power"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.nominal_power == undefined"
                    v-model="form.nominal_power"
                    required
                    :placeholder="__('Enter nominal power')"
                >
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.nominal_power != undefined" v-for="(item,index) in form.errors.nominal_power" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group
                class="col-6"
                id="iec-class-group"
                :label="__('IEC class')"
            >
                <b-form-select
                    id = "iec-class"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.iec_class == undefined"
                    v-model="form.iec_class"
                    :options="[{value: null,text: this.__('Select iec class')},{value: 1,text:'IEC I'},{value: 2,text:'IEC II'},{value: 3,text:'IEC III'}]"
                    required
                >
                    
                </b-form-select>
                <b-form-invalid-feedback :force-show="form.errors.iec_class != undefined" v-for="(item,index) in form.errors.iec_class" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group
                class="col-6"
                id="rotor-diameter-group"
                :label="__('Rotor diameter (m)')"
            >
                <b-form-input
                    id = "rotor-diameter"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.rotor_diameter == undefined"
                    v-model="form.rotor_diameter"
                    required
                    :placeholder="__('Enter rotor diameter')"
                >
                    
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.rotor_diameter != undefined" v-for="(item,index) in form.errors.rotor_diameter" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group
                class="col-6"
                id="invest-cost-group"
                :label="__('Invest cost ($)')"
            >
                <b-form-input
                    id = "invest-cost"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.invest_cost == undefined"
                    v-model="form.invest_cost"
                    required
                    :placeholder="__('Enter invest cost')"
                >
                    
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.invest_cost != undefined" v-for="(item,index) in form.errors.invest_cost" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group
                class="col-6"
                id="om-cost-group"
                :label="__('O&M cost ($/year)')"
            >
                <b-form-input
                    id = "om-cost"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.om_cost_per_year == undefined"
                    v-model="form.om_cost_per_year"
                    required
                    :placeholder="__('Enter O&M cost')"
                >
                    
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.om_cost_per_year != undefined" v-for="(item,index) in form.errors.om_cost_per_year" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>
        </b-form>

  </b-modal>
</template>
<script>
import { Bar } from 'vue-chartjs'
export default {
    data() {
        return {
            busy: false,
            isEdited: false,
            idEdited: null,
            form: {
                model: null,
                rotor_diameter: null,
                iec_class: null,
                invest_cost: null,
                nominal_power: null,
                om_cost_per_year: null,
                errors: {},
                validated: null,
            },
        };
    },
    computed: {
        isBusy: function(){
            return this.busy
        }
    },
    props: {
    },
    methods: {
        onSave(){
            this.busy = true
            if(!this.isEdited){
                axios.post('/wind_turbines',this.form).then( () =>{
                    this.resetForm()
                    this.$root.$emit('bv::refresh::table','wind-table')
                    this.$bvToast.toast( `${this.__('Data saved successfully')}`,{
                        title: `${this.__('Confirmation')}`,
                        variant: 'success',
                        autoHideDelay: 2000,
                        solid: true
                    })
                    this.busy = false;
                }).catch( errors => {
                    this.form.errors = errors.response.data.errors
                    this.form.validated=true
                    this.busy = false;
                })
            }else{
                axios.put(`/wind_turbines/${this.idEdited}`,this.form).then( () =>{
                    this.resetForm()
                    this.$bvModal.hide('add-turbine')
                    this.$root.$emit('bv::refresh::table','wind-table')
                    this.$bvToast.toast( `${this.__('Data edited successfully')}`,{
                        title: `${this.__('Confirmation')}`,
                        variant: 'success',
                        autoHideDelay: 2000,
                        solid: true
                    })
                    this.busy = false;
                }).catch( errors => {
                    this.form.errors = errors.response.data.errors
                    this.form.validated=true
                    this.busy = false;
                })
            }
        },
        resetForm(){
            this.busy = false
            this.isEdited = false
            this.idEdited = null
            this.form.iec_class = null
            this.form.rotor_diameter= null
            this.form.model = null
            this.form.invest_cost= null
            this.form.nominal_power= null
            this.form.om_cost_per_year=null
            this.form.errors= {}
            this.form.validated= null
        },
        showModal(){
            
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
        }
    },
    mounted(){
        this.$root.$on('editTurbine', (id) => {
            this.isEdited = true
            this.idEdited = id
            this.busy = true
            axios.get(`/wind_turbines/${id}`).then( (response) => {
                this.form.model = response.data.model
                this.form.rotor_diameter=response.data.rotor_diameter
                this.form.invest_cost=response.data.invest_cost
                this.form.nominal_power= response.data.nominal_power
                this.form.iec_class= response.data.iec_class
                this.form.om_cost_per_year=response.data.om_cost_per_year
                this.busy = false
            }).catch( (e)=>{

            })
        })
    }
}
</script>