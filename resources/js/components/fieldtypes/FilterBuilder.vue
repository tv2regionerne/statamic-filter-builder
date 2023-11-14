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
                    <div class="replicator-set-body publish-fields @container">
                        <publish-container
                            :name="`filter-${index}`"
                            :meta="meta.existing[filter.id] ?? meta.new[filter.handle]"
                            :values="filter.values"
                            :track-dirty-state="false"
                            @updated="($event) => updateFilter(index, $event)"
                        >
                            <publish-fields
                                slot-scope="{ setFieldValue, setFieldMeta }"
                                :fields="filterFields[filter.type][filter.handle]"
                                :name-prefix="`filter-${index}`"
                                class="w-full"
                                @updated="setFieldValue"
                                @meta-updated="setFieldMeta"
                            />
                        </publish-container>
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
import qs from "qs";

export default {

    mixins: [ Fieldtype ],
    
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
            this.$refs.addFilterDropdowm.close();
            this.update([
                ...this.value,
                { type, handle, values: this.meta.defaults[handle] },
            ]);
        },

        resetFilter(index, handle) {
            const { type } = this.value[index];
            this.update([
                ...this.value.slice(0, index),
                { type, handle, values: this.meta.defaults[handle] },
                ...this.value.slice(index + 1),
            ]);
        },

        updateFilter(index, values) {
            this.update([
                ...this.value.slice(0, index),
                {...this.value[index], values: { ...values }},
                ...this.value.slice(index + 1),
            ]);
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

    },

};
</script>
<style>
.filter_builder-fieldtype {
    .replicator-set-body {
        padding: 0.5rem;
    }
    .form-group {
        padding: 0.5rem;
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