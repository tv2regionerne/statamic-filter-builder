<template>

    <div class="replicator-set mb-2">
        <div class="replicator-set-header p-0" :class="{ 'collapsed': collapsed }">
            <div class="flex items-center justify-between flex-1 p-2 replicator-set-header-inner cursor-pointer" @click="toggleCollapsed">
                <div class="text-sm leading-none">
                    {{ field.display }}
                </div>
                <button class="flex self-end group items-center" @click="$emit('removed')" :aria-label="__('statamic-filter-builder::fieldtypes.filter_builder.delete_filter')">
                    <svg-icon name="micro/trash" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
                </button>
            </div>
        </div>
        <div class="replicator-set-body flex-1 publish-fields @container" v-show="!collapsed">
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
        collapsed: {
            default: false,
        },
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

        toggleCollapsed() {
            if (this.collapsed) {
                this.expand();
            } else {
                this.collapse();
            }
        },

        collapse() {
            this.$emit('collapsed');
        },

        expand() {
            this.$emit('expanded');
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