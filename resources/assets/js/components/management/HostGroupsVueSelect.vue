<template>
    <div>
        <ph4-vue-select
                label="group_name"
                placeholder="Type a host group"

                :value="tags"
                :options="options"
                :searchable="!readOnly"
                :taggable="!readOnly && taggable"
                :pushTags="!readOnly"
                :filterable="!readOnly"
                :shouldSpaceTrigger="!readOnly"
                :shouldTabTrigger="!readOnly"
                :shouldCommaTrigger="!readOnly"
                :disabled="readOnly"
                :multiple="true"
                :refocus="true"

                :createOption="createOption"
                :on-search="search"
                :isOptionRemovable="tagRemovable"
                :isOptionAllowed="tagValidator"

        ></ph4-vue-select>
    </div>
</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import Req from 'req';
    import mgmUtil from './util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import Ph4VueSelect from 'ph4-vue-select';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('ph4-vue-select', Ph4VueSelect);

    export default {
        name: "host-groups-selector",
        inject: ['$validator'],
        props: {
            tags: {
                type: Array,
                default: () => []
            },
            name: {
                type: String,
                default: 'host_group'
            },
            readOnly: {
                type: Boolean,
                default: false
            },
            taggable: {
                type: Boolean,
                default: true,
            }
        },
        data() {
            return {
                options: [],
            };
        },
        computed: {

        },
        methods: {
            hookup(){
                this.fetchGroups('', ()=>{}, this);
            },

            createOption(newOption) {
                newOption = {['group_name']: newOption};
                this.$emit('option:created', newOption);

                return newOption;
            },

            search(search, loading) {
                loading(true);
                this.fetchGroups(search, loading, this);
            },

            fetchGroups: _.debounce((search, loading, vm) => {
                axios.get(`/home/management/groups/search?q=${search}&noHostGroups=1`)
                    .then(response => {
                        vm.onLoaded(response.data);
                    })
                    .catch(e => {
                        console.warn(e);
                    })
                    .finally(()=>{
                        loading(false);
                    });
            }, 250),

            onLoaded(data){
                this.options = mgmUtil.sortHostGroups(data.results);
            },

            tagValidator(tagValue){
                return tagValue && !this.errors.has('host_group') && !_.startsWith(tagValue.group_name, 'host-');
            },

            tagRemovable(tag){
                return !_.startsWith(tag.group_name, 'host-');
            },
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        },
        beforeDestroy: function () {

        },
    }
</script>

<style>

</style>
