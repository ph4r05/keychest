<template>
    <div class="box" v-bind:class="cssBox">
        <div class="box-header with-border">
            <h3 class="box-title"><slot name="title"></slot></h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                <button v-if="remove" type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
            </div>
        </div>

        <div class="box-body">
            <slot></slot>
        </div>
        <!-- /.box-body -->
    </div>
</template>

<script>
    export default {
        props: {
            remove: {
                type: Boolean,
                required: false,
                default: false,
            },

            cssBox: {
                required: false
            }
        },

        data: function() {
            return {};
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },

        computed: {
        },

        methods: {
            hookup(){

            }
        }
    }
</script>

