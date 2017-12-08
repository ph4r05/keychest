<script>
    import Vue from 'vue';
    import _ from 'lodash';

    /*eslint-disable*/
    const validators = {
        email: new RegExp(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/),
        url : new RegExp(/^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i),
        text : new RegExp(/^[a-zA-Z]+$/),
        digits : new RegExp(/^[\d() \.\:\-\+#]+$/),
        isodate : new RegExp(/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/)
    };
    /*eslint-enable*/

    export default {
        inject: ['$validator'],
        name: 'InputTag',

        props: {
            tags: {
                type: Array,
                default: () => []
            },
            placeholder: {
                type: String,
                default: ''
            },
            onChange: {
                type: Function
            },
            readOnly: {
                type: Boolean,
                default: false
            },
            validate: {
                type: String,
                default: ''
            },
            validator: {
                type: Function,
            },
            complexTags: {
                type: Boolean,
                default: false
            },
            tagAccessor: {
                default: 'tag',
            },
            tagRemovable: {
                default: undefined,
            },
            tagFactory: {
                type: Function,
                default: undefined,
            }
        },

        data () {
            return {
                newTag: '',
                bus: undefined,
                clientWidth: 0,
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.bus = new Vue();

                window.addEventListener('resize', this.handleResize);
                this.handleResize();
            });
        },
        beforeDestroy: function () {
            window.removeEventListener('resize', this.handleResize);
        },
        methods: {
            handleResize() {
                this.clientWidth = this.$refs.wrapper.clientWidth;
            },

            focusNewTag () {
                if (this.readOnly) { return }
                this.$el.querySelector('.new-tag').focus()
            },

            createTag(tag){
                if (!this.complexTags){
                    return tag;
                } else if (this.tagFactory){
                    return this.tagFactory(tag);
                } else if (_.isString(tag)) {
                    return _.set({}, this.tagAccessor, tag);
                } else {
                    return tag;
                }
            },

            addNew (tag) {
                if (tag) {
                    tag = this.createTag(tag);

                    const index = !this.complexTags ? this.tags.indexOf(tag) :
                        _.map(this.tags, this.tagValue).indexOf(this.tagValue(tag));

                    if (index === -1 && this.validateIfNeeded(tag)){
                        this.tags.push(tag);
                        this.tagChange();
                    }
                }

                this.newTag = '';
                this.bus.$emit('cleanInput', 1);
            },

            validateIfNeeded (tagValue) {
                if (this.validator){
                    return this.validator(tagValue);
                }
                
                if (this.validate === '' || this.validate === undefined) {
                    return true
                } else if (Object.keys(validators).indexOf(this.validate) > -1) {
                    return validators[this.validate].test(tagValue)
                }
                return true
            },

            remove (index) {
                this.tags.splice(index, 1);
                this.tagChange();
            },

            removeLastTag () {
                if (this.newTag) { return }
                if (this.tags.length > 0 && !this.removableTag(_.last(this.tags))) { return }
                this.tags.pop();
                this.tagChange();
            },

            tagChange () {
                if (this.onChange) {
                    // avoid passing the observer
                    this.onChange(JSON.parse(JSON.stringify(this.tags)));
                }
                this.bus.$emit('tagChange');
            },

            onDelete(e){
                e.stopPropagation();
                this.removeLastTag();
            },

            onAdd(e){
                this.addNew(this.newTag);
            },

            removableTag(tag, index){
                if (!this.tagRemovable || !this.complexTags){
                    return true;
                } else if (_.isFunction(this.tagRemovable)){
                    return this.tagRemovable(tag, index);
                } else {
                    return _.get(tag, this.tagRemovable, true);
                }
            },

            tagValue(tag, index){
                if (!this.tagAccessor || !this.complexTags){
                    return tag;
                } else if (_.isFunction(this.tagAccessor)){
                    return this.tagAccessor(tag, index);
                } else {
                    return _.get(tag, this.tagAccessor);
                }
            },
        }
    }
</script>

<template>

    <div @click="focusNewTag()" v-bind:class="{'read-only': readOnly}" class="vue-input-tag-wrapper" ref="wrapper">
        <slot name="tag"
              :tags="tags"
              :readOnly="readOnly"
              :removeFnc="remove"
        >
            <span v-for="(tag, index) in tags" v-bind:key="index" class="input-tag">
                <template v-if="complexTags">
                    <span>{{ tagValue(tag, index) }}</span>
                    <a v-if="!readOnly && removableTag(tag, index)" @click.prevent.stop="remove(index)" class="remove"></a>
                </template>
                <template v-else="">
                    <span>{{ tag }}</span>
                    <a v-if="!readOnly" @click.prevent.stop="remove(index)" class="remove"></a>
                </template>
            </span>
        </slot>
        <slot :t="this"
              :placeholder="placeholder"
              :readOnly="readOnly"
              :newTag="newTag"
              :bus="bus"
              :clientWidth="clientWidth"
              :removeLastTag="removeLastTag"
              :addNew="addNew"
              :onAdd="onAdd"
              :onDelete="onDelete"
        >
        <input v-if="!readOnly" v-bind:placeholder="placeholder" type="text" class="new-tag"
               v-model="newTag" ref="input_component"
               v-on:keydown.delete.stop="removeLastTag()"
               v-on:keydown.enter.188.tab.prevent.stop="addNew(newTag)"/>
        </slot>
    </div>

</template>

<style>

    .vue-input-tag-wrapper {
        background-color: #fff;
        border: 1px solid #ccc;
        overflow: hidden;
        padding-left: 4px;
        padding-top: 4px;
        cursor: text;
        text-align: left;
        -webkit-appearance: textfield;
    }

    .vue-input-tag-wrapper .input-tag {
        background-color: #cde69c;
        border-radius: 2px;
        border: 1px solid #a5d24a;
        color: #638421;
        display: inline-block;
        font-size: 13px;
        font-weight: 400;
        margin-bottom: 4px;
        margin-right: 4px;
        padding: 3px;
    }

    .vue-input-tag-wrapper .input-tag .remove {
        cursor: pointer;
        font-weight: bold;
        color: #638421;
    }

    .vue-input-tag-wrapper .input-tag .remove:hover {
        text-decoration: none;
    }

    .vue-input-tag-wrapper .input-tag .remove::before {
        content: " x";
    }

    .vue-input-tag-wrapper .new-tag {
        background: transparent;
        border: 0;
        color: #777;
        font-size: 13px;
        font-weight: 400;
        margin-bottom: 6px;
        margin-top: 1px;
        outline: none;
        padding: 4px;
        padding-left: 0;
        width: 100px;
    }

    .vue-input-tag-wrapper.read-only {
        cursor: default;
    }

</style>
