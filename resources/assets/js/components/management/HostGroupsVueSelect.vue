<template>
    <div>
        <!--suppress XmlUnboundNsPrefix -->
        <ph4-vue-select
                label="group_name"
                placeholder="Type a host group"
                ref="vueSelect"

                :inputName="name"
                :value="tags"
                :options="options"
                :searchable="!readOnly"
                :taggable="!readOnly && taggable"
                :pushTags="!readOnly"
                :filterable="!readOnly"
                :shouldSpaceTrigger="!readOnly"
                :shouldTabTrigger="!readOnly"
                :shouldCommaTrigger="!readOnly"
                :noDrop="readOnly"
                :multiple="true"
                :refocus="true"

                :createOption="createOption"
                :on-search="search"
                :isOptionRemovable="tagRemovable"
                :isOptionAllowed="tagValidator"

        >
            <template slot="no-options">
                <template v-if="searchEmpty()">
                    Start searching or <a @click.prevent.stop="reloadEmptySearch" style="display: inline;">click here</a>
                </template>
                <template v-else="">
                    Sorry, no matching options. <a @click.prevent.stop="reloadEmptySearch" style="display: inline;">Try reload</a>
                </template>
            </template>
        </ph4-vue-select>
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

    export default {
        name: "host-groups-vue-selector",
        inject: ['$validator'],
        components: {
            'ph4-vue-select': Ph4VueSelect
        },
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
            },
            allowHostGroups: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                options: [],
                searchStr: '',
            };
        },
        computed: {
            tagRegex(){
                return this.allowHostGroups ?
                    /^([a-zA-Z0-9_/\-.]*)$/ :
                    /^((?!host-)([a-zA-Z0-9_/\-.]*))$/;
            },
        },
        methods: {
            hookup(){
                this.fetchData('', ()=>{}, this);

                if (this.$validator){
                    this.attachValidator();
                }
            },

            attachValidator(){
                this.$refs.vueSelect.$validator = this.$validator;
                this.$refs.vueSelect.visitSearch((vm, field) => {
                    vm.$validator.attach({
                        name: this.name,
                        el: field,
                        events: 'input',
                        vm: vm,
                        getter: () => {
                            return field.value; // vm.$data.search
                        },
                        rules: {
                            max: 128,
                            regex: this.tagRegex
                        },
                        alias: 'Host group'
                    });
                });
            },

            searchEmpty(){
                return !this.$refs || !this.$refs.vueSelect || !this.$refs.vueSelect.$data.search;
            },

            createOption(newOption) {
                newOption = {['group_name']: newOption};
                this.$emit('option:created', newOption);

                return newOption;
            },

            reloadEmptySearch(){
                this.search('', () => {
                    this.$nextTick(() => {
                        this.$refs.vueSelect.$refs.search.focus();
                    });
                });
            },

            search(search, loading) {
                this.searchStr = search;
                loading(true);
                this.fetchData(search, loading, this);
            },

            fetchData: _.debounce((search, loading, vm) => {
                if (search && !vm.isTagValid(search)){
                    loading(false);
                    return;
                }

                axios.get(`/home/management/groups/search?q=${search}&noHostGroups=${Number(!vm.allowHostGroups)}`)
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

            isTagValid(tagValue){
                return this.tagRegex.test(tagValue);
            },

            tagValidator(tagValue){
                return tagValue
                    && !this.errors.has(this.name)
                    && tagValue.group_name
                    && this.isTagValid(tagValue.group_name);
            },

            tagRemovable(tag){
                if (this.readOnly){
                    return false;
                }

                return this.allowHostGroups ? true : !_.startsWith(tag.group_name, 'host-');
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
