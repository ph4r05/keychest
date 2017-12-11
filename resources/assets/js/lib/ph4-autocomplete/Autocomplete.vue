<template>
    <div :class="`${getClassName('wrapper')} autocomplete-wrapper`">
        <input
                ref="input"
                type="text"
                :id="id"
                :class="`${getClassName('input')} autocomplete-input`"
                :placeholder="placeholder"
                :name="name"
                v-model="type"
                @input="handleInput"
                @dblclick="handleDoubleClick"
                @blur="handleBlur"
                @keydown="handleKeyDown"
                @focus="handleFocus"
                autocomplete="off"
        />

        <div
                :class="`${getClassName('list')} autocomplete autocomplete-list`"
                v-show="showList && json.length"
        >
            <div class="autocomplete-list-wrap" ref="listWrapper" :style="listWrapperStyle">
            <ul>
                <li
                        v-for="(data, i) in json"
                        :class="activeClass(i)"
                >
                    <a
                            href="#"
                            @click.prevent="selectList(data)"
                            @mousemove="mousemove(i)"
                    >
                        <div v-if="onShouldRenderChild" v-html="onShouldRenderChild(data)"></div>
                        <div v-if="!onShouldRenderChild">
                            <b class="autocomplete-anchor-text">{{ deepValue(data, anchor) }}</b>
                            <span class="autocomplete-anchor-label">{{ deepValue(data, label) }}</span>
                        </div>
                    </a>
                </li>
            </ul>
            </div>

        </div>

    </div>
</template>


