(function ($) {
    $().ready(function () {
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
                    callback: function (data, pagination) {
                    },
                    beforePageOnClick: function (el) {
                        const page = el.currentTarget;
                        const data = page.dataset;
                        self.$emit('change-route-page', parseInt(data.num));
                    }
                });
            },
            methods: {},
            watch: {
                paging: function () {
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
            created: function () {
                if (typeof apiSettings === 'undefined') return;
                this.Wordpress = new WPAPI({
                    endpoint: apiSettings.root,
                    nonce: apiSettings.nonce
                });
            }

        };
        const ArchivesCandidate = {
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
                    wpAxiosInstance: null,

                }
            },
            mounted: function () {
                this.wpAxiosInstance = axios.create({
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
                        .param('public_cv', 1)
                        .param('has_cv', 1)
                        .perPage(this.per_page)
                        .page(this.page);
                    const categoriesRequest = this.wpAxiosInstance.get('categories?per_page=50');
                    await axios.all([categoriesRequest]).then(axios.spread(
                        (...responses) => {
                            self.categories = lodash.clone(responses[0].data);
                        }
                    )).catch(errors => {
                    })
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
        const SingleUser = {
            template: '#candidate-details',
            data: function () {
                return {
                    loading: false,
                    userId: 0,
                    candidate: null,
                    crtCandidateLanguages: [],

                    categories: [],
                    regions: [],
                    languages: [],
                    experiences: [],
                    educations: []
                }
            },
            computed: {
                hasCandidateLanguage: function () {
                    return !lodash.isEmpty(this.crtCandidateLanguages);
                }
            },
            mounted: async function () {
                const self = this;
                this.loading = true;
                this.userId = parseInt(this.$route.params.id);
                const candidateInstance = axios.create({baseURL: apiSettings.root + 'job-portal'});
                let responseCandidate = await candidateInstance.get(`/users/${this.userId}`);
                responseCandidate = responseCandidate.data;
                if (!responseCandidate.success) {
                    alertify.error("Une erreur s'est produit");
                    return;
                }
                let Candidate = lodash.cloneDeep(responseCandidate.data);
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
                        // Populate
                        let metaLanguages = Candidate.meta.languages;
                        let useLg = lodash.isEmpty(metaLanguages) ? [] : JSON.parse(metaLanguages);
                        useLg = lodash.map(useLg, lodash.parseInt);
                        let crtCandidateLanguages = lodash.map(useLg, idLg => {
                            let item = lodash.find(self.languages, {'id': idLg});
                            if (lodash.isUndefined(item)) return null;
                            return item;
                        });

                        // item categories
                        if (!lodash.isEmpty(Candidate.meta.categories)) {
                            let idCategories = JSON.parse(Candidate.meta.categories); // Array return
                            let itemCategories = lodash.map(idCategories, idCtg => {
                                let item = lodash.find(self.categories, {'id': parseInt(idCtg, 10)});
                                return lodash.isUndefined(item) ? null : item.name;
                            });
                            Candidate.itemCategories = lodash.compact(itemCategories);
                        }

                        self.crtCandidateLanguages = lodash.compact(crtCandidateLanguages);
                    }
                ));
                const meta = Candidate.meta;
                self.experiences = JSON.parse(meta.experiences);
                self.educations = JSON.parse(meta.educations);

                self.candidate = lodash.clone(Candidate);
                self.loading = false;
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
                        component: ArchivesCandidate
                    },
                    {
                        path: 'candidate/:id',
                        name: 'UserDetails',
                        component: SingleUser,
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