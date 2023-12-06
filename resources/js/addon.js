import FilterBuilder from './components/fieldtypes/FilterBuilder.vue'
import SortBuilder from './components/fieldtypes/SortBuilder.vue'

Statamic.booting(() => {
    Statamic.component('filter_builder-fieldtype', FilterBuilder)
    Statamic.component('sort_builder-fieldtype', SortBuilder)
})
