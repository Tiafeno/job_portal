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

        const CV = {
            template: '#client-cv',
            components: {
                'comp-education': CVComponents.education,
                'comp-experience': CVComponents.experience
            },
            data: function () {
                return {
                    hasCV: false,

                    first_name: '',
                    last_name: '',
                    phone: '',
                    address: "",
                    gender: "",
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
                    /** */
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

                }
            }
        };

        const routes = [{
                path: '/',
                component: Layout,
                redirect: '/home',
                children: [{
                        path: 'home',
                        name: 'Home',
                        component: Home
                    },
                    {
                        path: 'cv',
                        name: 'CV',
                        component: CV
                    },
                ],
                beforeEnter: (to, from, next) => {
                    if (to.name != 'Login' && parseInt(clientApiSettings.current_user_id) == 0) next({
                        name: 'Login'
                    })
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
        });3

        Vue.component('v-select', VueSelect.VueSelect);
        // Application
        new Vue({
            el: '#client',
            router
        });

    });
})(jQuery);