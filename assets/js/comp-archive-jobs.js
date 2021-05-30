(function ($) {
    $().ready(function () {
        const salaryFilter = {
            props: ['salaries'],
            template: '#filter-salary-template',
            data: function () {
                return {
                    items: []
                }
            },
            created: function () {
                if (lodash.isArray(this.salaries)) {
                    this.items = lodash.map(this.salaries, salary => {
                        salary.filter_name = 'Under ' + salary.name;
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
                    const inputChecked = $('input:checked[name="salarie"]');
                    inputChecked.each(function (index, el) {
                        values.push($(el).val());
                    });

                    this.$emit('changed', values, 'salaries');
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

        const jobVerticalLists = {
            props: ['item'],
            template: "#job-vertical-lists",
            data: function () {
                return {}
            },
            created: function () {
                console.log(this.item);
            },
            mounted: function () {

            },
            methods: {
                viewContent: function ($event) {
                }
            }
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
                        self.$emit('change-route-page', parseInt(data.num), 'page');
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

        const archiveJobs = {
            template: "#job-archive-template",
            props: ['taxonomies'],
            components: {
                'filter-salary': salaryFilter,
                'filter-search': searchFilter,
                'job-vertical-lists': jobVerticalLists,
                'com-pagination': Pagination
            },
            data: function () {
                return {
                    loadArchive: false,
                    archives: [], // content
                    WPAPI: null,
                    Request: {}, // object request
                    ParamsFilter: {}, // for all search filter
                    paging: null, // content pagination
                    per_page: 10, // per page default value
                    page: 1, // default page value
                    totalResults: 0, // total number results
                    inputPerPages : lodash.range(10, 50, 10),
                    // node api params
                    _context: 'view',
                    _status: 'publish',
                }
            },
            created: function () {
                if (typeof archiveApiSettings === 'undefined') {
                    return;
                }
                this.WPAPI = new WPAPI({
                    endpoint: archiveApiSettings.root,
                    nonce: archiveApiSettings.nonce
                });
                this.init();
            },
            mounted: function () {

            },
            methods: {
                init: function () {
                    this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                        params: ['page', 'per_page', 'offset', 'context', 'param', 'search', 'filter']
                    });
                    this.getRequest();
                },
                requestHandler: function () {
                    return this.Request = lodash.cloneDeep(this.WPAPI.jobs());
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
                applyFilter: function (data, TEvent) {
                    if (lodash.isEmpty(TEvent)) return;
                    let _params = null;
                    switch (TEvent) {
                        case 'salaries':
                            if (lodash.isEmpty(data)) {
                                this.ParamsFilter.salaries = {};
                                break;
                            }
                            _params = lodash.map(data, lodash.unary(parseInt));
                            this.ParamsFilter.salaries = {
                                props: 'salaries',
                                type: 'taxonomy',
                                param: _params
                            };
                            break;
                        case 'region':
                            let _param = parseInt(data);
                            if (lodash.indexOf([0, '0', ' '], _param) >= 0) {
                                this.ParamsFilter.region = {};
                                break;
                            }
                            this.ParamsFilter.region = {
                                props: 'region',
                                type: 'taxonomy',
                                param: _param
                            };
                            break;
                        case 'search':
                            if (data === '' || data === ' ') {
                                this.ParamsFilter.search = {};
                                break;
                            }
                            this.ParamsFilter.search = {
                                props: 'search',
                                param: data.trim()
                            };
                            break;
                        default:
                            break;
                    }
                    // Build request url
                    this.getRequest();
                },
                getRequest: function () {
                    const self = this;
                    this.requestHandler();
                    if (!lodash.isEmpty(this.ParamsFilter)) {
                        const pKeys = Object.keys(this.ParamsFilter);
                        pKeys.forEach((value) => {
                            let filter = self.ParamsFilter[value];
                            if (filter.type === 'taxonomy') {
                                self.Request.param(filter.props, filter.param);
                            }
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
                    self.loadArchive = false;
                    archivesPromise.then(function (response) {
                        if (lodash.isEmpty(response)) {
                            self.archives = [];
                            self.paging = null
                            return;
                        }
                        const archivesResponse = lodash.cloneDeep(response);
                        self.paging = lodash.clone(response._paging); // Update paging value
                        self.archives = lodash.map(archivesResponse, function (archive) {
                            archive.get_type_name = ''; // add type of annonce
                            archive.get_cat_name = '';
                            const type = archive.job_type;
                            if (lodash.isArray(type) || !lodash.isEmpty(type)) {
                                let i = lodash.head(type);
                                let j = lodash.find(self.taxonomies.Types, {id: parseInt(i)});
                                archive.get_type_name = j.name;
                            }
                            const categories = archive.categories;
                            if (lodash.isArray(categories) || !lodash.isEmpty(categories)) {
                                let k = lodash.head(categories);
                                let l = lodash.find(self.taxonomies.Categories, {id: parseInt(k)});
                                archive.get_cat_name = l.name;
                            }
                            return archive;
                        });
                        self.loadArchive = true;
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
                        Categories: []
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
                    const categoriesRequest = this.axiosInstance.get('categories?per_page=50');
                    const typesRequest = this.axiosInstance.get('job_type?per_page=50');
                    const salaryRequest = this.axiosInstance.get('salaries?per_page=50');
                    await axios.all([typesRequest, categoriesRequest, salaryRequest]).then(axios.spread(
                        (...responses) => {
                            self.Taxonomies.Categories = lodash.clone(responses[1].data);
                            self.Taxonomies.Types = lodash.clone(responses[0].data);
                            self.Taxonomies.Salaries = lodash.clone(responses[2].data);

                            self.loading = true;
                        }
                    )).catch(errors => {
                    })
                },
            }
        });
    });
})(jQuery);