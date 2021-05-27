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

            },
            methods: {
                selectedFilter: function($event) {
                    $event.preventDefault();
                    const target = $event.target;
                    let values = [];

                    // Get all input selected
                    const inputChecked = $('input:checked[name="salarie"]');
                    inputChecked.each(function(index, el) {
                        values.push($(el).val());
                    });

                    this.$emit('changed', values, 'salaries');
                }
            },
            filters: {
            },
            watch: {
                salaries: function() {
                    if (lodash.isArray(this.salaries)) {
                        this.items = lodash.map(this.salaries, function(item) {
                            return { name: 'Under ' + item.name, value: item.name,  id: item.id };
                        });
                    }
                    return this.salaries;
                }
            }
        };
        const searchFilter = {
            template: '#filter-search-template',
            data: function () {
                return {}
            },
            methods: {
                searchKey: function(ev) {
                    ev.preventDefault();
                    const el = ev.target;
                    if (ev.keyCode === 13) { // Enter press...
                        const elValue = $(el).val()
                        this.$emit('changed', elValue, 'search');
                    }
                }
            }
        };

        const archiveJobs = {
            template: "#job-archive-template",
            props: ['taxonomies'],
            components: {
                'filter-salary': salaryFilter,
                'filter-search': searchFilter
            },
            data: function () {
                return {
                    loading: false,
                    archives: [], // content
                    WPAPI: null,
                    Request: {}, // object request
                    ParamsFilter: {}, // for all search filter
                    paging: null, // content pagination
                    per_page: 10, // per page default value
                    // node api params
                    _context: 'view',
                    _page: 1,
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
                    const self = this;
                    this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                        params: ['before', 'after', 'author', 'per_page', 'offset', 'context', 'param', 'search','filter']
                    });
                    this.loading = true;
                    this.requestHandler();
                    this.Request.per_page().then(function (jobsResponse) {
                        self.loading = false;
                        self.archives = lodash.clone(jobsResponse);
                        self.paging = jobsResponse._paging;
                    });
                },
                requestHandler: function () {
                    return this.Request = lodash.cloneDeep(this.WPAPI.jobs());
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
                getRequest: function() {
                    const self = this;
                    this.requestHandler();
                    if (!lodash.isEmpty(this.ParamsFilter)) {
                        const pKeys = Object.keys(this.ParamsFilter);
                        pKeys.forEach((value) => {
                            let filter = self.ParamsFilter[ value ];
                            if (filter.type === 'taxonomy') {
                                self.Request.param(filter.props, filter.param);
                            }
                            if (filter.props === 'search') {
                                self.Request.search(filter.param);
                            }
                        });
                    }

                    self.archives = self.Request.per_page(this.per_page).get();
                }
            },
            filters: {
                jobTypeName: function (value, taxonomies) {
                    if (!lodash.isArray(value)) return '';
                    var firstValue = value[0];
                    console.log(taxonomies);
                    var result = lodash.find(taxonomies.Types, {id: parseInt(firstValue)});
                    return result.name;
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
                        }
                    )).catch(errors => {
                    })
                },
            },
            delimiters: ['${', '}']
        });
    });
})(jQuery);