<script>
const { ValidatesFieldConditions } = FieldConditions;

export default {

    mixins: [
        ValidatesFieldConditions,
    ],

    data() {
        return {
            collapsed: this.value.map(item => item.id),
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

        itemPath(index) {
            return `${this.handle}.${index}`;
        },

    },

};
</script>