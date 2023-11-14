<template>

    <div>

        <loading-graphic v-if="loading" :text="false" />

        <div v-if="!loading">

            <div class="">
                <div v-for="filter, index in value" class="replicator-set shadow-sm mb-2 rounded border">
                    <div class="replicator-set-header">
                        <div class="py-2 pl-2 replicator-set-header-inner flex justify-between items-end w-full">
                            <div class="text-sm leading-none">
                                {{ filterlabel(filter) }}
                            </div>
                            <button class="flex self-end group items-center" @click="removeFilter(index)" :aria-label="__('Delete Filter')">
                                <svg-icon name="micro/trash" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
                            </button>
                        </div>
                    </div>
                    <div class="replicator-set-body flex-1 publish-fields @container">
                        <set-field
                            v-for="field in filterFields[filter.type][filter.handle]"
                            :key="field.handle"
                            :field="field"
                            :value="filter.values[field.handle]"
                            :meta="filterFieldMeta(filter.id, filter.handle, field.handle)"
                            :parent-name="name"
                            :set-index="index"
                            :errors="filterFieldErrors(index, field.handle)"
                            :field-path="filterFieldPath(index, field.handle)"
                            :read-only="isReadOnly"
                            v-show="true || showField(field.field, filterFieldPath(index, field.handle))"
                            @updated="($event) => updateFilterField(index, field.handle, $event)"
                            @meta-updated="($event) => updateFilterMeta(filter.id, field.handle, $event)"
                        />
                    </div>
                </div>
            </div>

            <div class="flex">
                <popover ref="addFilterDropdowm" class="dropdown-list filter_builder-dropdown" placement="bottom-start">
                    <template #trigger>
                        <button class="btn">
                            Add Filter
                        </button>
                    </template>
                    <button v-for="field in meta.fields" v-text="field.display" @click="addFilter('field', field.handle)" />
                </popover>
            </div>

        </div>

    </div>

</template>

<script>
const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],
    
    inject: ['storeName'],

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

        resetFilter(index, handle) {
            const { type } = this.value[index];
            this.update([
                ...this.value.slice(0, index),
                { type, handle, values: this.meta.defaults[handle] },
                ...this.value.slice(index + 1),
            ]);
        },

        updateFilterField(index, handle, value) {
            this.update([
                ...this.value.slice(0, index),
                {
                    ...this.value[index],
                    values: {
                        ...this.value[index].values,
                        [handle]: value,
                    }
                },
                ...this.value.slice(index + 1),
            ]);
        },

        updateFilterMeta(id, handle, meta) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [id]: {
                        ...this.meta.existing[id],
                        [handle]: meta,
                    },
                },
            });
        },

        removeFilter(index) {
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1),
            ]);
        },

        filterlabel(filter) {
            const type = {
                field: 'Field',
            }[filter.type];
            const handle = this.fieldsObject[filter.handle].display;
            return `${handle}`;
        },

        filterFieldMeta(id, filter, handle) {
            return this.meta.existing[id][handle];
        },

        filterFieldPath(index, handle) {
            return `${this.handle}.${index}.values.${handle}`;
        },

        filterFieldErrors(index, handle) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.filterFieldPath(index, handle)] || [];
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
        width: 40% !important;
    }
}
.filter_builder-dropdown {
    .popover-content {
        max-height: 15.6rem;
        overflow: auto;
    }
}
</style>