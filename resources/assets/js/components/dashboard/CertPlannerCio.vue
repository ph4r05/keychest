<template>
    <div>
        <div class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-success" :headerCollapse="true">
                    <template slot="title">Operational status - 12 months</template>
                    <p>
                        A chart of the operational status of business functions,
                        and destinations.
                        <br>
                        <i>Note: you can click an chart labels to hide/unhide information.</i>
                    </p>
                    <div class="form-group">
                        <canvas ref="chart_certs_tls" style="width:100%; height: 350px"></canvas>
                    </div>
                </sbox>
            </div>
        </div>

        <div class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-success" :headerCollapse="true">
                    <template slot="title">Certificate expiration levels - 12 months</template>
                    <p>A chart of the level of expired certificates over the last 12 months.
                        <br>
                        <i>Note: you can click an chart labels to hide/unhide information.</i></p>
                    <div class="form-group">
                        <canvas ref="chart_certs_all" style="width:100%; height: 350px"></canvas>
                    </div>
                </sbox>
            </div>
        </div>
    </div>
</template>

<script>
    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import CertPlanner from './CertPlanner';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        extends: CertPlanner,
        name: 'cert-planner-cio',
    }
</script>

<style scoped>

</style>
