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
                    @click="zoomHandle(row.item)"
                    size="sm"
                    variant="outline-success"
                    v-b-tooltip.hover
                    title="See in map"
                > <i class="fa fa-map"></i> 
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
            fields: ['name','type','actions'],
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
        editHandle(item){
            this.$root.$emit('zoneEdited',item.id);
            this.$bvModal.show('add-edit-zone');  
        },
        zoomHandle(item){
            let bounds = Vue.prototype.$drawnItems._layers[item.id]._bounds
            Vue.prototype.$map.fitBounds(bounds);
        }
    },
    computed: {
        
    }
}
</script>
