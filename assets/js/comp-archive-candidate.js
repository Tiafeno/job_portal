(function($) {
    $().ready(function() {
        const Pagination = {
            template: '#pagination-candidate-template',
            props: ['paging', 'pagesize'],
            data: function () {
                return {
                    source: []
                }
            },
            mounted: function () {
                const self = this;
                if (typeof this.paging.totalPages !== 'undefined') {
                    this.source = lodash.range(0, parseInt(this.paging.totalPages));
                }
                // Pagination view: http://pagination.js.org/docs/index.html
                $('#pagination-archive').pagination({
                    dataSource: self.source,
                    pageSize: self.pagesize,
                    ulClassName: 'pagination',
                    className: '',
                    callback: function (data, pagination) {},
                    beforePageOnClick : function(el) {
                        const page = el.currentTarget;
                        const data = page.dataset;
                        self.$emit('change-route-page', parseInt(data.num));
                    }
                });
            },
            methods: {
            },
            watch: {
                paging: function() {
                    if (typeof this.paging.totalPages === 'undefined') return [];
                    this.source = lodash.range(0, parseInt(this.paging.totalPages));
                    return this.paging;
                }
            }
        };
        const Layout = {
            template: '#layout-archive',
            data: function () {
                return {
                    Wordpress: null
                }
            },
            created: function() {
                if (typeof apiSettings === 'undefined') return;
                this.Wordpress = new WPAPI({
                    endpoint: apiSettings.root,
                    nonce: apiSettings.nonce
                });
            }

        };
        const CompArchives = {
            template: '#candidate-archive-item',
            components: {
                'com-pagination': Pagination
            },
            data: function () {
                return {
                    loading: false,
                    request: '',
                    per_page: 2, // default
                    page: 1, //default
                    paging: null,
                    Annonces: [],
                }
            },
            mounted: function() {
                this.init();
            },
            methods: {
                init: function () {
                    const self = this;
                    this.loading = true;
                    this.request = this.$parent.Wordpress.users()
                        .param('roles', 'candidate')
                        .param('has_cv', 1)// boolean value
                        .perPage(this.per_page)
                        .page(this.page);

                    this.request.then(resp => {
                            self.Annonces = lodash.clone(resp);
                            self.paging = lodash.isUndefined(resp._paging) ? null : resp._paging;
                            self.loading = false;
                        })
                },
                Route: function (page) {
                    /**
                     * Changer les routes pour l'affichage des annonces
                     * TODO: Ajouter les variables dans local storage pour avoir des valeur par default
                     * @type {boolean}
                     */
                    let edited = false;
                    if (page === this.page) return;
                    this.page = page;

                    if (edited) {
                        // Promise response
                        const archivesPromise = this.request
                            .per_page(self.per_page)
                            .page(self.page)
                            .get();
                        self.loading = true;
                        archivesPromise.then(response => {
                            self.loading = false;
                        })
                    }
                },
            }
        };
        const UserDetails = {
            template: '#candidate-details',
            data: function() {
                return {
                    loading: false,
                    userId: 0,
                    candidate: null,
                }
            },
            mounted: function() {
                const self = this;
                this.userId = parseInt(this.$route.params.id);
                this.$parent.Wordpress.users().id(this.userId)
                    .then(resp => {
                        self.candidate = lodash.clone(resp);
                    });
            }
        };


        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/archives',
                children: [
                    {
                        path: 'archives',
                        name: 'Archives',
                        component: CompArchives
                    },
                    {
                        path: 'candidate/:id',
                        name:'UserDetails',
                        component: UserDetails,
                    }
                ],
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });

        Vue.component('v-select', VueSelect.VueSelect);
        // Application
        new Vue({
            el: '#candidate-archive',
            router
        });
    });
})(jQuery);