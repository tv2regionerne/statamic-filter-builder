<template>

    <div>

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
            <v-select
                :append-to-body="true"
                class="w-52"
                :placeholder="__('statamic-filter-builder::fieldtypes.filter_builder.add_filter')"
                :options="fieldsOptions"
                :reduce="option => option.value"
                :value="null"
                @input="addFilter('field', $event)"
            />
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

        fieldsOptions() {
            return this.meta.fields.map(field => ({
                value: field.handle,
                label: field.display,
            }));
        },

        filterFields() {
            return {
                field: Object.fromEntries(this.meta.fields.map(field => ([
                    field.handle,
                    field.fields,
                ]))),
            };
        },

        mode() {
            return this.config.mode || 'config';
        },
        
        collections() {
            const store = this.$store.state.publish[this.storeName];
            const prefix = this.fieldPathPrefix || '';
            const key = prefix.slice(0, -this.handle.length) + this.config.field;
            return data_get(store.values, key);
        },

    },

    methods: {

        loadCollectionsMeta(collections) {
            const params = {
                config: utf8btoa(JSON.stringify({
                    ...this.config,
                    mode: 'config',
                    collections: collections,
                 })),
            };

            this.$axios.get(cp_url('fields/field-meta'), { params }).then(response => {
                this.meta = response.data.meta;
                this.value = response.data.value;
            });
        },

        addFilter(type, handle) {
            const id = uniqid();
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

    watch: {
        collections: function (collections, oldCollections) {
            if (JSON.stringify(collections) === JSON.stringify(oldCollections)) {
                return;                
            }
            this.update([]);
            this.updateMeta({
                ...this.meta,
                existing: {},
            });
            this.loadCollectionsMeta(collections);
        }
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