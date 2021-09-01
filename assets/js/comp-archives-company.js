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
                    totals: 0,
                    page: 1, //default
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
                        this.companyCollections = axios.create({
                            baseURL: apiSettings.root + 'job/v2',
                            headers: {'X-WP-Nonce': apiSettings.nonce}
                        });
                        this.companyCollections.get(`/companies`).then(resp => {
                            if (resp.status === 200) {
                                const results = resp.data;
                                this.totals = results.total;
                                this.companies = lodash.clone(results.data);
                            }
                            this.loading = false;
                        });
                    } catch (e) {
                        this.loading = false;
                        console.warn(e);
                    }
                },
            }
        };
        const JobsCompany = {
            props: ['employerid'],
            template: "#company-jobs",
            data: function () {
                return {
                    loading: false,
                    jobs: [],
                }
            },
            created: function () {
                this.initComponent();
            },
            methods: {
                initComponent: function () {
                    const employerCollection = new wp.api.collections.Emploi();
                    this.loading = true;
                    employerCollection.fetch({
                        data: {
                            per_page: 8,
                            meta_key: 'employer_id',
                            meta_value: this.employerid
                        }
                    }).then(resp => {
                        try {
                            this.jobs = lodash.clone(resp);
                            this.loading = false;
                        } catch (e) {
                            console.warn(e);
                            this.loading = false;
                        }
                    })
                }
            }
        };
        const SingleCompany = {
            template: '#company-details',
            components: {
                'comp-company-jobs': JobsCompany
            },
            data: function () {
                return {
                    companyId: 0,
                    employerId: 0,
                    loading: false,
                    company: null
                }
            },
            mounted: function () {
                this.initComponent();
            },
            methods: {
                initComponent: async function () {
                    try {
                        this.companyId = parseInt(this.$route.params.id, 10);
                        // Initialise collection api
                        const InstanceAxios = axios.create({
                            baseURL: apiSettings.root + 'job/v2',
                            headers: {'X-WP-Nonce': apiSettings.nonce}
                        });
                        this.loading = true;
                        // Recuperer l'employer de cette entreprise
                        const employerResponse = await InstanceAxios.get(`/companies/${this.companyId}/employer`);
                        // Recuperer l'entreprise
                        if (employerResponse.status === 200) {
                            InstanceAxios.get(`/companies/${this.companyId}`).then(resp => {
                                const employer = employerResponse.data;
                                this.employerId = employer.id;
                                if (resp.status === 200) {
                                    const responseHTTP = resp.data;
                                    if (responseHTTP.success) {
                                        this.company = lodash.clone(responseHTTP.data);
                                    }
                                }
                                this.loading = false;
                            });
                        } else {
                            console.log(employerResponse.data);
                            this.loading = false;
                        }

                    } catch (e) {
                        this.loading = false;
                        console.warn(e);
                    }
                }
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
                        path: 'companies/:id',
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