<template>
    <div>
        <div class="d-flex justify-content-end">
            <b-button
                style="margin-bottom: 5px;"
                v-if="error"
                @click="$refs.solarTable.refresh()"
                size="sm"
                variant="outline-primary"
            > <i class="fa fa-reload"></i> 
              {{__('Refresh table')}}
            </b-button>
        </div>
        <div class="d-flex justify-content-end">
            <b-button
                style="margin-bottom: 5px;"
                @click="$bvModal.show('add-panel')"
                size="sm"
                variant="outline-success"
                v-b-tooltip.hover
                :title="__('Add solar panel')"
            > <i class="fa fa-plus"></i> 
            </b-button>
        </div>
        <b-table
            ref="solarTable"
            id = "solar-table"
            :items ="getItems" 
            :fields="fields"
            :total-rows="totalRows"
            :per-page="3"
            :current-page="currentPage"
            show-empty
            :empty-text="__('There are no records to show')"
        >
            <template v-slot:table-busy>
                <div class = "text-center">
                    <b-spinner></b-spinner>
                </div>
            </template>
            <template v-slot:cell(actions)="row">
                <b-button
                    @click="editHandle(row.item)"
                    size="sm"
                    variant="outline-info"
                    v-b-tooltip.hover
                    :title="__('Edit')"
                > <i class="fa fa-edit"></i> 
                </b-button>
                <b-button
                    @click="deleteHandle(row.item)"
                    size="sm"
                    variant="outline-danger"
                    v-b-tooltip.hover
                    :title="__('Delete')"
                > <i class="fa fa-trash"></i> 
                </b-button>
            </template>
            <template v-slot:cell(dimentions)="row">
                {{row.item.height}} x {{row.item.width}}
            </template>
        </b-table>
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="3"
            aria-controls="solar-table"
        >
        </b-pagination>
    </div>
</template>
<script>
export default {
    data () {
        return {
            currentPage: 1,
            fields: [
                {key: 'model', label: this.__('Model')},
                {key: 'nominal_power',label: this.__('Power') + ' (Wp)'},
                {key:'invest_cost', label: this.__('Inv. Cost ($)')},
                {key:'dimentions',label:this.__('Dimentions (m)')},
                {key: 'actions', label: this.__('Actions')}
            ],
            totalRows: 1,
            error: false
        }
    },
    mounted(){
        
    },
    methods: {
        getItems(ctx){
            return axios.get(`/solar_panels?page=${ctx.currentPage}&perPage=${ctx.perPage}`).then( response => {
                this.totalRows = response.data.total;
                this.error = false
                return response.data.data;
            }).catch(e=>{
                this.error = true
                this.$bvToast.toast('Something wrong happen. Please, refresh solar panels table, reload this page or try later.',{
                    title: 'Confirmation',
                    variant: 'danger',
                    autoHideDelay: 50000,
                    solid: true
                })
            })
        },
        editHandle(item){
            this.$root.$emit('editPanel',item.id);
            this.$bvModal.show('add-panel');
        },
        deleteHandle(item){
            this.$bvModal.msgBoxConfirm(
                `${this.__('Are you sure you want to delete the solar panel')} ${item.model}?`,
                {
                    title: `${this.__('Confirm')}`,
                    size: "sm",
                    okVariant: "danger",
                    okTitle: `${this.__('Yes')}`,
                    cancelTitle: `${this.__('No')}`,
                    footerClass: "p-2",
                    hideHeaderClose: false,
                    centered: true
                }
            )
            .then( value => {
                if (value) {
                    axios.delete(`/solar_panels/${item.id}`)
                    .then(response => {
                        this.$refs.solarTable.refresh();
                        this.$bvToast.toast( `${this.__('Data deleted successfully')}`,{
                            title: `${this.__('Confirmation')}`,
                            variant: 'success',
                            autoHideDelay: 2000,
                            solid: true
                        })
                    })
                    .catch(err => {
                        console.error(err);
                    });
                }
            });
        }
    },
    computed: {
        
    }
}
</script>
<style>
.card-body{
    padding: 0.8rem;
}
</style>