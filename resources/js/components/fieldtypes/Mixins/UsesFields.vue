<script>
const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        ValidatesFieldConditions,
    ],

    inject: ['storeName'],

    data() {
        return {
            collapsed: this.value.map(item => item.id),
            previews: this.meta.previews,
        };
    },

    mounted() {
        if (this.meta.fields.length === 0) {
            this.loadCollectionsMeta(this.collections);
        }
    },

    computed: {

        mode() {
            return this.config.mode || 'config';
        },

        collections() {
            const store = this.$store.state.publish[this.storeName];
            const prefix = this.fieldPathPrefix || '';
            const key = prefix.slice(0, -this.handle.length) + this.config.field;
            return data_get(store.values, key);
        },

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

        itemFields() {
            return {
                field: Object.fromEntries(this.meta.fields.map(field => ([
                    field.handle,
                    field.fields,
                ]))),
            };
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

            this.$axios.post(cp_url('fields/field-meta'), params).then(response => {
                this.updateMeta(response.data.meta);
            });
        },

        addItem(type, handle) {
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
            this.previews[id] = {};
        },

        updateItem(index, values) {
            this.update([
                ...this.value.slice(0, index),
                { ...this.value[index], values },
                ...this.value.slice(index + 1),
            ]);
        },

        updateItemMeta(id, meta) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [id]: meta,
                },
            });
        },

        updateItemPreviews(id, previews) {
            this.previews[id] = previews;
        },

        removeItem(index) {
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1),
            ]);
        },

        collapseItem(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id);
            }
        },

        expandItem(id) {
            if (this.collapsed.includes(id)) {
                var index = this.collapsed.indexOf(id);
                this.collapsed.splice(index, 1);
            }
        },

        itemMeta(id) {
            return this.meta.existing[id];
        },

        itemPreviews(id) {
            return this.previews[id];
        },

        itemPath(index) {
            return [...this.fieldPathKeys, index].join('.');
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
