<template>

    <div>

        <div class="">
            <filter-item
                v-for="filter, index in value"
                :filter="filter"
                :field="fieldsObject[filter.handle]"
                :values="filter.values"
                :fields="itemFields[filter.type][filter.handle]"
                :meta="itemMeta(filter.id)"
                :previews="itemPreviews(filter.id)"
                :field-path-prefix="itemPath(index)"
                :read-only="isReadOnly"
                :parent-name="name"
                :index="index"
                :collapsed="collapsed.includes(filter.id)"
                @collapsed="collapseItem(filter.id)"
                @expanded="expandItem(filter.id)"
                @updated="updateItem(index, $event)"
                @meta-updated="updateItemMeta(filter.id, $event)"
                @removed="removeItem(index)"
                @previews-updated="updateItemPreviews(filter.id, $event)"
                />
        </div>

        <div class="flex">
            <v-select
                :append-to-body="true"
                class="w-52"
                :placeholder="__('statamic-filter-builder::fieldtypes.filter_builder.add_filter')"
                :options="fieldsOptions"
                :reduce="option => option.value"
                :value="null"
                @input="addItem('field', $event)"
            />
        </div>

    </div>

</template>

<script>
import FilterItem from './FilterItem.vue';
import UsesFields from './Mixins/UsesFields.vue';

export default {

    mixins: [
        Fieldtype,
        UsesFields,
    ],

    components: {
        FilterItem,
    },

};
</script>
<style>
.filter_builder-fieldtype {
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
.filter_builder-dropdown {
    .popover-content {
        max-height: 15.6rem;
        overflow: auto;
    }
}
</style>