// flow

module.exports = {
    watch: {

    },

    methods: {
        onKeyComma(e){
            return !this.shouldCommaTrigger ? null : this.onKeyTrigger(e);
        },

        onKeyTab(e){
            return !this.shouldTabTrigger ? null : this.onKeyTrigger(e);
        },

        onKeySpace(e){
            return !this.shouldSpaceTrigger ? null : this.onKeyTrigger(e);
        },

        onKeyTrigger(e){
            e.preventDefault();
            this.typeAheadSelect();
        },

    }
};
