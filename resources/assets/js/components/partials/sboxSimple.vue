<template>
    <div class="box" v-bind:class="[auxCss, cssBox]">
        <div class="box-header with-border">
            <h3 class="box-title" :data-widget="headerWidget"><slot name="title"/></h3>

            <div class="box-tools pull-right">
                <slot name="widgets"/>
                <button v-if="headerCollapse" type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i v-if="collapsed" class="fa fa-plus"></i>
                    <i v-else="" class="fa fa-minus"></i>
                </button>
                <button v-if="remove" type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <div class="box-body">
            <slot/>
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
            collapsed: {
                type: Boolean,
                required:false,
                default:false,
            },

            cssBox: {
                required: false
            },
            headerCollapse: {
                type: Boolean,
                required: false,
                default: true,
            },
        },

        computed: {
            auxCss(){
                return {
                    'collapsed-box': this.collapsed
                };
            },
            headerWidget(){
                return this.headerCollapse ? 'collapse' : '';
            }
        },
    }
</script>

