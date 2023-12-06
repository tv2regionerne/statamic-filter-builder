<template>

    <div class="replicator-set mb-2">
        <div class="replicator-set-header p-0" :class="{ 'collapsed': collapsed }">
            <div class="flex items-center justify-between flex-1 px-2 py-1.5 replicator-set-header-inner cursor-pointer" @click="toggleCollapsed">
                <label class="text-xs whitespace-nowrap mr-2">
                    {{ field.display }}
                </label>
                <div v-show="collapsed" class="flex-1 min-w-0 w-1 pr-8">
                    <div v-html="previewText" class="help-block mb-0 whitespace-nowrap overflow-hidden text-ellipsis" />
                </div>
                <button class="flex group items-center" @click="$emit('removed')" :aria-label="__('statamic-filter-builder::fieldtypes.sort_builder.delete_sort')">
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
                @replicator-preview-updated="updatePreview(field.handle, $event)"
            />
        </div>
    </div>

</template>

<script>
import ManagesPreviewText from './Mixins/ManagesPreviewText.js';

const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        ValidatesFieldConditions,
        ManagesPreviewText,
    ],
    
    inject: ['storeName'],

    props: {
        sort: {},
        field: {},
        values: {},
        fields: {},
        meta: {},
        previews: {},
        errors: {},
        fieldPathPrefix: {},
        readOnly: {},
        parentName: {},
        index: {},
        collapsed: {
            default: false,
        },
    },

    data() {
        return {
            fieldPreviews: this.previews,
        }
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

        updatePreview(handle, preview) {
            this.$emit('previews-updated', this.fieldPreviews = {
                ...this.fieldPreviews,
                [handle]: preview,
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