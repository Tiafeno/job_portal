jQuery(function ($) {
    $().ready(() => {
        const __compCandidate = {
            delimiters: ['${', '}'],
            template: "#comp-candidate-template",
            props: ['u'],
            data: function() {
                return {
                    loading: false,
                    candidate: null,
                    ptLanguages: [],
                    ptCategories: [],
                    form: {
                        address: '',
                        city: '',
                        hasCV: false,
                        isActive: false,
                        languages: [],
                        categories: [],
                        experiences: [],
                        educations: [],
                        profil: ''
                    }
                }
            },
            computed: {
                Experiences: function() {
                    const experiences = this.u.experiences;
                    return experiences ? experiences : [];
                },
                Educations: function() {
                    const educations = this.u.educations;
                    return educations ? educations : [];
                }
            },
            created: function() {
                this.candidate = _.clone(this.u);
                this._buildForm();
            },
            mounted: function() {
                $('.ui.dropdown')
                    .dropdown({
                        clearable: true,
                        placeholder: 'any'
                    });
            },
            methods: {
                _buildForm: function() {
                    // Create form with builder javascript library
                    this.axiosInstance = axios.create({
                        baseURL: WPAPIUserSettings.root + 'wp/v2',
                        headers: {
                            'X-WP-Nonce': WPAPIUserSettings.nonce
                        }
                    });
                    const categoriesRequest = this.axiosInstance.get('categories?per_page=80&hide_empty=false');
                    const languagesRequest = this.axiosInstance.get('language?per_page=50');
                    const regionRequest = this.axiosInstance.get('region?per_page=50');
                    this.loading = true;
                    axios.all([languagesRequest, categoriesRequest, regionRequest]).then(axios.spread(
                        (...responses) => {
                            this.ptCategories = responses[1].data;
                            this.ptLanguages = responses[0].data;
                            this.loading = false;
                        }
                    )).catch(errors => { });
                    // populate form
                    this.form.address = this.u.meta.address;
                    this.form.city = this.u.meta.city;
                    this.form.hasCV = this.u.has_cv;
                    this.form.isActive = this.u.is_active;

                    let currentLanguages = this.u.meta.languages;
                    currentLanguages = lodash.isEmpty(currentLanguages) ? [] : JSON.parse(currentLanguages);
                    this.form.languages = lodash.map(currentLanguages, langue => parseInt(langue));

                    let categories = this.u.meta.categories;
                    categories = lodash.isEmpty(categories) ? [] : JSON.parse(categories);
                    this.form.categories = lodash.map(categories, cat => parseInt(cat));

                    this.form.profil = this.u.meta.profil;
                },
                submitForm: function(ev) {
                    ev.preventDefault();
                    this.loading = true;
                    this.$parent.wpapi.users().id(WPAPIUserSettings.uId).update({
                        is_active: this.form.isActive ? 1 : 0,
                        meta: {
                            city: this.form.city,
                            address: this.form.address,
                            languages: JSON.stringify(this.form.languages),
                            categories: JSON.stringify(this.form.categories)
                        }
                    }).then(user => {
                        console.log(user);
                        this.loading = false;
                    })
                }
            }
        };
        const __compCompany = {
            props: ['u'],
            template: "#comp-company-template",
            data: function() {
                return {
                    loading: false,
                    employer: null,
                    form: {
                        address: '',
                        city: '',
                        stat: '',
                        nif: '',
                        isActive: false,
                    }
                }
            },
            created: function() {
                this.employer = _.clone(this.u);
                this._buildForm();
            },
            mounted: function() {
                $('.ui.dropdown')
                    .dropdown({
                        clearable: true,
                        placeholder: 'any'
                    });
            },
            methods: {
                _buildForm: function() {
                    // Create form with builder javascript library
                    // populate form
                    this.form.address = this.u.meta.address;
                    this.form.city = this.u.meta.city;
                    this.form.isActive = this.u.is_active;
                    this.form.nif = this.u.meta.nif;
                    this.form.stat = this.u.meta.stat;
                },
                submitForm: function(ev) {
                    ev.preventDefault();
                    this.loading = true;
                    this.$parent.wpapi.users().id(WPAPIUserSettings.uId).update({
                        is_active: this.form.isActive ? 1 : 0,
                        meta: {
                            city: this.form.city,
                            address: this.form.address,
                            nif: this.form.nif,
                            stat: this.form.Stat
                        }
                    }).then(user => {
                        console.log(user);
                        this.loading = false;
                    })
                }
            }
        };
        // Wordpress api is loaded!
        wp.api.loadPromise.done(() => {
            new Vue({
                el: '#user_app',
                components: {
                    'comp-candidate': __compCandidate,
                    'comp-company': __compCompany
                },
                data: function () {
                    return {
                        loading: false,
                        userRole: null,
                        user: null,
                        wpapi: new WPAPI({
                            endpoint: WPAPIUserSettings.root,
                            nonce: WPAPIUserSettings.nonce
                        }),
                    }
                },
                created: function () {
                    this.init();
                },
                methods: {
                    init: function () {
                        this.loading = true;
                        const user = new wp.api.models.User({id: WPAPIUserSettings.uId});
                        user.fetch({data: {context: 'edit'}}).then(resp => {
                            this.user = resp;
                            this.userRole = _.first(resp.roles);
                            this.loading = false;
                        });
                    },
                }
            });
        });
    });
});