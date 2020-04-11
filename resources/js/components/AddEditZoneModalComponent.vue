<template>
    <b-modal 
        id="add-edit-zone" 
        centered 
        :title="isEdited == false?__('Add') + ' ' + __('Zone'): __('Edit') + ' ' + __('Zone')"
        no-close-on-esc
        no-close-on-backdrop
        size="sm"
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
                class="col-12"
                id="name-group"
                :label="__('Name')"
            >
                <b-form-input
                    id = "name"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.name == undefined"
                    v-model="form.name"
                    required
                    :placeholder="__('Enter name for this zone')"
                >
                </b-form-input>
                <b-form-invalid-feedback :force-show="form.errors.name != undefined" v-for="(item,index) in form.errors.name" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group
                class="col-12"
                id="type-group"
                :label="__('Type')"
            >
                <b-form-radio-group
                    id = "type"
                    :disabled="isBusy"
                    :state="!form.validated?null:form.errors.type == undefined && form.errors.area == undefined"
                    v-model="form.type"
                    :options="radioOptions"
                >
                </b-form-radio-group>
                <b-form-invalid-feedback :force-show="form.errors.type != undefined" v-for="(item,index) in form.errors.type" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
                <b-form-invalid-feedback :force-show="form.errors.area != undefined" v-for="(item,index) in form.errors.area" :key = index>
                    {{item}}
                </b-form-invalid-feedback>
            </b-form-group>

        </b-form>

  </b-modal>
</template>
<script>
import area from '@turf/area'
export default {
    data() {
        return {
            busy: false,
            radioOptions: [
                {text: this.__('Solar park'),value: "solar"},
                {text: this.__('Eolic farm'),value: "eolic"}
            ],
            isEdited: false,
            idEdited: null,
            form: {
                name: null,
                type: null,
                feature: null,
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
                axios.post('/zones',this.form).then( (response) =>{
                    var data = JSON.parse(this.form.feature)
                    this.resetForm()
                    L.geoJSON(data,{
                        onEachFeature: (feature,layer) => {
                            layer._leaflet_id = response.data
                            layer.options.color = "#bada55"
                            layer.options.weight = 4
                            layer.options.opacity = 0.5
                            layer.options.fillOpacity = 0.2
                            Vue.prototype.$drawnItems.addLayer(layer)
                            layer.on('click', (e) => {
                                this.$root.$emit('zoneEdited',response.data);
                                this.$bvModal.show('add-edit-zone'); 
                            })
                        }
                    })
                    this.$bvModal.hide('add-edit-zone')
                    this.$root.$emit('bv::refresh::table','zone-table')
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
                axios.put(`/zones/${this.idEdited}`,this.form).then( () =>{
                    this.resetForm()
                    this.$bvModal.hide('add-edit-zone')
                    this.$root.$emit('bv::refresh::table','zone-table')
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
            this.form.name= null
            this.form.feature=null
            this.form.type = null
            this.form.area = null
            this.form.errors= {}
            this.form.validated= null
        },
        showModal(){
            
        }
    },
    mounted(){
        this.$root.$on('zoneCreated', (polygon) => {
            console.log(area(polygon))
            var polygon_for_db = JSON.stringify(polygon);
            this.form.area = area(polygon)
            this.form.feature = polygon_for_db
        })
        this.$root.$on('zoneEdited', (id) => {
            this.isEdited = true
            this.idEdited = id
            this.busy = true
            axios.get(`/zones/${id}`).then( (response) => {
                this.form.name = response.data.name
                this.form.type=response.data.type
                this.form.feature = response.data.feature
                this.form.area = response.data.area
                this.busy = false
            }).catch( (e)=>{

            })
        })
    }
}
</script>