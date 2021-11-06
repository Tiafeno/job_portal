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
                statusToObj: function() {
                    if (this.candidate === null) return null;
                    return lodash.find(this.status, {'_id': this.candidate.cv_status});
                },
                getRegisterDate: function() {
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
                let $encodeValue = lodash.cloneDeep(responseCandidate);
                let $decodeValue = window.atob($encodeValue);
                const Candidate = JSON.parse($decodeValue);
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
                        let metaLanguages = Candidate.meta.languages;
                        let useLg = lodash.isEmpty(metaLanguages) ? [] : JSON.parse(metaLanguages);
                        useLg = lodash.map(useLg, lodash.parseInt);
                        let crtCandidateLanguages = lodash.map(useLg, idLg => {
                            let item = lodash.find(this.languages, {'id': idLg});
                            if (lodash.isUndefined(item)) return null;
                            return item;
                        });
                        // item categories
                        if (!lodash.isEmpty(Candidate.meta.categories)) {
                            let idCategories = JSON.parse(Candidate.meta.categories); // Array return
                            let itemCategories = lodash.map(idCategories, idCtg => {
                                let item = lodash.find(this.categories, {'id': parseInt(idCtg, 10)});
                                return lodash.isUndefined(item) ? null : item.name;
                            });
                            Candidate.itemCategories = lodash.compact(itemCategories);
                        }
                        this.crtCandidateLanguages = lodash.compact(crtCandidateLanguages);
                    }
                ));
                const meta = Candidate.meta;
                this.experiences = JSON.parse(meta.experiences);
                this.educations = JSON.parse(meta.educations);
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
        Vue.component('v-select', VueSelect.VueSelect);
        new Vue({
            el: '#candidate-archive',
            router
        });
    });
})(jQuery);