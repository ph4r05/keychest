<template>
    <div class="mgmt-add-host row">

        <sbox cssBox="box-success" :headerCollapse="false">
            <template slot="title">Add managed host</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>
            <p>
                here
            </p>
        </sbox>

    </div>
</template>
<script>
    import _ from 'lodash';
    import accounting from 'accounting';
    import moment from 'moment';
    import pluralize from 'pluralize';
    import axios from 'axios';
    import Req from 'req';
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    Vue.use(VueEvents);

    export default {
        components: {

        },
        data () {
            return {
                numSelected: 0
            }
        },

        computed: {
        },

        methods: {
            back() {
                this.$router.back();
            },
        },
        events: {

        }
    }
</script>
<style>

</style>
