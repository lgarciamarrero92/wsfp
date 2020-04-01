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
        <!--b-progress :value="prog" :max="1000" :key="key" animated> </b-progress-->
    </b-modal>
    
</div>
</template>
<script>

export default {
    data() {
        return {
            prog: this.progress,
            key: 1
        };
    },
    props: {
        progress: Number
    },
    methods: {

    },
    watch:{
        
    },
    computed: {
       
    },
    mounted(){
        this.$root.$on('bv::modal::shown',(bvEvent,modalId)=>{
            if(modalId == 'busy'){
                this.prog = 0
            }
        })
    }
}
</script>