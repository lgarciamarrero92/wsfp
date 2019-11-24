<template>
    <b-modal 
        id="modal-center" 
        centered 
        title="Set details"
        ok-title="Save"
        no-close-on-esc
        no-close-on-backdrop
        and hide-header-close
        @ok="onSave"
    >
    <p class="my-4">{{type}}</p>
  </b-modal>
</template>
<script>
export default {
    data() {
        return {
        };
    },
    props: {
        type: String
    },
    methods: {
        onSave: function(){
            console.log('saving')
        }
    }
}
</script>