<template>
    <div>
        <div class="d-flex justify-content-end">
            <b-button
                style="margin-bottom: 5px;"
                v-if="error"
                @click="$refs.zoneTable.refresh()"
                size="sm"
                variant="outline-primary"
            > <i class="fa fa-reload"></i> 
              {{__('Refresh table')}}
            </b-button>
        </div>
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
            error: false
        }
    },
    mounted(){
        
    },
    methods: {
        getItems(ctx){
            return axios.get(`/zones?page=${ctx.currentPage}&perPage=${ctx.perPage}`).then( response => {
                this.totalRows = response.data.total;
                this.error = false
                return response.data.data;
            }).catch(e=>{
                this.error = true
                this.$bvToast.toast('Something wrong happen. Please, refresh zones table, reload this page or try later.',{
                    title: 'Confirmation',
                    variant: 'danger',
                    autoHideDelay: 50000,
                    solid: true
                })
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
                        this.$drawnItems.removeLayer(this.$zone_layer[item.id])
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
            var layer = Vue.prototype.$drawnItems._layers[this.$zone_layer[item.id]]
            let bounds = layer._bounds
            Vue.prototype.$map.fitBounds(bounds);
        },
    },
    computed: {
        
    },
    mounted(){
        
    }
}
</script>
