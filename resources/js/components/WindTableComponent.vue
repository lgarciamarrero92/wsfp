<template>
    <div>
        <div class="d-flex justify-content-end">
            <b-button
                style="margin-bottom: 5px;"
                @click="$bvModal.show('add-turbine')"
                size="sm"
                variant="outline-success"
                v-b-tooltip.hover
                :title="__('Add wind turbine')"
            > <i class="fa fa-plus"></i> 
            </b-button>
        </div>
        <b-table
            ref="windTable"
            id = "wind-table"
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
        </b-table>
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="3"
            aria-controls="wind-table"
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
                {key: 'nominal_power',label: this.__('Power') + ' (kW)'},
                {key:'invest_cost', label: this.__('Inv. Cost ($)')},
                {key:'rotor_diameter',label: this.__('Diameter') + ' (m)'},
                {key: 'actions', label: this.__('Actions')}
            ],
            totalRows: 1,
        }
    },
    mounted(){
        
    },
    methods: {
        getItems(ctx){
            return axios.get(`/wind_turbines?page=${ctx.currentPage}&perPage=${ctx.perPage}`).then( response => {
                this.totalRows = response.data.total;
                return response.data.data;
            }).catch(e=>{

            })
        },
        editHandle(item){
            this.$root.$emit('editTurbine',item.id);
            this.$bvModal.show('add-turbine');
        },
        deleteHandle(item){
            this.$bvModal.msgBoxConfirm(
                `${this.__('Are you sure you want to delete the wind turbine')} ${item.model}?`,
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
                    axios.delete(`/wind_turbines/${item.id}`)
                    .then(response => {
                        this.$refs.windTable.refresh();
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
