(function ($, _) {
    const categorieItem = {
        props: ['item'],
        template: "#grid-item",
        data: function () {
            return {
                cat: null,
            }
        },
        delimiters: ['${', '}']
    };
    // Application
    new Vue({
        el: '#grid-categorie-items',
        components: {
            'grid-item': categorieItem,
        },
        data: function () {
            return {
                loading: false,
                categoriesCollections: null,
                items: []
            }
        },
        created: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.loading = true;
                // Initialise collection api
                this.categoriesCollections = new wp.api.collections.Categories();
                this.categoriesCollections.fetch({
                    data: {
                        per_page: 8,
                        orderby: 'count',
                        order: 'desc'
                    }
                }).then(resp => {
                    this.items = _.clone(resp);
                    this.loading = false;
                });
            },
            // @event on click more categories button
            moreCategories: function($event) {
                $event.preventDefault();
                this.loading = true;
                if (this.categoriesCollections.hasMore()) {
                    this.categoriesCollections.more().then(resp => {
                        this.items = this.items.concat(resp);
                        this.loading = false;
                    });
                }
            }
        },
        delimiters: ['${', '}']
    });
})(jQuery, lodash);