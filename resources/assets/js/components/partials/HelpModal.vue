<template>
    <div class="modal fade" v-bind:id="id" tabindex="-1" role="dialog"
         v-bind:aria-labelledby="title" aria-hidden="true"
         v-bind:class="cssBox">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <slot name="title">{{ title }}</slot>
                    </h3>
                </div>

                <div class="modal-body">
                    <slot></slot>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            id: {
                required: true,
                type: String
            },

            title: {
                required: false,
                type: String,
                default: ''
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
