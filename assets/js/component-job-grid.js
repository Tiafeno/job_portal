(function ($, _) {
    $().ready(function () {
        const jobGrid = {
            template: "#job-grid-template",
            props: ['item', 'taxonomies'],
            data: function () {
                return {}
            },
            created: function () {

            },
            mounted: function () {

            },
            methods: {},
            filters: {
                jobTypeName: function(value, taxonomies) {
                    if (!lodash.isArray(value)) return '';
                    var firstValue = value[0];
                    console.log(taxonomies);
                    var result = lodash.find(taxonomies.Types, {id: parseInt(firstValue)});
                    return result.name;
                },
                capitalize: function (value) {
                    if (!value) return '';
                    value = value.toString()
                    return value.charAt(0).toUpperCase() + value.slice(1)
                }
            },
            delimiters: ['${', '}']
        };

        // Application
        new Vue({
            el: '#job-grid',
            components: {
                'job-grid': jobGrid,
            },
            data: function () {
                return {
                    loading: false,
                    Taxonomies: {},
                    axiosInstance: null,
                    itemsCount: 8,
                    jobs: [],
                    Me: {},
                    WPAPI: null,

                }
            },
            created: function () {
                if (typeof apiSettings === 'undefined') {
                    return;
                }
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    this.WPAPI = new WPAPI({
                        endpoint: apiSettings.root,
                        nonce: apiSettings.nonce
                    });
                    this.axiosInstance = axios.create({
                        baseURL: apiSettings.root + 'wp/v2',
                        headers: {
                            'X-WP-Nonce': apiSettings.nonce
                        }
                    });
                    const categoriesRequest = this.axiosInstance.get('categories?per_page=50');
                    const typesRequest = this.axiosInstance.get('job_type?per_page=50');
                    await axios.all([typesRequest, categoriesRequest]).then(axios.spread(
                        (...responses) => {
                            self.Taxonomies.Categories = lodash.clone(responses[1].data);
                            self.Taxonomies.Types = lodash.clone(responses[0].data);
                        }
                    )).catch(errors => { })

                    this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                        params: ['before', 'after', 'author', 'per_page', 'offset', 'context', 'search']
                    });
                    this.loading = true;
                    this.WPAPI.jobs().per_page(this.itemsCount).then(function (jobsResponse) {
                        self.loading = false;
                        self.jobs = lodash.clone(jobsResponse);
                        console.log(jobsResponse);
                    });
                },


            },
            delimiters: ['${', '}']
        });
    });

})(jQuery, lodash);