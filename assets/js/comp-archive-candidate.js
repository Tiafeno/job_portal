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
                    per_page: 10, // default
                    page: 1, //default
                    paging: null,
                    annonces: [],
                    categories: [],
                    axiosInstance: null,
                }
            },
            mounted: function() {
                this.axiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    this.loading = true;
                    this.request = this.$parent.Wordpress.users()
                        //.param('roles', 'candidate') // Not allow for client not logged in
                        .param('has_cv', 1)// boolean value
                        .perPage(this.per_page)
                        .page(this.page);

                    const categoriesRequest = this.axiosInstance.get('categories?per_page=50');
                    await axios.all([categoriesRequest]).then(axios.spread(
                        (...responses) => {
                            self.categories = lodash.clone(responses[0].data);
                        }
                    )).catch(errors => { })

                    this.request.then(resp => {
                            let annonces = lodash.clone(resp);
                            self.annonces = lodash.map(annonces, annonce => {
                                annonce.job = '';
                                let currentCategories = JSON.parse(annonce.meta.categories);
                                if (!lodash.isArray(currentCategories)) return annonce;
                                let job = lodash.find(self.categories, {'id': lodash.head(currentCategories)});
                                if (lodash.isUndefined(job) || !job) return annonce;
                                annonce.job = job.name;
                                return annonce;
                            });
                            self.paging = lodash.isUndefined(resp._paging) ? null : resp._paging;
                            self.loading = false;
                        })
                },
                Route: function (page) {
                    if (page === this.page) return;
                    this.page = page;
                    // Promise response
                    const archivesPromise = this.request
                        .per_page(self.per_page)
                        .page(self.page)
                        .get();
                    self.loading = true;
                    archivesPromise.then(response => {
                        self.loading = false;
                    });
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

                    categories: [],
                    regions: [],
                    languages: [],
                    experiences: [],
                    educations: []
                }
            },
            mounted: async function() {
                const self = this;
                this.loading = true;
                this.userId = parseInt(this.$route.params.id);
                let axiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                const categoriesRequest = axiosInstance.get('categories?per_page=50');
                const languagesRequest = axiosInstance.get('language?per_page=50');
                const regionsRequest = axiosInstance.get('region?per_page=50');
                await axios.all([categoriesRequest, languagesRequest, regionsRequest]).then(axios.spread(
                    (...responses) => {
                        self.categories = lodash.clone(responses[0].data);
                        self.languages = lodash.clone(responses[1].data);
                        self.regions = lodash.clone(responses[2].data);
                    }
                )).catch(errors => { })
                this.$parent.Wordpress.users().id(this.userId)
                    .then(resp => {
                        console.log(resp);
                        const meta = resp.meta;
                        self.experiences = JSON.parse(meta.experiences);
                        self.educations = JSON.parse(meta.educations);

                        self.candidate = lodash.clone(resp);
                        self.loading = false;
                    });
            }
        };


        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/candidates',
                children: [
                    {
                        path: 'candidates',
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