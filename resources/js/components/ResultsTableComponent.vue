<template>
    <div>
        <div class="d-flex">
            <div class="mx-2">
                <label for="range-energy"> Energy: {{getRelativesWeights[0].toFixed(2)}}</label>
                <b-form-input id="range-energy" min="0" max="10" type="range" v-model="weights[0]"></b-form-input>
            </div>
            <div class="mx-2">
                <label for="range-costs"> Costs: {{getRelativesWeights[1].toFixed(2)}}</label>
                <b-form-input id="range-costs" min="0" max="10" type="range" v-model="weights[1]"></b-form-input>
            </div>
            <div class="mx-2">
                <label for="range-solar-area"> Solar Area: {{getRelativesWeights[2].toFixed(2)}}</label>
                <b-form-input id = "range-solar-area" min="0" max="10" type="range" v-model="weights[2]" ></b-form-input>
            </div>
            <div class="mx-2">
                <label for="range-wind-area"> Wind Area: {{getRelativesWeights[3].toFixed(2)}}</label>
                <b-form-input id = "range-wind-area" min="0" max="10" type="range" v-model="weights[3]" ></b-form-input>
            </div>
        </div>
        <b-table
            ref="resultsTable"
            id = "results-table"
            :items ="getItems" 
            :fields="fields"
            :total-rows="totalRows"
            :per-page="6"
            :current-page="currentPage"
            show-empty
            small
            responsive
            sticky-header
        >
            <template v-slot:table-busy>
                <div class = "text-center">
                    <b-spinner></b-spinner>
                </div>
            </template>
            <template v-slot:cell(actions)="row">
                <b-button
                    @click="seeInMap(row.item)"
                    size="sm"
                    variant="outline-success"
                    v-b-tooltip.hover
                    title="See in map"
                > <i class="fa fa-map"></i> 
                </b-button>
                <b-button
                    @click="row.toggleDetails"
                    size="sm"
                    variant="outline-info"
                    v-b-tooltip.hover
                    :title="row.detailsShowing?'Hide details':'See details'"
                > 
                    <i v-if="row.detailsShowing" class="fa fa-eye-slash"></i>
                    <i v-else class="fa fa-eye"></i> 
                </b-button>
            </template>

            <template v-slot:row-details="row">
                <b-table
                    ref="detailsTable"
                    id = "details-table"
                    table-variant="info"
                    bordered
                    :items ="getDetails(row.item)" 
                    :fields="fieldsDetails"
                    small
                    responsive
                    sticky-header
                >
                    <template v-slot:cell(energy)="row">
                        <div>
                            {{(row.item.energy/1000000).toFixed(3)}}
                        </div>
                    </template>
                    <template v-slot:cell(costs)="row">
                        <div>
                            {{(row.item.costs/1000000).toFixed(3)}}
                        </div>
                    </template>
                </b-table>
            </template>

            <template v-slot:cell(energy)="row">
                <div>
                    {{(row.item.energy/1000000).toFixed(3)}}
                </div>
            </template>
            <template v-slot:cell(windArea)="row">
                <div>
                    {{(row.item.windArea/10000).toFixed(2)}}
                </div>
            </template>
            <template v-slot:cell(solarArea)="row">
                <div>
                    {{(row.item.solarArea/10000).toFixed(2)}}
                </div>
            </template>
            <template v-slot:cell(costs)="row">
                <div>
                    {{(row.item.costs/1000000).toFixed(3)}}
                </div>
            </template>
        </b-table>
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="6"
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
                
                {key: 'energy',label: 'Energy (GWh/year)',sortable: true},
                {key: 'costs',label: 'Costs (Millions of dolars/year)',sortable: true},
                {key: 'windArea',label: 'Turbines impacted area (ha)',sortable: true},
                {key: 'solarArea',label: 'Panels impacted area (ha)',sortable: true},
                {
                    key: 'costOfEnergy',
                    label: 'Cost of Energy ($/kWh)',
                    formatter: (value,key,item) => {
                        return (item.costs/item.energy).toFixed(5)
                    },
                    sortable: true,
                    sortByFormatted: true
                },
                {
                    key: 'weight',
                    label: 'Weight',
                    formatter: (value,key,item) => {
                        return Number(( 
                                    (item.energy/this.mE)*this.getRelativesWeights[0]-
                                    (item.costs/this.mC)*this.getRelativesWeights[1]-
                                    (item.solarArea/this.mSA)*this.getRelativesWeights[2]-
                                    (item.windArea/this.mWA)*this.getRelativesWeights[3]
                                ).toFixed(2))
                    },
                    sortable: true,
                    sortByFormatted: true
                },
                {key: 'actions',label: 'Actions' /*, stickyColumn: true*/},
            ],
            fieldsDetails: [
                'zone','type','model','number',{key: 'energy',label: 'Energy (GWh/year)',sortable: true},{key: 'costs',label: 'Costs (Millions of dolars/year)',sortable: true},
            ],
            totalRows: 1,
            mE: 0,
            mC: 0,
            mWA: 0,
            mSA: 0,
            inMap: null,
            weights: [5,5,5,5]
        }
    },
    mounted(){
        
    },
    props: {
        items: Array
    },
    methods: {
        
        seeInMap(item){
            if(this.inMap){
                let zones = Object.keys(this.inMap.details)
                for(var i = 0 ; i < zones.length; i++ ){
                    if(this.inMap["details"][zones[i]].distribution)
                        this.inMap["details"][zones[i]].distribution.removeFrom(Vue.prototype.$map)
                }
            }
            this.inMap = item
            let zones = Object.keys(item.details)
            for(var i = 0 ; i < zones.length; i++ ){
                if(item["details"][zones[i]].distribution)
                    item["details"][zones[i]].distribution.addTo(Vue.prototype.$map)
            }
        },
        getDetails(item){
            let zones = Object.keys(item.details)
            let items = []
            for(var i = 0 ; i < zones.length; i++ ){
                if(item["details"][zones[i]].distribution){
                    items.push({
                         'energy': item["details"][zones[i]].energy,
                         'costs': item["details"][zones[i]].costs,
                         'model': item["details"][zones[i]].model,
                         'number': item["details"][zones[i]].number,
                         'type': item["details"][zones[i]].type,
                         'zone': item["details"][zones[i]].zone
                    })
                }
                else{
                    items.push({
                         'energy': 0,
                         'costs': 0,
                         'model': '-',
                         'number': 0,
                         'type': '-',
                         'zone': item["details"][zones[i]].zone
                    })
                }
            }
            return items;
        }
    },
    computed: {
        getItems(){
            this.mE = this.mC = this.mWA = this.mSA = 0
            this.totalRows = this.items.length
            this.items.forEach(element=>{
                if(element.costs > this.mC ){
                    this.mC = element.costs
                }
                if(element.energy > this.mE ){
                    this.mE = element.energy
                }
                if(element.solarArea > this.mSA ){
                    this.mSA = element.solarArea
                }
                if(element.windArea > this.mWA ){
                    this.mWA = element.windArea
                }
            })
            return this.items
        },
        getRelativesWeights(){
            let tot = Number(this.weights[0])+Number(this.weights[1])+Number(this.weights[2])+Number(this.weights[3])
            return [ Number(this.weights[0])/tot , Number(this.weights[1])/tot , Number(this.weights[2])/tot , Number(this.weights[3])/tot ]
        }
    }
}
</script>
