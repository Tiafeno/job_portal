(function ($) {
    $().ready(function () {
        // Return random password
        const getRandomId = () => {
            const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            const string_length = 8;
            let randomstring = '';
            for (var i = 0; i < string_length; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                randomstring += chars.substring(rnum, rnum + 1);
            }
            return randomstring;
        };

        const Layout = {
            template: '#client-layout',
            data: function () {
                return {
                    Loading: false,
                    isLogged: false,
                    isCandidate: false,
                    isEmployer: false,
                    Client: null,
                    Wordpress: null,
                }
            },
            created: function () {
                if (typeof clientApiSettings === 'undefined') return;
                this.Wordpress = new WPAPI({
                    endpoint: clientApiSettings.root,
                    nonce: clientApiSettings.nonce
                });
                this.Wordpress.jobs = this.Wordpress.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                    // Listing any of these parameters will assign the built-in
                    // chaining method that handles the parameter:
                    params: ['context', 'per_page', 'offset', 'param']
                });
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    if (parseInt(clientApiSettings.current_user_id) == 0 || !clientApiSettings.current_user_id) {
                        this.isLogged = false
                        return false;
                    }
                    this.isLogged = true;
                    await this.Wordpress.users()
                        .context('edit')
                        .me()
                        .then(response => {
                            self.Client = lodash.clone(response);
                            // Check if is Candidate or Employer
                            if (lodash.indexOf(self.Client.roles, 'candidate') >= 0) {
                                this.isCandidate = true;
                            }
                            if (lodash.indexOf(self.Client.roles, 'employer') >= 0) {
                                this.isEmployer = true;
                            }
                            self.Loading = true;
                        });
                }
            }
        };
        const Home = {
            template: '<p>Home page</p>'
        };
        const CVComponents = {
            experience: {
                props: ['year_range', 'item'],
                template: '#experience-template',
            },
            education: {
                props: ['year_range', 'item'],
                template: '#education-template',
            }
        };
        const CVComp = {
            template: '#client-cv',
            components: {
                'comp-education': CVComponents.education,
                'comp-experience': CVComponents.experience
            },
            beforeRouteLeave (to, from, next) {
                const answer = window.confirm('Do you really want to leave? you have unsaved changes!')
                if (answer) {
                    next()
                } else {
                    next(false)
                }
            },
            data: function () {
                return {
                    hasCV: false,
                    city: '',
                    errors: [],
                    first_name: '',
                    last_name: '',
                    phone: '',
                    address: "",
                    gender: "",
                    birthday: "",
                    profil: "",
                    languages: [],
                    categories: [],

                    optLanguages: [],
                    optCategories: [],

                    currentUser: null,
                    Loading: true,
                    yearRange: [],
                    // Si la valeur est different de null, c'est qu'il a selectioner une liste a modifier
                    // Ne pas oublier de reinisialiser la valeur apres mise a jour
                    // Default value: null
                    formEduSelected: null,
                    formEduEdit: {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        desc: '',
                        b: '', /** begin year */
                        e: '' /** end year */
                    },
                    formExpSelected: null,
                    formExpEdit: {
                        _id: getRandomId(),
                        office: '',
                        enterprise: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '',
                        /** end year */
                        desc: '',
                    },
                    WPApiModel: null,
                    Emploi: null

                }
            },
            created: function () {
                let currentDate = new Date();
                this.yearRange = lodash.range(1950, currentDate.getFullYear());
            },
            mounted: async function () {
                const self = this;
                this.Loading = true;
                // this.WPApiModel = new wp.api.models.User({
                //     id: clientApiSettings.current_user_id
                // });
                // this.WPApiModel.fetch().done(function (response) {
                //     self.currentUser = lodash.cloneDeep(response);
                //     self.Loading = false;
                // });
                await this.$parent.Wordpress.users().me().context('edit').then(function(response) {
                    self.currentUser = lodash.cloneDeep(response);
                    //Populate data value
                    self.first_name = self.currentUser.first_name;
                    self.last_name = self.currentUser.last_name;
                    self.phone = self.currentUser.meta.phone;
                    self.address = self.currentUser.meta.address;
                    self.gender = self.currentUser.meta.gender;
                    self.birthday = self.currentUser.meta.birthday;
                    self.profil = self.currentUser.meta.profil;

                    let languages = self.currentUser.meta.languages;
                    languages = lodash.isEmpty(languages) ? [] : JSON.parse(languages);
                    self.languages = lodash.clone(languages);

                    let categories = self.currentUser.meta.categories;
                    categories = lodash.isEmpty(categories) ? [] : JSON.parse(categories);
                    self.categories = lodash.clone(categories);

                    self.hasCV = !!self.currentUser.meta.has_cv;

                    self.Loading = false;
                });

                // Education sortable list
                new Sortable(document.getElementById('education-list'), {
                    handle: '.edu-history', // handle's class
                    animation: 150,
                    // Element dragging ended
                    onEnd: function ( /**Event*/ evt) {
                        var itemEl = evt.item; // dragged HTMLElement
                        evt.to; // target list
                        evt.from; // previous list
                        evt.oldIndex; // element's old index within old parent
                        evt.newIndex; // element's new index within new parent
                        evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
                        evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements
                        evt.clone // the clone element
                        evt.pullMode; // when item is in another sortable: `"clone"` if cloning, `true` if moving
                        console.log(evt);
                    },
                });

                // Recuperer les langues
                fetch(clientApiSettings.root + 'wp/v2/language').then(res => {
                    res.json().then(json => (self.optLanguages = json));
                });

                // Recuperer les langues
                fetch(clientApiSettings.root + 'wp/v2/categories').then(res => {
                    res.json().then(json => (self.optCategories = json));
                });


            },
            computed: {
                getExperiences() {
                    let experiences = this.getMeta('experiences');
                    let response = lodash.isEmpty(experiences) ? [] : JSON.parse(experiences);
                    return response;
                },
                getEducations() {
                    let educations = this.getMeta('educations');
                    let response = lodash.isEmpty(educations) ? [] : JSON.parse(educations);
                    return response;
                },
            },
            methods: {
                getMeta: function (value) {
                    let metaValue = lodash.isNull(this.currentUser) ? JSON.stringify([]) :
                        (typeof this.currentUser.meta == 'undefined' ? JSON.stringify([]) : this.currentUser.meta[value]);
                    return metaValue;
                },
                updateExperiences: function (data) {
                    const self = this;
                    this.Loading = true;
                    this.$parent.Wordpress.users().me().update({
                        meta: {
                            experiences: JSON.stringify(data)
                        }
                    }).then(function (response) {
                        self.currentUser = lodash.clone(response);
                        /** reset experience form value to default */
                        self.resetExperience();
                        self.Loading = false;
                        $('.modal').modal('hide');
                    }).catch(function (err) {
                        self.Loading = false;
                    });
                },
                updateEducations: function(data) {
                    const self = this;
                    this.Loading = true;
                    this.$parent.Wordpress.users().me().update({
                        meta: {
                            educations: JSON.stringify(data)
                        }
                    }).then(function (response) {
                        self.currentUser = lodash.clone(response);
                        /** reset experience form value to default */
                        self.resetEducation();
                        self.Loading = false;
                        $('.modal').modal('hide');
                    }).catch(function (err) {
                        self.Loading = false;
                    });
                },
                resetExperience: function () {
                    this.formExpEdit = {
                        _id: getRandomId(),
                        office: '',
                        enterprise: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '',
                        /** end year */
                        desc: '',
                    };
                    this.formExpSelected = null;
                },
                resetEducation: function() {
                    this.formEduEdit = {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        b: '', /** begin year */
                        e: '' /** end year */
                    };
                    this.formEduSelected = null;
                },
                /** Envt click button modal */
                addExperience: function () {
                    this.resetExperience();
                    $('#experience').modal('show');
                },
                addEducation: function() {
                    this.resetEducation();
                    $('#education').modal('show');
                },
                editExperience: function (evt, id) {
                    evt.preventDefault();
                    const self = this;
                    const experiences = this.getExperiences;
                    let expSelected = lodash.find(experiences, exp => exp._id === id);
                    Object.keys(expSelected).forEach((item, index) => {
                        self.formExpEdit[item] = expSelected[item];
                    });
                    this.formExpSelected = id;
                    $('#experience').modal('show');
                },
                deleteExperience: function(evt, id) {
                    const experiences = this.getExperiences;
                    let currentExperiences = lodash.remove(experiences, exp => {
                        return exp._id === id;
                    });
                    this.updateExperiences(currentExperiences);
                },
                deleteEducation: function(evt, id) {
                    const educations = this.getEducations;
                    let currentEducations = lodash.remove(educations, edu => {
                        return edu._id === id;
                    });
                    this.updateEducations(currentEducations);
                },
                editEducation: function (evt, id) {
                    evt.preventDefault();
                    const self = this;
                    const educations = this.getEducations;
                    let eduSelected = lodash.find(educations, {_id: id});
                    Object.keys(eduSelected).forEach((item, index) => {
                        self.formEduEdit[item] = eduSelected[item];
                    });
                    this.formEduSelected = id;
                    $('#education').modal('show');
                },
                updateStatusCandidate: function() {

                },
                validateExpForm: function (ev) {
                    ev.preventDefault();
                    this.submitExpForm();
                },
                validateEduForm: function(ev) {
                    ev.preventDefault();
                    this.submitEduForm();
                },
                submitExpForm: function () {
                    const self = this;
                    let experiences = this.getExperiences;
                    if (this.formExpSelected === null) {
                        experiences.push(this.formExpEdit);
                    } else {
                        /** update exist experience */
                        experiences = lodash.map(experiences, exp => {
                            if (exp._id === self.formExpSelected) {
                                Object.keys(exp).forEach((expKey) => {
                                    exp[expKey] = self.formExpEdit[expKey];
                                });
                            }
                            return exp;
                        });
                    }
                    this.updateExperiences(experiences);
                },
                submitEduForm: function() {
                    const self = this;
                    let educations = this.getEducations;
                    if (this.formEduSelected === null) {
                        educations.push(this.formEduEdit);
                    } else {
                        /** update exist experience */
                        educations = lodash.map(educations, edu => {
                            if (edu._id === self.formEduSelected) {
                                Object.keys(edu).forEach((key) => {
                                    edu[key] = self.formEduEdit[key];
                                });
                            }
                            return edu;
                        });
                    }
                    this.updateEducations(educations);
                },
                submitCV: function(ev) {
                    ev.preventDefault();
                    const self = this;
                    this.errors = [];

                    // TODO: Verify error input form
                    this.Loading = true;
                    let _languages = JSON.stringify(this.languages);
                    let _categories = JSON.stringify(this.categories);
                    let userId = parseInt(clientApiSettings.current_user_id);
                    this.$parent.Wordpress.users().me()
                        .update({
                            last_name: this.last_name,
                            first_name: this.first_name,
                            meta: {
                                phone: this.phone,
                                address: this.address,
                                gender: this.gender,
                                city: this.city,
                                languages: _languages,
                                categories: _categories,
                                birthday: this.birthday,
                                reference: `CV${userId}`,
                                profil: this.profil,
                                // Render visible this CV
                                has_cv: true,
                            }
                        })
                        .then(function(resp) {
                            self.Loading = false;
                            self.hasCV = true;
                        })
                        .catch(function(er) {
                            self.Loading = false;
                        });
                }
            }
        };
        const AnnonceComp = {
            template: "#client-annonce",
            data: function () {
                return {
                    loading : false,
                    annonces: []
                }
            },
            mounted: function(){
                const self = this;
                this.loading = true;
                this.$parent.Wordpress.jobs()
                    .param('meta_key', 'employer_id')
                    .param('meta_value', clientApiSettings.current_user_id)
                    .per_page(10)
                    .then(function(response) {
                        console.log(response);
                        self.annonces = lodash.clone(response);
                        self.loading = false;
                });
            }
        };
        const AnnonceDetails = {
            template: "#annonce-apply",
            data: function() {
                return {
                    loading : false,
                    job: null,
                    candidateApply : [],
                    jobAxiosInstance: null
                }
            },
            mounted: function() {
                const self = this;
                this.jobAxiosInstance = axios.create({
                    baseURL: clientApiSettings.root + 'job/v2',
                    headers: {'X-WP-Nonce': clientApiSettings.nonce}
                });
                this.loading = true;
                const job_id = this.$route.params.id;
                self.jobAxiosInstance.get(`details/${job_id}`).then(function(response) {
                    const details = response.data;
                    if (details.success) {
                        self.candidateApply = lodash.map(details.data.candidate, candidate => {
                            candidate.link = clientApiSettings.page_candidate + '#/candidate/' + candidate.id;
                            return candidate;
                        });
                        self.job = lodash.clone(details.data.job);
                    }
                    self.loading = false;
                }).catch(function() {
                    self.loading = false;
                })
            },
            computed: {
            }
        }
        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/home',
                children: [
                    { path: 'home', name: 'Home', component: Home },
                    {
                        path: 'cv',
                        name: 'CV',
                        component: CVComp,
                    },
                    {
                        path: 'jobs',
                        name: 'Annonce',
                        component: AnnonceComp,
                    },
                    {
                        path: 'job/:id/details',
                        name: 'AnnonceDetails',
                        component: AnnonceDetails
                    }
                ],
                beforeEnter: (to, from, next) => {

                },
            },
            {
                path: '/login',
                name: 'Login',
                component: CompLogin,
                beforeEnter: (to, from, next) => {
                    if (parseInt(clientApiSettings.current_user_id) !== 0) next({
                        name: 'Home'
                    })
                    else next();
                },
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });

        Vue.component('v-select', VueSelect.VueSelect);
        // Application
        new Vue({
            el: '#client',
            router
        });

    });
})(jQuery);