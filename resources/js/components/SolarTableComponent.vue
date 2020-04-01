<template>
    <div>
        <b-table
            ref="solarTable"
            id = "solar-table"
            :items ="getItems" 
            :fields="fields"
            :total-rows="totalRows"
            :per-page="3"
            :current-page="currentPage"
            show-empty
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
                    title="Edit"
                > <i class="fa fa-edit"></i> 
                </b-button>
                <b-button
                    @click="deleteHandle(row.item)"
                    size="sm"
                    variant="outline-danger"
                    v-b-tooltip.hover
                    title="Delete"
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
            fields: ['model',{key: 'nominal_power',label: 'Power (Wp)'},{key:'invest_cost',label: 'Inv. Cost ($)'},{key:'dimentions',label:'Dimentions (m)'},'actions'],
            totalRows: 1,
        }
    },
    mounted(){
        
    },
    methods: {
        getItems(ctx){
            return axios.get(`/solar_panels?page=${ctx.currentPage}&perPage=${ctx.perPage}`).then( response => {
                this.totalRows = response.data.total;
                return response.data.data;
            }).catch(e=>{

            })
        },
        editHandle(item){
            this.$root.$emit('editPanel',item.id);
            this.$bvModal.show('add-panel');
        },
        deleteHandle(item){
            this.$bvModal.msgBoxConfirm(
                `Are you sure you want to delete the solar panel ${item.model}?`,
                {
                    title: "Confirm",
                    size: "sm",
                    okVariant: "danger",
                    okTitle: "Yes",
                    cancelTitle: "No",
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
                        this.$bvToast.toast( 'Data deleted successfully',{
                            title: 'Confirmation',
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
