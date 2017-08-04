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
            },
            checkAll(){
                let self = this;
                let idColumn = this.trackBy;

                this.tableData.forEach(function(dataItem) {
                    const id = dataItem[idColumn];
                    self.toggleCheckbox(dataItem, 'fld', {target:{checked: true}});
                });
            },
            uncheckAll(){
                let self = this;
                let idColumn = this.trackBy;

                this.tableData.forEach(function(dataItem) {
                    const id = dataItem[idColumn];
                    self.toggleCheckbox(dataItem, 'fld', {target:{checked: false}});
                });
            },
            uncheckAllPages(){
                this.selectedTo = [];
                this.$emit('vuetable:checkbox-toggled-all', false);
            }
        }
    }
</script>
