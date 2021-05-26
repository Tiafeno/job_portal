(function($) {
    $().ready(function () {
        const salaryFilter = {
            props: ['salaries'],
            template: '#filter-salary-template',
            data: function() {
                return {
                    items: []
                }
            },
            created: function() {
                $itemValues = lodash.map(this.salaries, item => parseInt(item.name, 10));
                this.items = _.chunk($itemValues, 2);
            },
            methods: function() {

            },
            filters: {
                priceRange: function(salary) {

                }
            }
        };
        const searchFilter = {
            template: '#filter-search-template',
            data: function() {
                return {

                }
            }
        };

        const archiveJobs = {
            template: "#job-archive-template",
            props: ['taxonomies'],
            components:{
                'filter-salary': salaryFilter,
                'filter-search': searchFilter
            },
            data: function () {
                return {
                    loading: false,
                    archives: [],
                    WPAPI: null,
                    Request: {},
                    params: null,
                    paging: null,
                    // node api params
                    _context: 'view',
                    _per_page: 10,
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
                init: function() {
                    const self = this;
                    this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                        params: ['before', 'after', 'author', 'per_page', 'offset', 'context', 'search']
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
                }
            },
            filters: {
                jobTypeName: function(value, taxonomies) {
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
                    )).catch(errors => { })
                },
            },
            delimiters: ['${', '}']
        });
    });
})(jQuery);