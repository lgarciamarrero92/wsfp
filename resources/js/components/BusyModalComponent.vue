<template>
<div>
    <b-modal 
        id="busy" 
        centered 
        title="We are busy"
        no-close-on-esc
        no-close-on-backdrop
        body-bg-variant="ligth"
        hide-header
        hide-footer
        hide-header-close
        ok-disabled
        :static="true"
        :lazy="true"
        cancel-disabled
        size="sm"
    >
        <div class = "d-flex justify-content-center align-items-center" style = "height: 150px;">
            <div class="d-flex flex-column align-items-center">
                <b-spinner variant="primary"></b-spinner>
                <strong class="pulseText">{{message}}</strong>
            </div>
        </div>
        <b-progress :value="prog" :max="1" animated show-progress> </b-progress>
    </b-modal>
    
</div>
</template>
<script>

export default {
    data() {
        return {
            prog: 0,
            message: ""
        };
    },
    props: {
    },
    methods: {

    },
    watch:{
        
    },
    computed: {
       
    },
    mounted(){
        this.$root.$on('bv::modal::show',(bvEvent,modalId)=>{
            if(modalId == 'busy'){
                this.prog = 0
                this.message = "Starting..."
            }
        })
        this.$root.$on('coefficients-progress',(val)=>{
            this.prog = val
            this.message = "Making model..."
        })
        this.$root.$on('generation-progress',(val)=>{
            this.message = "Optimizing..."
            this.prog = val.progress
        })
    }
}
</script>
<style>
@keyframes textColorChange {
    0% {color: #3490dc;}
    50% {color: #38c172;}
    100% {color: #3490dc;}
}
/* Use @-webkit-keyframes for Safari/Chrome */

.pulseText {
    animation: textColorChange 2s infinite;
}
</style>