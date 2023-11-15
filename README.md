# Statamic Filter Builder

This addon allows you to build advanced collection filters through the control panel.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require tv2regionerne/statamic-filter-builder
```

## How to Use

Add a filter builder field to a publish form then create and entry and set up your filtering rules. In your template you can then apply those filters to a collection tag like this:

```html
{{ collection:articles query_scope="filter_builder" :filter_builder="my_filters" }}
```