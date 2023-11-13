import FilterBuilder from './components/fieldtypes/FilterBuilder.vue'

Statamic.booting(() => {
    Statamic.component('filter_builder-fieldtype', FilterBuilder)
})
