<template>
    <div>
        <!--suppress XmlUnboundNsPrefix -->
        <ph4-vue-select
                label="sol_name"
                placeholder="Type a solution"
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
                :multiple="multiple"
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
        name: 'solution-vue-select',
        inject: ['$validator'],
        components: {
            'ph4-vue-select': Ph4VueSelect
        },
        props: {
            tags: {
                default: () => []
            },
            name: {
                type: String,
                default: 'sol_name'
            },
            readOnly: {
                type: Boolean,
                default: false
            },
            taggable: {
                type: Boolean,
                default: false,
            },
            multiple: {
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
                return /^([a-zA-Z0-9_/\-.:]*)$/;
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
                        events: 'input|blur',
                        vm: vm,
                        getter: () => {
                            return field.value; // vm.$data.search
                        },
                        rules: {
                            max: 128,
                            regex: this.tagRegex
                        },
                        alias: 'Solution name'
                    });
                });
            },

            searchEmpty(){
                return !this.$refs || !this.$refs.vueSelect || !this.$refs.vueSelect.$data.search;
            },

            createOption(newOption) {
                newOption = {['sol_name']: newOption};
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

                axios.get(`/home/management/solutions/search?q=${search}`)
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
                this.options = data.results;
            },

            isTagValid(tagValue){
                return this.tagRegex.test(tagValue);
            },

            tagValidator(tagValue){
                return tagValue
                    && !this.errors.has(this.name)
                    && tagValue.sol_name
                    && this.isTagValid(tagValue.sol_name);
            },

            tagRemovable(tag){
                return !this.readOnly;
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
