(function ($) {
    $().ready(function () {
        Vue.component('v-select', VueSelect.VueSelect);
        Vue.filter('jobStatus', function (value) {
            if (!value) return 'Inconnue'
            value = value.toString()
            return value === 'pending' ? 'En attente de validation' : (value === 'private' ? 'Supprimer' : 'Publier');
        });
        Vue.filter('cvStatus', function (user) {
            if (!user) return 'Inconnue';
            const isPublic = user.meta.public_cv; // boolean
            const hasCV = user.meta.has_cv; // boolean
            if (!hasCV) return "Indisponible";
            return isPublic ? "Publier" : "En attent de validation";

        });
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
                    params: ['context', 'per_page', 'offset', 'param', 'status']
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
                            this.isCandidate = lodash.indexOf(self.Client.roles, 'candidate') >= 0;
                            this.isEmployer = lodash.indexOf(self.Client.roles, 'employer') >= 0;
                            self.Loading = true;
                        });
                }
            }
        };
        const EditPassword = {
            template: '#edit-password-template',
            data: function () {
                return {
                    loading: false,
                    validators: [],
                    pwd: '',
                    pwd_conf: '',
                }
            },
            methods: {
                errorHandler: function (item) {
                    this.validators.push(item);
                },
                submitNewPassword: function (ev) {
                    ev.preventDefault();
                    this.validators = [];
                    var self = this;
                    if (lodash.isEmpty(this.pwd) || lodash.isEmpty(this.pwd_conf)) {
                        this.errorHandler("Veuillez remplire correctement les champs requis");
                    }
                    if (this.pwd !== this.pwd_conf) {
                        this.errorHandler("Les deux (2) mot de passe ne sont pas identique");
                    }
                    if (!lodash.isEmpty(this.validators)) {
                        return;
                    }
                    var form = new FormData();
                    form.append('action', 'change_my_pwd');
                    form.append('pwd', this.pwd);
                    form.append('pwd_nonce', clientApiSettings.nonce_form);
                    this.loading = true;
                    axios.post(clientApiSettings.ajax_url, form).then(function (resp) {
                        var response = resp.data;
                        if (response.success) {
                            alertify.alert('information', response.data, function () {
                                window.location.reload();
                            });
                        }
                    }).catch(function (err) {
                    }).done(function () {
                        self.loading = false;
                    })

                }
            }
        };
        /**
         * Cette composant permet de modifier le profil
         *
         * @type {{
         * template: string, data: (
         *  function(): {
         *      currentUser: null,
         *      validators: [],
         *      isCandidate: boolean,
         *      isEmployer: boolean,
         *      currentUserCompany: null
         * }),
         *  methods: {
         *      init: (function(): Promise<void>),
         *      profilHandler: ProfilEdit.methods.profilHandler,
         *      submitProfil: ProfilEdit.methods.submitProfil},
         *      mounted: ProfilEdit.mounted }
         *  }
         */
        const ProfilEdit = {
            template: "#profil-client-template",
            data: function () {
                return {
                    validators: [],
                    isCandidate: false,
                    isEmployer: false,
                    currentUser: null,
                    currentUserCompany: null,
                }
            },
            mounted: function () {
                this.init();
            },
            methods: {
                submitProfil: function (ev) {
                    ev.preventDefault();
                },
                init: async function () {
                    const self = this;
                    this.loading = true;
                    var currentUsr = await this.$parent.$parent.Wordpress.users().context('edit').me().get();
                    self.currentUser = lodash.clone(currentUsr);
                    // Check if is company
                    if (lodash.indexOf(currentUsr.roles, 'employer') >= 0) {
                        this.isEmployer = true;
                        var companyId = currentUsr.meta.company_id;
                        var CompanyModel = new wp.api.models.User({id: parseInt(companyId)});
                        CompanyModel.fetch({data: {context: 'edit'}}).done(function (companyResponse) {
                            self.currentUserCompany = lodash.clone(companyResponse);
                            self.loading = false;
                        });
                    } else {
                        this.isCandidate = true;
                    }
                    self.profilHandler();
                },
                profilHandler: function () {

                }
            }
        };
        const Home = {
            template: '#dashboard',
            components: {
                'comp-edit-pwd': EditPassword,
                'comp-edit-profil': ProfilEdit
            },
            data: function () {
                return {
                    loading: false,
                }
            },
            methods: {}
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
            beforeRouteLeave(to, from, next) {
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
                    publicCV: false,
                    errors: [],
                    first_name: '',
                    last_name: '',
                    phone: '',
                    address: "",
                    city: '',
                    region: 0,
                    gender: "",
                    birthday: "",
                    profil: "", // Biographie
                    languages: [],
                    categories: [],

                    optLanguages: [],
                    optCategories: [],
                    optRegions: [],

                    currentUser: null,
                    Loading: true,
                    yearRange: [],
                    // Si la valeur est different de null, c'est qu'il a selectioner une liste a modifier
                    // Ne pas oublier de reinisialiser la valeur apres mise a jour
                    // Default value: null
                    eduValidator: [],
                    formEduSelected: null,
                    formEduEdit: {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        desc: '',
                        b: '',
                        /** begin year */
                        e: '' /** end year */
                    },
                    expValidator: [],
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
                await this.$parent.Wordpress.users().me().context('edit').then(function (response) {
                    self.currentUser = lodash.cloneDeep(response);
                    //Populate data value
                    self.first_name = self.currentUser.first_name;
                    self.last_name = self.currentUser.last_name;
                    self.phone = self.currentUser.meta.phone;
                    self.address = self.currentUser.meta.address;
                    self.gender = self.currentUser.meta.gender;
                    self.city = self.currentUser.meta.city;
                    self.birthday = self.currentUser.meta.birthday;
                    self.profil = self.currentUser.meta.profil;
                    self.region = self.currentUser.meta.region;

                    let languages = self.currentUser.meta.languages;
                    languages = lodash.isEmpty(languages) ? [] : JSON.parse(languages);
                    self.languages = lodash.clone(languages);

                    let categories = self.currentUser.meta.categories;
                    categories = lodash.isEmpty(categories) ? [] : JSON.parse(categories);
                    self.categories = lodash.clone(categories);

                    self.hasCV = !!self.currentUser.meta.has_cv;
                    self.publicCV = !!self.currentUser.meta.public_cv;

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
                fetch(clientApiSettings.root + 'wp/v2/language?per_page=50').then(res => {
                    res.json().then(json => (self.optLanguages = json));
                });

                // Recuperer les categories
                fetch(clientApiSettings.root + 'wp/v2/categories?per_page=50').then(res => {
                    res.json().then(json => (self.optCategories = json));
                });

                // Recuperer les items de region
                fetch(clientApiSettings.root + 'wp/v2/region?per_page=50').then(res => {
                    res.json().then(json => (self.optRegions = json));
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
                errorHandler: function (field) {
                    return `Le champ <b>"${field}"</b> est obligatoire`;
                },
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
                updateEducations: function (data) {
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
                resetEducation: function () {
                    this.formEduEdit = {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '' /** end year */
                    };
                    this.formEduSelected = null;
                },
                /** Envt click button modal */
                addExperience: function () {
                    this.resetExperience();
                    $('#experience').modal('show');
                },
                addEducation: function () {
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
                deleteExperience: function (evt, id) {
                    evt.preventDefault();
                    const experiences = this.getMeta('experiences');
                    let currentExperiences = lodash.remove(experiences, exp => {
                        return exp._id === id;
                    });
                    this.updateExperiences(currentExperiences);
                },
                deleteEducation: function (evt, id) {
                    evt.preventDefault();
                    const educations = this.getMeta('educations');
                    let currentEducations = lodash.remove(educations, edu => {
                        return edu._id === id;
                    });
                    this.updateEducations(currentEducations);
                },
                editEducation: function (evt, id) {
                    evt.preventDefault();
                    const self = this;
                    const educations = this.getEducations;
                    let eduSelected = lodash.find(educations, {
                        _id: id
                    });
                    Object.keys(eduSelected).forEach((item, index) => {
                        self.formEduEdit[item] = eduSelected[item];
                    });
                    this.formEduSelected = id;
                    $('#education').modal('show');
                },
                validateExpForm: function (ev) {
                    ev.preventDefault();
                    this.expValidator = [];
                    const form = this.formExpEdit;
                    if (lodash.isEmpty(form.office)) {
                        this.expValidator.push(this.errorHandler('Poste'));
                    }
                    if (lodash.isEmpty(form.enterprise)) {
                        this.expValidator.push(this.errorHandler('Entreprise'));
                    }
                    if (lodash.isEmpty(form.city)) {
                        this.expValidator.push(this.errorHandler('Ville'));
                    }
                    if (lodash.isEmpty(form.country)) {
                        this.expValidator.push(this.errorHandler('Pays'));
                    }
                    if (!form.b) {
                        this.expValidator.push(this.errorHandler('Année de début'))
                    }
                    if (!lodash.isEmpty(this.expValidator)) {
                        return;
                    }
                    this.submitExpForm();
                },
                validateEduForm: function (ev) {
                    ev.preventDefault();
                    this.eduValidator = [];
                    const form = this.formEduEdit;
                    if (lodash.isEmpty(form.city)) {
                        this.eduValidator.push(this.errorHandler('Ville'));
                    }
                    if (lodash.isEmpty(form.country)) {
                        this.eduValidator.push(this.errorHandler('Pays'))
                    }
                    if (lodash.isEmpty(form.diploma)) {
                        this.eduValidator.push(this.errorHandler('Diplôme'))
                    }
                    if (lodash.isEmpty(form.establishment)) {
                        this.eduValidator.push(this.errorHandler('Etablissement'))
                    }
                    if (!form.b) {
                        this.eduValidator.push(this.errorHandler('Année de début'))
                    }
                    if (!lodash.isEmpty(this.eduValidator)) {
                        return;
                    }
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
                submitEduForm: function () {
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
                submitCV: function (ev) {
                    ev.preventDefault();
                    const self = this;
                    let experiences = this.getMeta('experiences');
                    let educations = this.getMeta('educations');
                    this.errors = [];
                    if (lodash.isEmpty(this.languages)) {
                        this.errors.push(this.errorHandler('Langue'));
                    }
                    if (lodash.isEmpty(this.categories)) {
                        this.errors.push(this.errorHandler('Emploi recherché ou métier'));
                    }
                    if (lodash.isEmpty(this.gender)) {
                        this.errors.push(this.errorHandler('Genre'));
                    }
                    if (lodash.isEmpty(this.address)) {
                        this.errors.push(this.errorHandler('Adresse'));
                    }
                    if (!this.region || this.region === 0 || this.region == '0') {
                        this.errors.push(this.errorHandler('Region'));
                    }
                    if (lodash.isEmpty(this.city)) {
                        this.errors.push(this.errorHandler('Ville'));
                    }
                    // Verifier s'il y a une experience et education au minimum
                    let msgExperienceEmpty = "Ajoutez au moins une experience dans votre CV";
                    if (lodash.isEmpty(experiences)) {
                        this.errors.push(msgExperienceEmpty);
                    } else {
                        experiences = JSON.parse(experiences);
                        if (lodash.isEmpty(experiences)) {
                            this.errors.push(msgExperienceEmpty);
                        }
                    }
                    let msgEducationEmpty = "Ajoutez au moins un parcour à votre CV";
                    if (lodash.isEmpty(educations)) {
                        this.errors.push(msgEducationEmpty);
                    } else {
                        educations = JSON.parse(educations);
                        if (lodash.isEmpty(educations)) {
                            this.errors.push(msgEducationEmpty);
                        }
                    }

                    if (!lodash.isEmpty(this.errors)) {
                        return false;
                    }
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
                                region: this.region,
                                city: this.city,
                                languages: _languages,
                                categories: _categories,
                                birthday: this.birthday,
                                reference: `CV${userId}`,
                                profil: this.profil,
                                // Render visible this CV
                                has_cv: true,
                                public_cv: this.publicCV
                            }
                        })
                        .then(function (resp) {
                            alertify.notify('Enregistrer avec succès', 'success', 5, function () {
                                self.Loading = false;
                                self.hasCV = true;
                            });
                        })
                        .catch(function (er) {
                            self.Loading = false;
                        });
                }
            }
        };
        const AnnonceComp = {
            template: "#client-annonce",
            data: function () {
                return {
                    loading: false,
                    annonces: []
                }
            },
            mounted: function () {
                this.Populate();
            },
            methods: {
                trashAnnonce: function (ev, jobId) {
                    ev.preventDefault();
                    var self = this;
                    alertify.confirm("Voulez vous vraiment supprimer cette annonce. ID: " + jobId, function () {
                            self.loading = true;
                            self.$parent.Wordpress.jobs().id(jobId).update({
                                status: 'private'
                            }).then(function () {
                                self.Populate();
                            });
                        },
                        function () {

                        });
                },
                Populate: function () {
                    const self = this;
                    this.loading = true;
                    this.$parent.Wordpress.jobs()
                        .status(['pending', 'publish', 'private'])
                        .param('meta_key', 'employer_id')
                        .param('meta_value', clientApiSettings.current_user_id)
                        .per_page(10)
                        .then(function (response) {
                            self.annonces = lodash.clone(response);
                            self.loading = false;
                        });
                }
            }
        };
        const AnnonceDetails = {
            template: "#annonce-apply",
            data: function () {
                return {
                    loading: false,
                    job: null,
                    candidateApply: [],
                    jobAxiosInstance: null
                }
            },
            mounted: function () {
                const self = this;
                this.jobAxiosInstance = axios.create({
                    baseURL: clientApiSettings.root + 'job/v2',
                    headers: {
                        'X-WP-Nonce': clientApiSettings.nonce
                    }
                });
                this.loading = true;
                const job_id = this.$route.params.id;
                self.jobAxiosInstance.get(`details/${job_id}`).then(function (response) {
                    const details = response.data;
                    if (details.success) {
                        self.candidateApply = lodash.map(details.data.candidates, candidate => {
                            candidate.link = clientApiSettings.page_candidate + '#/candidate/' + candidate.id;
                            return candidate;
                        });
                        self.job = lodash.clone(details.data.job);
                    }
                    self.loading = false;
                }).catch(function () {
                    self.loading = false;
                });
            },
            computed: {}
        }
        const routes = [{
            path: '/',
            component: Layout,
            redirect: '/home',
            children: [{
                path: 'home',
                name: 'Home',
                props: true,
                component: Home
            },
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
                let isAuth = parseInt(clientApiSettings.current_user_id) !== 0;
                if (to.name != 'Login' && !isAuth) next({
                    name: 'Login'
                });
                else next();
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
        new Vue({
            el: '#client',
            router
        });
    });
})(jQuery);