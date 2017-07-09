<template>
    <span class="helpsub" data-toggle="modal" v-bind:data-target="cid">
        <sup><i class="fa fa-question-circle"></i></sup>
    </span>
</template>

<script>
    export default {
        props: {
            id: {
                required: true,
                type: String
            }
        },

        data: function() {
            return {};
        },

        computed: {
            cid(){
                return '#' + this.id;
            }
        }
    }
</script>
