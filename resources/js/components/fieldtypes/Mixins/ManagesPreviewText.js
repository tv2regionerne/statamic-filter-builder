export default {

    computed: {
        previewText() {
            return Object.values(this.previews)
                .filter(value => {
                    if (['null', '[]', '{}', ''].includes(JSON.stringify(value))) return null;
                    return value;
                })
                .map(value => {
                    if (typeof value === 'string') return escapeHtml(value);

                    if (Array.isArray(value) && typeof value[0] === 'string') {
                        return escapeHtml(value.join(', '));
                    }

                    return escapeHtml(JSON.stringify(value));
                })
                .join(' / ');
        }
    }

}