<script>
    import _ from 'lodash';

    /*! Copyright (c) 2016 Naufal Rabbani (http://github.com/BosNaufal)
    * Licensed Under MIT (http://opensource.org/licenses/MIT)
    *
    * Vue 2 Autocomplete @ Version 0.0.1
    *
    */
    export default {
        inject: ['$validator'],
        props: {
            id: String,
            name: String,
            className: String,
            classes: {
                type: Object,
                default: () => ({
                    wrapper: false,
                    input: false,
                    list: false,
                    item: false,
                })
            },

            //event bus
            bus: Object,

            // v-model
            value: String,

            placeholder: String,
            required: Boolean,
            // Intial Value
            initValue: {
                type: String,
                default: ""
            },
            // Manual List
            options: Array,
            // Filter After Get the data
            filterByAnchor: {
                type: Boolean,
                default: true,
            },
            // Anchor of list
            anchor: {
                type: String,
                required: true
            },
            // Label of list
            label: {
                type: String,
                default: ''
            },
            // Debounce time
            debounce: Number,
            // ajax URL will be fetched
            url: {
                type: String,
                required: true
            },
            // query param
            param: {
                type: String,
                default: 'q'
            },
            encodeParams: {
                type: Boolean,
                default: true,
            },
            // Custom Params
            customParams: Object,
            // Custom Headers
            customHeaders: Object,
            // minimum length
            min: {
                type: Number,
                default: 0
            },
            // space cannot be entered, triggers event
            spaceAsTrigger: {
                type: Boolean,
                default: false
            },
            // width of the tooltip bar, optional
            tooltipWidth: {
                type: Number,
            },
            // Create a custom template from data.
            onShouldRenderChild: Function,
            // Process the result before retrieveng the result array.
            process: Function,
            // Callback
            onInput: Function,
            onShow: Function,
            onBlur: Function,
            onHide: Function,
            onFocus: Function,
            onSelect: Function,
            onBeforeAjax: Function,
            onAjaxProgress: Function,
            onAjaxLoaded: Function,
            onShouldGetData: Function,
            onEnter: Function,
            onTab: Function,
            on188: Function,
            onDelete: Function,
            onRight: Function,
        },
        data() {
            return {
                showList: false,
                type: "",
                json: [],
                focusList: "",
                debounceTask: undefined,
            };
        },
        computed: {
            listWrapperStyle(){
                return !this.tooltipWidth ? {} : {
                    width: this.tooltipWidth + 'px',
                    paddingRight: '0px',
                };
            }
        },
        watch: {
            options(newVal, oldVal) {
                if (this.filterByAnchor) {
                    const { type, anchor } = this;
                    const regex = new RegExp(`${type}`, 'i');
                    const filtered = newVal.filter((item) => {
                        const found = item[anchor].search(regex) !== -1;
                        return found
                    });
                    this.json = filtered;
                } else {
                    this.json = newVal;
                }
            },
            value(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.type = newVal;
                }
            }
        },
        methods: {
            getClassName(part) {
                const { classes, className } = this;
                if (classes[part]) return `${classes[part]}`;
                return className ? `${className}-${part}` : ''
            },
            // Netralize Autocomplete
            clearInput() {
                this.showList = false;
                this.type = "";
                this.json = [];
                this.focusList = "";
                this.$emit('input', this.type);
            },
            // Get the original data
            cleanUp(data){
                return JSON.parse(JSON.stringify(data));
            },
            /*==============================
              INPUT EVENTS
            =============================*/
            handleInput(e){
                const { value } = e.target;
                this.showList = true;
                this.$emit('input', this.type);
                // Callback Event
                if(this.onInput) this.onInput(value);
                // If Debounce
                if (this.debounce) {
                    if (this.debounceTask !== undefined) clearTimeout(this.debounceTask);
                    this.debounceTask = setTimeout(() => {
                        return this.getData(value);
                    }, this.debounce)
                } else {
                    return this.getData(value);
                }
            },
            handleKeyDown(e){
                const key = e.keyCode;
                const DOWN = 40;
                const UP = 38;
                const RIGHT = 39;
                const ENTER = 13;
                const TAB = 9;
                const DELETE = 8;
                const ESC = 27;
                const SPACE = 32;
                let signalChange = true;
                // Prevent Default for Prevent Cursor Move & Form Submit
                switch (key) {
                    case DOWN:
                        e.preventDefault();
                        this.focusList++;
                        break;

                    case UP:
                        e.preventDefault();
                        this.focusList--;
                        break;

                    case ENTER:
                        e.preventDefault();
                        if (this.showList && this.json && this.focusList in this.json) {
                            this.selectList(this.json[this.focusList]);
                            this.showList = false;
                        }
                        this.onEnter ? this.onEnter(e) : null;
                        this.$emit('onEnter', e);
                        signalChange = false;
                        break;

                    case ESC:
                        this.showList = false;
                        break;

                    case RIGHT:
                        //e.preventDefault()
                        this.onRight ? this.onRight(e) : null;
                        this.$emit('onRight', e);
                        signalChange = false;
                        break;

                    case TAB:
                        e.preventDefault();
                        if (_.isEmpty(this.type)){
                            this.listLoadTrigger();
                        }

                        this.onTab ? this.onTab(e) : null;
                        this.$emit('onTab', e);
                        signalChange = false;
                        break;

                    case DELETE:
                        this.onDelete ? this.onDelete(e) : null;
                        this.$emit('onDelete', e);
                        break;

                    case SPACE:
                        if (this.spaceAsTrigger){
                            e.preventDefault();
                            if (_.isEmpty(this.type)){
                                this.listLoadTrigger();
                            }

                            this.$emit('onSpace', e);
                            signalChange = false;
                        }
                        break;
                    case 188:
                        e.preventDefault();
                        this.on188 ? this.on188(e) : null;
                        this.$emit('on188', e);
                        signalChange = false;
                        break;
                }

                if (signalChange) {
                    this.$emit('input', this.type);
                }

                const listLength = this.json.length - 1;
                const outOfRangeBottom = this.focusList > listLength;
                const outOfRangeTop = this.focusList < 0;
                const topItemIndex = 0;
                const bottomItemIndex = listLength;
                let nextFocusList = this.focusList;
                if (outOfRangeBottom) nextFocusList = topItemIndex;
                if (outOfRangeTop) nextFocusList = bottomItemIndex;
                this.focusList = nextFocusList;
            },
            setValue(val) {
                this.type = val;
                this.$emit('input', this.type);
            },
            listLoadTrigger(){
                this.json = [];
                this.getData("");
                // Callback Event
                this.onShow ? this.onShow() : null;
                this.showList = true;
            },
            /*==============================
              LIST EVENTS
            =============================*/
            handleDoubleClick(){
                this.listLoadTrigger();
            },
            handleBlur(e){
                // Callback Event
                this.onBlur ? this.onBlur(e) : null;
                setTimeout(() => {
                    // Callback Event
                    this.onHide ? this.onHide() : null;
                    this.showList = false;
                },250);
            },
            handleFocus(e){
                this.focusList = 0;
                // Callback Event
                this.onFocus ? this.onFocus(e) : null
            },
            mousemove(i){
                this.focusList = i;
            },
            activeClass(i){
                const focusClass = i === this.focusList ? 'focus-list' : '';
                return `${focusClass}`
            },
            selectList(data){
                // Deep clone of the original object
                const clean = this.cleanUp(data);
                // Put the selected data to type (model)
                this.type = clean[this.anchor];
                this.$emit('input', this.type);
                // Hide List
                this.showList = false;
                // Callback Event
                this.$emit('onSelect', clean);
                this.onSelect ? this.onSelect(clean) : null
            },
            deepValue(obj, path) {
                const arrayPath = path.split('.');
                for (let i = 0; i < arrayPath.length; i++) {
                    obj = obj[arrayPath[i]];
                }
                return obj;
            },
            /*==============================
              AJAX EVENTS
            =============================*/
            composeParams(val) {
                const encode = (val) => this.encodeParams ? encodeURIComponent(val) : val;
                let params = `${this.param}=${encode(val)}`;
                if(this.customParams) {
                    Object.keys(this.customParams).forEach((key) => {
                        params += `&${key}=${encode(this.customParams[key])}`;
                    })
                }
                return params
            },
            composeHeader(ajax) {
                if(this.customHeaders) {
                    Object.keys(this.customHeaders).forEach((key) => {
                        ajax.setRequestHeader(key, this.customHeaders[key])
                    })
                }
            },
            doAjax(val) {
                // Callback Event
                this.onBeforeAjax ? this.onBeforeAjax(val) : null;
                // Compose Params
                let params = this.composeParams(val);
                // Init Ajax
                let ajax = new XMLHttpRequest();
                ajax.open('GET', `${this.url}?${params}`, true);
                this.composeHeader(ajax);
                // Callback Event
                ajax.addEventListener('progress', (data) => {
                    if(data.lengthComputable && this.onAjaxProgress) this.onAjaxProgress(data)
                });
                // On Done
                ajax.addEventListener('loadend', (e) => {
                    const { responseText } = e.target;
                    let json = JSON.parse(responseText);
                    // Callback Event
                    this.onAjaxLoaded ? this.onAjaxLoaded(json) : null;
                    this.json = this.process ? this.process(json) : json;
                });
                // Send Ajax
                ajax.send();
            },
            getData(value){
                if (value.length < this.min || !this.url) return;
                if (this.onShouldGetData) this.manualGetData(value);
                else this.doAjax(value)
            },
            // Do Ajax Manually, so user can do whatever he want
            manualGetData(val) {
                const task = this.onShouldGetData(val);
                if (task && task.then) {
                    return task.then((options) => {
                        this.json = options
                    })
                }
            },
        },
        created(){
            // Sync parent model with initValue Props
            this.type = this.initValue ? this.initValue : (this.value ? this.value : null);
        },
        mounted() {
            if (this.required) this.$refs.input.setAttribute("required", this.required);
            if (this.bus){
                this.bus.$on('cleanInput', this.clearInput);
            }
        }
    }
</script>
