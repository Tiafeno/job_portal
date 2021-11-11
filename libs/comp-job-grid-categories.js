"use strict";

(function ($, _) {
  var categorieItem = {
    props: ['item'],
    template: "#grid-item",
    data: function data() {
      return {
        cat: null
      };
    },
    delimiters: ['${', '}']
  }; // Application

  wp.api.loadPromise.done(function () {
    new Vue({
      el: '#grid-categorie-items',
      components: {
        'grid-item': categorieItem
      },
      data: function data() {
        return {
          loading: false,
          categoriesCollections: null,
          items: []
        };
      },
      created: function created() {
        this.init();
      },
      methods: {
        init: function init() {
          var _this = this;

          this.loading = true;

          try {
            // Initialise collection api
            this.categoriesCollections = new wp.api.collections.Categories();
            this.categoriesCollections.fetch({
              data: {
                per_page: 8,
                orderby: 'count',
                order: 'desc'
              }
            }).then(function (resp) {
              _this.items = _.clone(resp);
              _this.loading = false;
            });
          } catch (e) {
            this.loading = false;
            console.warn(e);
          }
        },
        // @event on click more categories button
        moreCategories: function moreCategories($event) {
          var _this2 = this;

          $event.preventDefault();

          if (this.categoriesCollections.hasMore()) {
            this.loading = true;
            this.categoriesCollections.more().then(function (resp) {
              _this2.items = _this2.items.concat(resp);
              _this2.loading = false;
            });
          }
        }
      },
      delimiters: ['${', '}']
    });
  });
})(jQuery, lodash);