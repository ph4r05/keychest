<template>
    <div @click="onClick">
        <div class="table-responsive" ref="tbl">
            <table class="table table-condensed">
                <tbody>
                    <tr>
                        <th class="col-md-2">Host name</th>
                        <td class="col-md-10">{{ rowData.host_name }}</td>
                    </tr>
                    <tr>
                        <th>Host address</th>
                        <td>{{ rowData.host_addr }}:{{ rowData.ssh_port }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="config-host alert alert-info-2" v-if="rowData.ssh_key" ref="ssh_key">
            <span class="code-block ssh-key">{{ rowData.ssh_key.pub_key }}</span>
        </div>

    </div>
</template>

<script>
    import _ from 'lodash';

    export default {
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data(){
            return {

            };
        },
        methods: {
            hookup(){
                if (this.rowData.ssh_key){
                    const newWidth = this.$refs.tbl.clientWidth;
                    this.$refs.ssh_key.style.width = newWidth+'px';
                    this.$refs.ssh_key.style.maxWidth = 'inherit';
                }
            },
            onClick (event) {
                console.log('my-detail-row: on-click', event.target)
            },
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        }
    }
</script>
<style scoped>
    .config-host .ssh-key {
        word-wrap: break-word !important;
    }

    @media (min-width: 2000px){
        .config-host {
            max-width: 1600px;
        }
    }

    @media (min-width: 1440px) and (max-width: 2000px){
        .config-host {
            max-width: 1300px;
        }
    }

    @media (min-width: 960px) and (max-width: 1440px){
        .config-host {
            max-width: 900px;
        }
    }

    @media (min-width: 769px) and (max-width: 960px){
        .config-host {
            max-width: 750px;
        }
    }

    @media (min-width: 481px) and (max-width: 768px){
        .config-host {
            max-width: 400px;
        }
    }

    @media (max-width: 480px) {
        .config-host {
            max-width: 400px;
        }
    }

</style>

