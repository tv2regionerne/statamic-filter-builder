<template>

    <div>

        <div class="">
            <sort-item
                v-for="sort, index in value"
                :sort="sort"
                :field="fieldsObject[sort.handle]"
                :values="sort.values"
                :fields="itemFields[sort.type][sort.handle]"
                :meta="itemMeta(sort.id)"
                :previews="itemPreviews(sort.id)"
                :field-path-prefix="itemPath(index)"
                :read-only="isReadOnly"
                :parent-name="name"
                :index="index"
                :collapsed="collapsed.includes(sort.id)"
                @collapsed="collapseItem(sort.id)"
                @expanded="expandItem(sort.id)"
                @updated="updateItem(index, $event)"
                @meta-updated="updateItemMeta(sort.id, $event)"
                @removed="removeItem(index)"
                @previews-updated="updateItemPreviews(sort.id, $event)"
                />
        </div>

        <div class="flex">
            <v-select
                :append-to-body="true"
                class="w-52"
                :placeholder="__('statamic-filter-builder::fieldtypes.sort_builder.add_sort')"
                :options="fieldsOptions"
                :reduce="option => option.value"
                :value="null"
                @input="addItem('field', $event)"
            />
        </div>

    </div>

</template>

<script>
import SortItem from './SortItem.vue';
import UsesFields from './Mixins/UsesFields.vue';

export default {

    mixins: [
        Fieldtype,
        UsesFields,
    ],

    components: {
        SortItem,
    },

};
</script>
<style>
.sort_builder-fieldtype {
    .replicator-set-body {
        padding: 0.5rem !important;
    }
    .form-group {
        padding: 0.5rem !important;
    }
    .\@lg\:w-1\/4 {
        width: 20% !important;
    }
    .\@lg\:w-1\/2 {
        width: 80% !important;
    }
    .\@lg\:w-1\/2 + .\@lg\:w-1\/2 {
        margin-left: 20% !important;
    }
}
.sort_builder-dropdown {
    .popover-content {
        max-height: 15.6rem;
        overflow: auto;
    }
}
</style>