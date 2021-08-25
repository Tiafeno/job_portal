(function ($) {
    $().ready(function () {
        const Layout = {
            template: '#layout-archive',
            data: function () {
                return {}
            },
        };

        const ArchivesCompanies = {
            template: '#company-archive-item',
            data: function () {
                return {
                    loading: false,
                    per_page: 10, // default
                    page: 1, //default
                    paging: null,
                    companies: [],
                    companyCollections: null,
                }
            },
            mounted: function () {
                this.initComponent();
            },
            methods: {
                initComponent: function () {
                    this.loading = true;
                    try {
                        // Initialise collection api
                        this.companyCollections = new wp.api.collections.Users;
                        this.companyCollections.fetch({
                            data: {
                                per_page: 8,
                                roles: 'employer',
                                context: 'view'
                            }
                        }).then(resp => {
                            this.companies = _.clone(resp);
                            this.loading = false;
                        });
                    } catch (e) {
                        this.loading = false;
                        console.warn(e);
                    }
                },
            }
        };
        const SingleCompany = {
            template: '#company-details',
            data: function () {
                return {
                    loading: false,
                }
            },
            mounted: async function () {

            }
        };
        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/companies',
                children: [
                    {
                        path: 'companies',
                        name: 'Companies',
                        component: ArchivesCompanies
                    },
                    {
                        path: 'company/:id',
                        name: 'SingleCompany',
                        component: SingleCompany,
                    }
                ],
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });
        new Vue({
            el: '#company-archive',
            router
        });
    });
})(jQuery);