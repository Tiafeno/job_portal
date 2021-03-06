(function ($, _) {
    $().ready(function () {
        const jobGrid = {
            template: "#job-grid-template",
            props: ['item', 'taxonomies'],
            data: function () {
                return {
                    defaultAvatarSrc: ''
                }
            },
            created: function () {
                this.defaultAvatarSrc = this.item.company.avatar_urls[96];
            },
            computed: {
                avatarSrc: function() {
                    const company = this.item.company;
                    const avatar = company.avatar;
                    return _.isEmpty(avatar) ? this.defaultAvatarSrc : avatar.upload_dir.baseurl + '/' + avatar.image.file;
                }
            },
            filters: {
                jobType: function (value, Tax) {
                    if (!lodash.isArray(value)) return '';
                    var firstValue = value[0];
                    var result = _.find(Tax.Types, {id: parseInt(firstValue)});
                    return result.name;
                },
                jobCategories: function (value, Tax) {
                    if (!lodash.isArray(value)) return '';
                    var firstValue = value[0];
                    var result = _.find(Tax.Categories, {id: parseInt(firstValue)});
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
        wp.api.loadPromise.done( () => {
            new Vue({
                el: '#job-grid',
                components: {
                    'job-grid': jobGrid,
                },
                data: function () {
                    return {
                        loading: false,
                        Taxonomies: {},
                        EmploiCollection: null,
                        axiosInstance: null,
                        itemsCount: 8,
                        moreClickCount: 0,
                        jobs: [],
                    }
                },
                created: function () {
                    if (typeof apiSettings === 'undefined') {
                        return;
                    }
                    this.axiosInstance = axios.create({
                        baseURL: apiSettings.root + 'wp/v2',
                        headers: {'X-WP-Nonce': apiSettings.nonce}
                    });
                    this.init();
                },
                methods: {
                    init: async function () {
                        const self = this;
                        this.loading = true;
                        const categoriesRequest = this.axiosInstance.get('categories?per_page=50');
                        const typesRequest = this.axiosInstance.get('job_type?per_page=50');
                        await axios.all([typesRequest, categoriesRequest]).then(axios.spread(
                            (...responses) => {
                                self.Taxonomies.Categories = lodash.clone(responses[1].data);
                                self.Taxonomies.Types = lodash.clone(responses[0].data);
                            }
                        )).catch(errors => {
                        })
                        this.EmploiCollection = new wp.api.collections.Emploi();
                        this.EmploiCollection.fetch({
                            data: {
                                per_page: this.itemsCount,
                                orderby: 'date',
                                order: 'desc'
                            }
                        }).then(resp => {
                            this._build(resp);
                            this.loading = false;
                        })
                    },
                    _build: function(jobs) {
                        this.jobs = _.map(jobs, job => {
                            const title = job.title.rendered;
                            job.title.rendered = _.truncate(title, {length: 30, separator: ' '});
                            return job;
                        });
                    },
                    moreEmploi: function (ev) {
                        if (this.moreClickCount >= 2 || !this.EmploiCollection.hasMore()) {
                            return true;
                        }
                        ev.preventDefault();
                        if (this.EmploiCollection.hasMore()) {
                            this.loading = true;
                            this.EmploiCollection.more().then(resp => {
                                const jobs = this.jobs.concat(resp);
                                this._build(jobs);
                                this.loading = false;
                            });
                        }
                        this.moreClickCount += 1;
                        return false;
                    }
                },
                delimiters: ['${', '}']
            });
        })
    });

})(jQuery, lodash);