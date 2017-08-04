<script>
    export default {
        props: {
        },
        data: function() {
            return {
            }
        },
        computed: {
        },
        methods: {
            invertCheckBoxes(){
                let self = this;
                let idColumn = this.trackBy;

                this.tableData.forEach(function(dataItem) {
                    const id = dataItem[idColumn];
                    self.toggleCheckbox(dataItem, 'fld', {target:{checked: !self.isSelectedRow(id)}});
                });

                this.$emit('vuetable:checkbox-inverted');
            }
        }
    }
</script>
