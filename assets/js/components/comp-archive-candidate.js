(function ($) {
    $().ready(function () {
        moment.locale('fr');
        const jobHTTPInstance = axios.create({
            baseURL: apiSettings.root + 'job/v2',
            headers: {'X-WP-Nonce': apiSettings.nonce}
        });
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
        };
        const ArchivesCandidate = {
            template: '#candidate-archive-item',
            components: {
                'com-pagination': Pagination
            },
            data: function () {
                return {
                    loading: false,
                    s: '',
                    region: '',
                    cat: '',
                    request: '',
                    per_page: 10, // default
                    page: 1, //default
                    paging: null,
                    annonces: [],
                    categories: [],
                    wpAxiosInstance: null,
                    wordpress: null,
                }
            },
            created: function () {
                if (typeof apiSettings === 'undefined') return;
                this.wpAxiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                this.wordpress = new WPAPI({
                    endpoint: apiSettings.root,
                    nonce: apiSettings.nonce
                });
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    this.loading = true;
                    this.request = this.wordpress.users()
                        //.param('roles', 'candidate') // Not allow for client not logged in
                        .param('validated', 1)
                        .param('has_cv', 1)
                        .param('blocked', 0)
                        .perPage(this.per_page)
                        .page(this.page);
                    const categoriesRequest = this.wpAxiosInstance.get('categories?per_page=50');
                    await axios.all([categoriesRequest]).then(axios.spread(
                        (...responses) => {
                            self.categories = lodash.clone(responses[0].data);
                        }
                    )).catch(errors => { })
                    this.request.then(resp => {
                        this._buildAnnonceHandler(resp);
                        this.loading = false;
                    });
                },
                _buildAnnonceHandler: function(wpResponse) {
                    let annonces = lodash.clone(wpResponse);
                    this.annonces = lodash.map(annonces, annonce => {
                        annonce.job = '';
                        let currentCategories = JSON.parse(annonce.meta.categories);
                        if (!lodash.isArray(currentCategories)) return annonce;
                        let job = lodash.find(this.categories, {'id': lodash.head(currentCategories)});
                        if (lodash.isUndefined(job) || !job) return annonce;
                        annonce.job = job.name;
                        return annonce;
                    });
                    this.paging = lodash.isUndefined(wpResponse._paging) ? null : wpResponse._paging;
                },
                filterHandler: function(ev) {
                    ev.preventDefault();
                    const self = this;
                    this.loading = true;
                    const filterReq = this.request
                        .param('s', this.s)
                        .param('region', this.region)
                        .param('cat', this.cat)
                        .get();
                    filterReq.then(function(resp) {
                        self._buildAnnonceHandler(resp);
                        self.loading = false;
                    });
                },
                Route: function (page) {
                    if (page === this.page) return;
                    this.page = page;
                    // Promise response
                    const archivesPromise = this.request
                        .per_page(this.per_page)
                        .page(this.page)
                        .get();
                    this.loading = true;
                    archivesPromise.then(response => {
                        this._buildAnnonceHandler(response);
                        this.loading = false;
                    });
                },
            }
        };
        const SingleCandidate = {
            template: '#candidate-details',
            data: function () {
                return {
                    loading: false,
                    status: [],
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
                },
                statusToObj: function() { // je cherche ...
                    if (this.candidate === null) return null;
                    if (typeof this.candidate.status._id == 'undefined') return null;
                    return lodash.find(this.status, {'_id': this.candidate.status._id});
                },
                getRegisterDate: function() { // Date d'inscription dans le site
                    return moment(this.candidate.registered_date).format('LLL');
                }
            },
            mounted: async function () {
                this.loading = true;
                this.userId = parseInt(this.$route.params.id);
                const candidateInstance = axios.create({baseURL: apiSettings.root + 'job/v2'});
                let responseCandidate = await candidateInstance.get(`/candidate/${this.userId}`);
                responseCandidate = responseCandidate.data;
                if (!responseCandidate) {
                    alertify.error("Une erreur s'est produit");
                    return;
                }
                const Candidate = lodash.cloneDeep(responseCandidate);
                let axiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                const categoriesRequest = axiosInstance.get('categories?per_page=50');
                const languagesRequest = axiosInstance.get('language?per_page=50');
                const regionsRequest = axiosInstance.get('region?per_page=50');
                const cvStatusRequest =  jobHTTPInstance.get('/cv-status');
                await axios.all([categoriesRequest, languagesRequest, regionsRequest, cvStatusRequest]).then(axios.spread(
                    (...responses) => {
                        this.categories = lodash.clone(responses[0].data);
                        this.languages = lodash.clone(responses[1].data);
                        this.regions = lodash.clone(responses[2].data);
                        this.status = lodash.clone(responses[3].data);
                        // Populate
                        let languages = Candidate.languages;
                        let useLg = lodash.isEmpty(languages) ? [] : languages;
                        this.crtCandidateLanguages = lodash.clone(useLg);

                        // item categories
                        Candidate.itemCategories = lodash.clone(Candidate.categories);
                    }
                ));
                this.experiences = Candidate.experiences;
                this.educations = Candidate.educations;
                this.candidate = lodash.clone(Candidate);
                this.loading = false;
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
                        component: SingleCandidate,
                    }
                ],
            }
        ];
        // short for `routes: routes`
        const router = new VueRouter({ routes  });
        wp.api.loadPromise.done( () => {
            Vue.component('v-select', VueSelect.VueSelect);
            new Vue({
                el: '#candidate-archive',
                router
            });
        });

    });
})(jQuery);