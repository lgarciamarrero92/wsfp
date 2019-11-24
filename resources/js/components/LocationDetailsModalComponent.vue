<template>
    <b-modal 
        id="modal-center" 
        centered 
        title="Set details for this area"
        no-close-on-esc
        no-close-on-backdrop
        and hide-header-close
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
                    v-model="energySelected"
                    :options="energyOptions"
                ></b-form-checkbox-group>
            </b-form-group>
            {{energySelected}}
        </div>
    </div>

  </b-modal>
</template>
<script>
export default {
    data() {
        return {
            saving: false,
            energySelected: [],
            energyOptions: [
                {text: 'Solar', value: 'solar'},
                {text: 'Eolic', value: 'eolic'},
                {text: 'Biomass', value: 'biomass'},
                {text: 'Hydraulic', value: 'hydraulic'},
            ]
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
    }
}
</script>