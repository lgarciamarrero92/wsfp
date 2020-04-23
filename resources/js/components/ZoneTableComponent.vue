<template>
    <div>
        <b-table
            ref="zoneTable"
            id = "zone-table"
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
                    @click="zoomHandle(row.item)"
                    size="sm"
                    variant="outline-success"
                    v-b-tooltip.hover
                    :title="__('See in map')"
                > <i class="fa fa-map"></i> 
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
            <template v-slot:cell(type)="row">
                <div v-if="row.item.type == 'eolic'">
                    {{ __('Eolic') }}
                </div>
                <div v-else>
                    {{ __('Solar') }}
                </div>
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
            fields: [ { key: 'name', label: this.__('Name') }, { key: 'type', label: this.__('Type')}, { key: 'actions', label: this.__('Actions')}],
            totalRows: 1,
        }
    },
    mounted(){
        
    },
    methods: {
        getItems(ctx){
            return axios.get(`/zones?page=${ctx.currentPage}&perPage=${ctx.perPage}`).then( response => {
                this.totalRows = response.data.total;
                return response.data.data;
            }).catch(e=>{

            })
        },
        deleteHandle(item){
            this.zoomHandle(item)
            this.$bvModal.msgBoxConfirm(
                `${this.__('Are you sure you want to delete the zone')} ${item.name}?`,
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
                    axios.delete(`/zones/${item.id}`)
                    .then(response => {
                        this.$drawnItems.removeLayer(item.id)
                        this.$refs.zoneTable.refresh();
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
        },
        editHandle(item){
            this.$root.$emit('zoneEdited',item.id);
            this.$bvModal.show('add-edit-zone');  
        },
        zoomHandle(item){
            let bounds = Vue.prototype.$drawnItems._layers[item.id]._bounds
            Vue.prototype.$map.fitBounds(bounds);
        },
    },
    computed: {
        
    },
    mounted(){
        
    }
}
</script>
