<template>
    <div class="custom-actions">
        <button class="btn btn-sm btn-primary" @click="editItemAction('edit-item', rowData, rowIndex)"><i class="glyphicon glyphicon-pencil"></i></button>
        <button class="btn btn-sm btn-danger" @click="deleteItemAction('delete-item', rowData, rowIndex)"><i class="glyphicon glyphicon-trash"></i></button>
    </div>
</template>

<script>
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
        methods: {
            deleteItemAction (action, data, index) {
                // Can be implemented here or by this.$emit('deleteServer', data); https://forum.vuejs.org/t/passing-data-back-to-parent/1201/2
                this.$emit('onDeleteSubWatch', data);
                this.$events.fire('on-delete-sub-watch', data);
            },
            editItemAction (action, data, index) {
                // Can be implemented here or by this.$emit('deleteServer', data); https://forum.vuejs.org/t/passing-data-back-to-parent/1201/2
                this.$emit('onEditSubWatch', data);
                this.$events.fire('on-edit-sub-watch', data);
            }
        }
    }
</script>

<style>
    .custom-actions button.ui.button {
        padding: 8px 8px;
    }

    .custom-actions button.ui.button > i.icon {
        margin: auto !important;
    }
</style>
