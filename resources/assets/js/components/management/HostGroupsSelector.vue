<template>
    <input-tags
            placeholder="Type a group"
            url="/home/management/groups/search"
            :validator="tagValidator"
            :tags="tags"
            :complexTags="true"
            tagAccessor="group_name"
            :tagRemovable="tagRemovable"
            :readOnly="readOnly"
            ref="wrapper"

    >
        <template slot-scope="props">
            <autocomplete
                    v-if="!props.readOnly"
                    v-validate="{max: 128, regex: /^([a-zA-Z0-9_/\-.]+)$/ }"
                    :name="name"
                    data-vv-as="Host group"

                    :placeholder="props.placeholder"
                    :debounce="250"
                    :classes="{ input: 'new-tag' }"
                    :tooltipWidth="subWidth"
                    v-model="props.t.newTag"

                    ref="input_component"
                    anchor="group_name"
                    label=""
                    url="/home/management/groups/search"

                    :process="processGroupAutocomplete"
                    :spaceAsTrigger="true"
                    :customParams="{ noHostGroups: '1' }"
                    @onEnter="props.onAdd"
                    @onTab="props.onAdd"
                    @on188="props.onAdd"
                    @onRight="props.onAdd"
                    @onSpace="props.onAdd"
                    @onSelect="props.addNew"
                    @onDelete="props.onDelete"
            ></autocomplete>
        </template>
    </input-tags>
</template>

<script>
    import _ from 'lodash';
    import Req from 'req';
    import mgmUtil from './util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import InputTags from 'ph4-input-tag';
    import AutoComplete from 'ph4-autocomplete';
    import 'ph4-autocomplete/Autocomplete.css';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('input-tags', InputTags);
    Vue.component('autocomplete', AutoComplete);

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
        },
        data() {
            return {
                clientWidth: 0,
            };
        },
        computed: {
            subWidth(){
                return this.clientWidth + 2;
            }
        },
        methods: {
            hookup(){
                window.addEventListener('resize', this.handleResize);
                this.handleResize();
            },

            handleResize() {
                this.clientWidth = this.$refs.wrapper.clientWidth;
            },

            processGroupAutocomplete(json){
                return json.results;
            },

            tagValidator(tagValue){
                return !this.errors.has('host_group') && !_.startsWith(tagValue, 'host-');
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
            window.removeEventListener('resize', this.handleResize);
        },
    }
</script>

<style>
    .autocomplete-list ul {
        margin-left: -5px;
        margin-top: 1px;
        margin-right: 5px;
        padding-top: 0;
        width: 100%;
        overflow-y: auto;
        max-height: 200px;
    }

    .autocomplete-list ul li{
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }

    .autocomplete-list ul:before{
        display: none !important;
    }

    .autocomplete-list .autocomplete-list-wrap {
        width: 100%;
        padding-right: 20px;
    }
</style>