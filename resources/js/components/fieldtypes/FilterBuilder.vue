<template>

    <div>

        <loading-graphic v-if="loading" :text="false" />

        <div v-if="!loading">

            <div class="">
                <filter-item
                    v-for="filter, index in value"
                    :filter="filter"
                    :field="fieldsObject[filter.handle]"
                    :values="filter.values"
                    :fields="filterFields[filter.type][filter.handle]"
                    :meta="filterMeta(filter.id)"
                    :field-path-prefix="filterPath(index)"
                    :read-only="isReadOnly"
                    :parent-name="name"
                    :index="index"
                    :collapsed="collapsed.includes(filter.id)"
                    @collapsed="collapseFilter(filter.id)"
                    @expanded="expandFilter(filter.id)"
                    @updated="updateFilter(index, $event)"
                    @meta-updated="updateFilterMeta(filter.id, $event)"
                    @removed="removeFilter(index)"
                    />
            </div>

            <div class="flex">
                <popover ref="addFilterDropdowm" class="dropdown-list filter_builder-dropdown" placement="bottom-start">
                    <template #trigger>
                        <button class="btn">
                            {{ __('statamic-filter-builder::fieldtypes.filter_builder.add_filter') }}
                        </button>
                    </template>
                    <button v-for="field in meta.fields" v-text="field.display" @click="addFilter('field', field.handle)" />
                </popover>
            </div>

        </div>

    </div>

</template>

<script>
import FilterItem from './FilterItem.vue';
const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],

    components: {
        FilterItem,
    },
    
    inject: ['storeName'],

    data() {
        return {
            collapsed: this.value.map(fitler => fitler.id),
        };
    },

    computed: {

        fieldsObject() {
            return Object.fromEntries(this.meta.fields.map(field => ([
                field.handle,
                field,
            ])));
        },

        filterFields() {
            return {
                field: Object.fromEntries(this.meta.fields.map(field => ([
                    field.handle,
                    field.fields,
                ]))),
            };
        },

    },

    methods: {

        addFilter(type, handle) {
            const id = uniqid();
            this.$refs.addFilterDropdowm.close();
            this.update([
                ...this.value,
                { id, type, handle, values: this.meta.defaults[handle] },
            ]);
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [id]: this.meta.new[handle],
                },
            });
        },

        updateFilter(index, values) {
            this.update([
                ...this.value.slice(0, index),
                { ...this.value[index], values },
                ...this.value.slice(index + 1),
            ]);
        },

        updateFilterMeta(id, meta) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [id]: meta,
                },
            });
        },

        removeFilter(index) {
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1),
            ]);
        },

        collapseFilter(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id);
            }
        },

        expandFilter(id) {
            if (this.collapsed.includes(id)) {
                var index = this.collapsed.indexOf(id);
                this.collapsed.splice(index, 1);
            }
        },

        filterMeta(id) {
            return this.meta.existing[id];
        },

        filterPath(index) {
            return `${this.handle}.${index}`;
        },

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