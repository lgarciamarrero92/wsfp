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
            <div>
                <b-spinner variant="primary"></b-spinner>
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
            }
        })
        this.$root.$on('coefficients-progress',(val)=>{
            this.prog = val/2
        })
        this.$root.$on('generation-progress',(val)=>{
            this.prog = 0.5+(val.progress/2)
        })
    }
}
</script>