<template>

    <div class="replicator-set shadow-sm mb-2 rounded border">
        <div class="replicator-set-header">
            <div class="py-2 pl-2 replicator-set-header-inner flex justify-between items-end w-full">
                <div class="text-sm leading-none">
                    {{ field.display }}
                </div>
                <button class="flex self-end group items-center" @click="$emit('removed')" :aria-label="__('statamic-filter-builder::fieldtypes.filter_builder.delete_filter')">
                    <svg-icon name="micro/trash" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
                </button>
            </div>
        </div>
        <div class="replicator-set-body flex-1 publish-fields @container">
            <set-field
                v-for="field in fields"
                :key="field.handle"
                :field="field"
                :value="values[field.handle]"
                :meta="meta[field.handle]"
                :parent-name="parentName"
                :set-index="index"
                :errors="fieldErrors(field)"
                :field-path="fieldPath(field)"
                :read-only="isReadOnly"
                v-show="showField(field, fieldPath(field))"
                @updated="update(field.handle, $event)"
                @meta-updated="updateMeta(field.handle, $event)"
            />
        </div>
    </div>

</template>

<script>
const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        ValidatesFieldConditions,
    ],
    
    inject: ['storeName'],

    props: {
        filter: {},
        field: {},
        values: {},
        fields: {},
        meta: {},
        errors: {},
        fieldPathPrefix: {},
        readOnly: {},
        parentName: {},
        index: {},
    },

    computed: {


    },

    methods: {

        update(handle, value) {
            this.$emit('updated', {
                ...this.values,
                [handle]: value,
            });
        },

        updateMeta(handle, meta) {
            this.$emit('meta-updated', {
                ...this.meta,
                [handle]: meta,
            });
        },

        fieldPath(field) {
            return `${this.fieldPathPrefix}.values.${field.handle}`;
        },

        fieldErrors(field) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.fieldPath(field)] || [];
        },

    },

};
</script>