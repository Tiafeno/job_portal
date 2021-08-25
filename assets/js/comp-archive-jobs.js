const urlSearchParams = new URLSearchParams(window.location.search);
const params = Object.fromEntries(urlSearchParams.entries());
const paramKeys = Object.keys(params); // return array of keys

(function ($, _) {
    $().ready(function () {
        const jobapiAxiosInstance = axios.create({
            baseURL: archiveApiSettings.root + 'job/v2',
            headers: {
                'X-WP-Nonce': archiveApiSettings.nonce
            }
        });
        const regionFilter = {
            props: ['regions'],
            template: "#filter-region-template",
            data: function () {
                return {
                    valueSelected: null,
                    items: []
                }
            },
            created: function () {
                if (_.indexOf(paramKeys, 'region') >= 0) {
                    this.valueSelected = parseInt(params['region']);
                }
                if (_.isArray(this.regions)) {
                    this.items = _.map(this.regions, cat => cat);
                }
            },
            methods: {
                selectedFilter: function ($event) {
                    $event.preventDefault();
                    let values = [];
                    // Get all input selected
                    const inputChecked = $('select.region-filter');
                    inputChecked.each(function (index, el) {
                        values.push($(el).val());
                    });
                    const inputName = inputChecked.attr('name');
                    this.$emit('changed', values, inputName);
                }
            }
        };
        const categoryFilter = {
            props: ['categories'],
            template: "#filter-category-template",
            data: function () {
                return {
                    valueSelected: null,
                    items: []
                }
            },
            created: function () {
                if (_.indexOf(paramKeys, 'cat') >= 0) {
                    this.valueSelected = parseInt(params['cat']);
                }
                if (_.isArray(this.categories)) {
                    this.items = _.map(this.categories, cat => cat);
                }
            },
            methods: {
                selectedFilter: function ($event) {
                    $event.preventDefault();
                    let values = [];
                    // Get all input selected
                    const inputChecked = $($event.currentTarget);
                    inputChecked.each(function (index, el) {
                        values.push($(el).val());
                    });
                    const inputName = inputChecked.attr('name');
                    this.$emit('changed', values, inputName);
                }
            }
        };
        const salaryFilter = {
            props: ['salaries'],
            template: '#filter-salary-template',
            data: function () {
                return {
                    items: []
                }
            },
            created: function () {
                if (_.isArray(this.salaries)) {
                    this.items = _.map(this.salaries, salary => {
                        var valueFloat = parseFloat(salary.name);
                        var amount = valueFloat.toLocaleString("en-GB", {
                            style: "currency",
                            currency: "MGA",
                            minimumFractionDigits: 0
                        });
                        salary.filter_name = 'Plus de ' + amount.toString();
                        return salary;
                    });
                }

            },
            methods: {
                selectedFilter: function ($event) {
                    $event.preventDefault();
                    const target = $event.target;
                    let values = [];

                    // Get all input selected
                    const inputChecked = $('input:checked.salary-filter');
                    inputChecked.each(function (index, el) {
                        values.push($(el).val());
                    });
                    const inputName = inputChecked.attr('name');
                    this.$emit('changed', values, inputName);
                }
            }
        };
        const searchFilter = {
            template: '#filter-search-template',
            data: function () {
                return {}
            },
            methods: {
                searchKey: function (ev) {
                    ev.preventDefault();
                    const el = ev.target;
                    if (ev.keyCode === 13) { // Enter press...
                        const elValue = $(el).val()
                        this.$emit('changed', elValue, 'search');
                    }
                }
            }
        };
        // Composant 'je postule'
        const CompApply = {
            template: "#apply-job",
            props: ['jobid'],
            data: function () {
                return {
                    loading: false,
                    isLogged: false,
                    buttonText: "Je postule",
                    message: {success: null, data: ''},
                }
            },
            mounted: function () {
                this.isLogged = !!archiveApiSettings.isLogged;
            },
            watch: {
                loading: function () {
                    this.buttonText = this.loading ? "Chargement..." : "Je postule";
                }
            },
            methods: {
                apply: function () {
                    const self = this;
                    const jobId = this.jobid;
                    if (!this.isLogged) {
                        // Call login modal
                        renderLoginModel();
                        $('#signin').modal('show');
                    } else {
                        this.loading = true;
                        jobapiAxiosInstance.post(`apply/${jobId}`, {}).then(function (response) {
                            const dataResponse = response.data;
                            self.message = _.clone(dataResponse);
                            self.loading = false;
                        }).catch(function () {
                            self.loading = false;
                        })
                    }
                }
            }
        };
        const jobVerticalLists = {
            props: ['item'],
            components: {
                'comp-apply': CompApply
            },
            template: "#job-vertical-lists",
        };
        const Pagination = {
            template: '#pagination-jobs-template',
            props: ['paging', 'pagesize'],
            data: function () {
                return {
                    source: []
                }
            },
            mounted: function () {
                const self = this;
                if (typeof this.paging.totalPages !== 'undefined') {
                    this.source = _.range(0, parseInt(this.paging.totalPages));
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
                        self.$emit('change-route-page', parseInt(data.num), 'page');
                    }
                });
            },
            methods: {},
            watch: {
                paging: function () {
                    if (typeof this.paging.totalPages === 'undefined') return [];
                    this.source = _.range(0, parseInt(this.paging.totalPages));
                    return this.paging;
                }
            }
        };
        const archiveJobs = {
            template: "#job-archive-template",
            props: ['taxonomies'],
            components: {
                'filter-salary': salaryFilter,
                'filter-search': searchFilter,
                'filter-region': regionFilter,
                'filter-category': categoryFilter,
                'job-vertical-lists': jobVerticalLists,
                'com-pagination': Pagination
            },
            data: function () {
                return {
                    loadArchive: false,
                    archives: [], // content
                    WPAPI: null,
                    hasURLSearchParam: false,
                    Request: {}, // object request
                    ParamsFilter: {}, // for all search filter
                    paging: null, // content pagination
                    per_page: 10, // per page default value
                    page: 1, // default page value
                    totalResults: 0, // total number results
                    inputPerPages: _.range(10, 50, 10),
                    // node api params
                    _context: 'view',
                    _status: 'publish',
                }
            },
            mounted: function () {
                if (typeof archiveApiSettings === 'undefined') {
                    return;
                }
                this.WPAPI = new WPAPI({
                    endpoint: archiveApiSettings.root,
                    nonce: archiveApiSettings.nonce
                });
                this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                    params: ['page', 'per_page', 'offset', 'context', 'param', 'search', 'filter']
                });
                // Verifier s'il y a des parametres dans l'URL
                if (!_.isEmpty(paramKeys)) {
                    this.hasURLSearchParam = true;
                    paramKeys.forEach(valueKey => {
                        this.applyFilter(params[valueKey], valueKey, true);
                    });
                }
                this.getRequest();
            },
            methods: {
                requestHandler: function () {
                    return this.Request = _.cloneDeep(this.WPAPI.jobs());
                },
                Route: function (page, view = 'per_page') {
                    /**
                     * Changer les routes pour l'affichage des annonces
                     * TODO: Ajouter les variables dans local storage pour avoir des valeur par default
                     * @type {boolean}
                     */
                    let edited = false;
                    if (view === 'per_page') {
                        if (page === this.per_page) return;
                        this.per_page = page;
                        edited = true;
                    }

                    if (view === 'page') {
                        if (page === this.page) return;
                        this.page = page;
                        edited = true;
                    }

                    if (edited) this.getRequest();
                },
                resetFilter: function ($event) {
                    $event.preventDefault();
                    this.ParamsFilter = {};
                    this.getRequest();
                    // Reset input radio/checkbox filter
                    $('#archive-jobs input[type="radio"]').prop('checked', false)
                    $('#archive-jobs input[type="checkbox"]').prop('checked', false)
                },
                applyFilter: function (data, TEvent, multipleFilter = false) {
                    if (_.isEmpty(TEvent)) return;
                    let _params = null;
                    switch (TEvent) {
                        case 'salaries':
                            if (_.isEmpty(data)) {
                                this.ParamsFilter.salaries = {};
                                break;
                            }
                            _params = _.map(data, _.unary(parseInt));
                            this.ParamsFilter.salaries = {
                                props: 'salaries',
                                type: 'taxonomy',
                                param: _params
                            };
                            break;
                        case 'region':
                            let _param = parseInt(data);
                            if (_.indexOf([0, '0', ' ', ''], _param) >= 0) {
                                this.ParamsFilter.region = {};
                                break;
                            }
                            this.ParamsFilter.region = {
                                props: 'region',
                                type: 'taxonomy',
                                param: _param
                            };
                            break;
                        case 'cat':
                            let catId = parseInt(data);
                            if (_.indexOf([0, '0'], catId) >= 0) {
                                this.ParamsFilter.cat = {};
                                break;
                            }
                            this.ParamsFilter.cat = {
                                props: 'categories',
                                type: 'taxonomy',
                                param: parseInt(catId)
                            };
                            break;
                        case 'search':
                            if (data === '' || data === ' ') {
                                this.ParamsFilter.search = {};
                                break;
                            }
                            this.ParamsFilter.search = {
                                props: 'search',
                                type: null,
                                param: data.trim()
                            };
                            break;
                        default:
                            break;
                    }
                    if (!multipleFilter) this.getRequest();
                },
                getRequest: function () {
                    const self = this;
                    // Initialise request
                    this.requestHandler();
                    if (!_.isEmpty(this.ParamsFilter)) {
                        const pKeys = Object.keys(this.ParamsFilter);
                        pKeys.forEach((value) => {
                            // Recuperer le filtre
                            let filter = self.ParamsFilter[value];
                            // Si le type du filtre est une taxonomie (salaries, region e.g)
                            if (filter.type === 'taxonomy') {
                                self.Request.param(filter.props, filter.param);
                            }
                            // La requete n'est pas le même pour la recherche par mot.
                            // Ici c'est spécialement pour `search`
                            if (filter.props === 'search') {
                                self.Request.search(filter.param);
                            }
                        });
                    }
                    // Promise response
                    const archivesPromise = this.Request
                        .per_page(self.per_page)
                        .page(self.page)
                        .get();
                    self.loadArchive = true;
                    archivesPromise.then(function (response) {
                        // Si la reponse est vide
                        if (_.isEmpty(response)) {
                            self.archives = [];
                            self.paging = null
                            self.loadArchive = false;
                            return;
                        }
                        // On recupere la reponse
                        const archivesResponse = _.cloneDeep(response);
                        self.paging = _.clone(response._paging); // Update paging value
                        // Add property value
                        self.archives = _.map(archivesResponse, function (archive) {
                            archive.get_type_name = ''; // add type of contract for annonce
                            archive.get_cat_name = '';
                            const type = archive.job_type;
                            // Type de contrat
                            if (_.isArray(type) && !_.isEmpty(type)) {
                                let i = _.head(type);
                                let j = _.find(self.taxonomies.Types, {'id': parseInt(i)});
                                archive.get_type_name = j.name;
                            }
                            // Categorie
                            const categories = archive.categories;
                            if (_.isArray(categories) && !_.isEmpty(categories)) {
                                let k = _.head(categories);
                                let l = _.find(self.taxonomies.Categories, {'id': parseInt(k)});
                                archive.get_cat_name = l.name;
                            }
                            return archive;
                        });
                        self.loadArchive = false;
                    });

                }
            }
        };
        // Application
        new Vue({
            el: '#archive-jobs',
            components: {
                'comp-archive-jobs': archiveJobs,
            },
            data: function () {
                return {
                    loading: false,
                    Taxonomies: {
                        Types: [],
                        Salaries: [],
                        Categories: [],
                        Regions: []
                    },
                    axiosInstance: null,
                    itemsCount: 8,
                }
            },
            created: function () {
                if (typeof archiveApiSettings === 'undefined') {
                    return;
                }
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    this.axiosInstance = axios.create({
                        baseURL: archiveApiSettings.root + 'wp/v2',
                        headers: {
                            'X-WP-Nonce': archiveApiSettings.nonce
                        }
                    });
                    const categoriesRequest = this.axiosInstance.get('categories?per_page=80&hide_empty=false');
                    const typesRequest = this.axiosInstance.get('job_type?per_page=50');
                    const salaryRequest = this.axiosInstance.get('salaries?per_page=50');
                    const regionRequest = this.axiosInstance.get('region?per_page=50');
                    this.loading = true;
                    await axios.all([typesRequest, categoriesRequest, salaryRequest, regionRequest]).then(axios.spread(
                        (...responses) => {
                            self.Taxonomies.Categories = _.clone(responses[1].data);
                            self.Taxonomies.Types = _.clone(responses[0].data);
                            self.Taxonomies.Salaries = _.clone(responses[2].data);
                            self.Taxonomies.Regions = _.clone(responses[3].data);
                            self.loading = false;
                        }
                    )).catch(errors => { })
                },
            }
        });
    });
})(jQuery, lodash);