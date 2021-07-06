<template>
    <div>
        <div class="row m-1" style="border: 1px solid black;">
            <div class="col-12 pt-2">
                <strong style="
                    position: absolute;
                    bottom: 0.3px;
                    background-color: white;
                    padding-left: 4px;
                    padding-right: 4px;
                ">
                    Compromise programming
                </strong> 
            </div>
            <div class="col-3">
                <label for="range-energy">  {{ __('Energy') + ': ' + getRelativesWeights[0].toFixed(2)}}</label>
                <b-form-input id="range-energy" min="0" max="10" type="range" v-model="weights[0]"></b-form-input>
            </div>
            <div class="col-3">
                <label for="range-costs">  {{ __('Costs') + ': ' + getRelativesWeights[1].toFixed(2)}}</label>
                <b-form-input id="range-costs" min="0" max="10" type="range" v-model="weights[1]"></b-form-input>
            </div>
            <div class="col-3">
                <label for="range-solar-area"> {{__('Solar Area') + ': ' + getRelativesWeights[2].toFixed(2)}}</label>
                <b-form-input id = "range-solar-area" min="0" max="10" type="range" v-model="weights[2]" ></b-form-input>
            </div>
            <div class="col-3">
                <label for="range-wind-area">  {{ __('Wind Area') + ': ' + getRelativesWeights[3].toFixed(2)}}</label>
                <b-form-input id = "range-wind-area" min="0" max="10" type="range" v-model="weights[3]" ></b-form-input>
            </div>
            <div class="col-12">
                <b-form-group v-slot="{ ariaDescribedby }">
                    <b-form-radio-group
                        id="Ldis"
                        v-model="Ldis"
                        :aria-describedby="ariaDescribedby"
                        name="l-dis"
                        class="d-flex justify-content-between"
                    >
                        <b-form-radio value="1">L1</b-form-radio>
                        <b-form-radio value="2">L2</b-form-radio>
                        <b-form-radio value="3">L3</b-form-radio>
                        <b-form-radio value="-1">L&#8734;</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
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
            :empty-text="__('There are no records to show')"
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
                    :title="__('See in map')"
                > <i class="fa fa-map"></i> 
                </b-button>
                <b-button
                    @click="row.toggleDetails"
                    size="sm"
                    variant="outline-info"
                    v-b-tooltip.hover
                    :title="row.detailsShowing? __('Hide details'): __('See details')"
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
                    <template v-slot:cell(type)="row">
                        <div v-if="row.item.type == 'eolic'">
                            {{ __('Eolic') }}
                        </div>
                        <div v-if="row.item.type == 'solar'">
                            {{ __('Solar') }}
                        </div>
                        <div v-if="row.item.type == '-'">
                            -
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
                
                {key: 'energy',label: this.__('Energy (GWh/year)'),sortable: true},
                {key: 'costs',label: this.__('Costs (Millions of dolars/year)'),sortable: true},
                {key: 'windArea',label: this.__('Turbines impacted area (ha)'),sortable: true},
                {key: 'solarArea',label: this.__('Panels impacted area (ha)'),sortable: true},
                {
                    key: 'costOfEnergy',
                    label: this.__('Cost of Energy ($/kWh)'),
                    formatter: (value,key,item) => {
                        return (item.costs/item.energy).toFixed(5)
                    },
                    sortable: true,
                    sortByFormatted: true
                },
                {
                    key: 'weight',
                    label: this.__('Distance to ideal'),
                    formatter: (value,key,item) => {
                        if(this.Ldis != -1){
                            return Math.pow(( 
                                Math.pow((Math.abs(this.idealPoint['energy']-item.energy)/Math.abs(this.idealPoint['energy']-this.nadirPoint['energy']))*this.getRelativesWeights[0],this.Ldis)+
                                Math.pow((Math.abs(this.idealPoint['costs']-item.costs)/Math.abs(this.idealPoint['costs']-this.nadirPoint['costs']))*this.getRelativesWeights[1],this.Ldis)+
                                Math.pow((Math.abs(this.idealPoint['windArea']-item.windArea)/Math.abs(this.idealPoint['windArea']-this.nadirPoint['windArea']))*this.getRelativesWeights[2],this.Ldis)+
                                Math.pow((Math.abs(this.idealPoint['solarArea']-item.solarArea)/Math.abs(this.idealPoint['solarArea']-this.nadirPoint['solarArea']))*this.getRelativesWeights[3],this.Ldis) 
                            ),1.0/this.Ldis).toFixed(2)
                        }else{
                            return Math.max(
                                (Math.abs(this.idealPoint['energy']-item.energy)/Math.abs(this.idealPoint['energy']-this.nadirPoint['energy']))*this.getRelativesWeights[0],
                                (Math.abs(this.idealPoint['costs']-item.costs)/Math.abs(this.idealPoint['costs']-this.nadirPoint['costs']))*this.getRelativesWeights[1],
                                (Math.abs(this.idealPoint['windArea']-item.windArea)/Math.abs(this.idealPoint['windArea']-this.nadirPoint['windArea']))*this.getRelativesWeights[2],
                                (Math.abs(this.idealPoint['solarArea']-item.solarArea)/Math.abs(this.idealPoint['solarArea']-this.nadirPoint['solarArea']))*this.getRelativesWeights[3]
                            ).toFixed(2)
                        }
                    },
                    sortable: true,
                    sortByFormatted: true
                },
                {key: 'actions',label: this.__('Actions') /*, stickyColumn: true*/},
            ],
            fieldsDetails: [
                { key: 'zone', label: this.__('Zone') },{ key: 'type', label: this.__('Type')},{ key: 'model', label: this.__('Model')},{ key: 'number', label: this.__('Number')},{key: 'energy',label: this.__('Energy (GWh/year)'),sortable: true},{key: 'costs',label: this.__('Costs (Millions of dolars/year)'),sortable: true},
            ],
            totalRows: 1,
            mE: 0,
            mC: 0,
            mWA: 0,
            mSA: 0,
            inMap: L.featureGroup([]),
            weights: [5,5,5,5],
            Ldis: 2,
        }
    },
    mounted(){
        this.inMap.addTo(Vue.prototype.$map)
    },
    props: {
        items: Array,
        nadirPoint: Object,
        idealPoint: Object
    },
    methods: {
        
        seeInMap(item){
            this.$root.$emit('show-map-loading');
            this.inMap.clearLayers()
            let zones = Object.keys(item.details)
            for(var i = 0 ; i < zones.length; i++ ){
                if(item["details"][zones[i]].distribution){
                    let gjson = JSON.parse(item["details"][zones[i]].distribution)
                    if(item["details"][zones[i]].type == "eolic"){
                        let layer = L.geoJSON( gjson,{
                            pointToLayer: (feature,latlng)=>{
                                return L.marker(latlng, {
                                    icon: new L.icon({
                                        iconUrl: 'images/icon_eolic.png',
                                        iconSize: [50, 50],
                                        iconAnchor: [25,50]
                                    })
                                })
                            }
                        })
                        this.inMap.addLayer(layer)
                    }
                    if(item["details"][zones[i]].type == "solar"){
                        let layer = L.geoJSON( gjson,{
                            onEachFeature: function(feature,layer){
                                layer.bindPopup(feature.properties.popupContent)
                            },
                            style: function(feature) {
                                return feature.properties.style
                            }
                        })
                        this.inMap.addLayer(layer)
                    }
                }
                    
            }
            setTimeout(() => {
                this.$root.$emit('hide-map-loading')
            }, 0);
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
        },
        isEqual(obj1,obj2){
            let eps = 1e-5
            if( Math.abs(obj1.energy - obj2.energy) > eps ){
                return false
            }
            if( Math.abs(obj1.costs - obj2.costs) > eps ){
                return false
            }
            if( Math.abs(obj1.windArea - obj2.windArea) > eps ){
                return false
            }
            if( Math.abs(obj1.solarArea - obj2.solarArea) > eps ){
                return false
            }
            return true
        }
    },
    computed: {
        getItems(){
            this.mE = this.mC = this.mWA = this.mSA = 0
            let items = []
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
                if(element.energy > 0 ){
                    let belongs = true
                    items.forEach(element2 => {
                        if(this.isEqual(element2,element)){
                            belongs = false
                        }
                    });
                    if(belongs){
                        items.push(element)
                    }
                }
            })
            this.totalRows = items.length
            return items
        },
        getRelativesWeights(){
            let tot = Number(this.weights[0])+Number(this.weights[1])+Number(this.weights[2])+Number(this.weights[3])
            return [ Number(this.weights[0])/tot , Number(this.weights[1])/tot , Number(this.weights[2])/tot , Number(this.weights[3])/tot ]
        }
    }
}
</script>
