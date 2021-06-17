(function ($) {
    $().ready(() => {
        // Return random password
        const getRandomPassword = () => {
            const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            const string_length = 8;
            let randomstring = '';
            for (var i = 0; i < string_length; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                randomstring += chars.substring(rnum, rnum + 1);
            }
            return randomstring;
        };
        // Ajouter une entreprise
        const CreateCompany = {
            template: '#create-company',
            data: function () {
                return {
                    loading: false,
                    heading: "Ajouter une entreprise",
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                    wordpress_api: new WPAPI({
                        endpoint: window.wpApiSettings.root,
                        nonce: window.wpApiSettings.nonce
                    }),
                    errors: [],
                    formData: {
                        name: '',
                        category: '',
                        email: '',
                        address: '',
                        phone: '',
                        country: '',
                        city: '',
                        zipcode: '',
                        website: '',
                        employees: 0,
                        description: ''
                    }
                }
            },
            methods: {
                checkForm: function (e) {
                    e.preventDefault();
                    this.errors = [];
                    const data = this.formData;
                    if (lodash.isEmpty(data.name)) {
                        this.errors.push('Le titre est requis');
                    }
                    if (lodash.isEmpty(data.category)) {
                        this.errors.push('Champ categorie est requis');
                    }
                    if (lodash.isEmpty(data.email)) {
                        this.errors.push('Champ email est requis');
                    }
                    if (lodash.isEmpty(data.address)) {
                        this.errors.push('Champ adresse est requis');
                    }
                    if (lodash.isEmpty(data.country)) {
                        this.errors.push('Champ pays est requis');
                    }
                    if (lodash.isEmpty(data.city)) {
                        this.errors.push('Champ ville est requis');
                    }
                    if (lodash.isEmpty(data.description)) {
                        this.errors.push('Champ a propos est requis');
                    }

                    if (lodash.isEmpty(this.errors)) {
                        this.addCompany();
                    }
                },
                addCompany: function () {
                    const self = this;
                    this.loading = true;
                    const _email = this.formData.email;
                    const _name = this.formData.name;
                    this.wordpress_api.users().create({
                        name: _name,
                        nickname: _name,
                        username: _name,
                        password: getRandomPassword(),
                        email: _email,
                        first_name: "",
                        last_name: "",
                        roles: ['company'],
                        meta: {
                            country: 0,
                            city: 0,
                            address: '',
                            region: 0,
                            nif: '',
                            stat: '',
                            newsletter: 0, // bool value to subscribe or not

                        }
                    })
                        .then(function (response) {
                            // Add this company for the employee
                            self.wordpress_api.users().id(self.me.id).update({
                                meta: {company_id: response.id}
                            }).then(function (response) {
                                self.loading = false;
                                // Company add successfuly
                                self.$emit('has-company-account', true);
                            });
                        }).catch(function (err) {
                        self.loading = false;
                        self.errorHandler(err);
                    });
                },
                errorHandler: function (response) {
                    console.log(response);
                    switch (response.code) {
                        case 'existing_user_email':
                            alertify.alert('Erreur', response.message, function () {
                            });
                            break;
                        default:
                            alertify.alert('Information', response.message, function () {
                            });
                            break
                    }
                },
                formatHTML: function (str) {
                    return str.replace(/(<([^>]+)>)/ig, "");
                }
            },
            created: function () {
            },
            mounted: function () {
                $('select')
                    .dropdown({
                        clearable: true,
                        placeholder: 'any'
                    })
            },
            props: ['me'],
            delimiters: ['${', '}']

        };
        // Ajouter une annonce
        const CreateAnnonce = {
            template: '#create-annonce',
            data: function () {
                return {
                    heading: "Ajouter une annonce",
                    sectionClass: 'utf_create_company_area padd-bot-80',
                    loading: false,
                    errors: [],
                    inputs: {
                        title: '',
                        salary_range: '',
                        category: '',
                        experience: 0,
                        type: '', //CDI, CDD etc..
                        qualification: '',
                        description: ''
                    },
                }
            },
            created: function () {
            },
            mounted: function () {
                $('select').dropdown({
                    clearable: true,
                    placeholder: 'any'
                });
            },
            methods: {
                checkAddForm: function (ev) {
                    ev.preventDefault();
                    this.errors = [];
                    if (lodash.isEmpty(this.inputs.title)) {
                        this.errors.push("Le titre est requis");
                    }
                    if (lodash.isEmpty(this.inputs.description)) {
                        this.errors.push("Description requis");
                    }

                    if (!lodash.isEmpty(this.errors)) {
                        return;
                    }
                    this.submitForm();
                },
                submitForm: function () {
                    const self = this;
                    this.loading = true;
                    let _category = [],
                        _salaries = new Array(),
                        _jobtype = new Array(),
                        _qualification = new Array();

                    if (this.inputs.category) _category.push(parseInt(this.inputs.category));
                    if (this.inputs.salary_range) _salaries.push(parseInt(this.inputs.salary_range));
                    if (this.inputs.type) _jobtype.push(parseInt(this.inputs.type));
                    if (this.inputs.qualification) _qualification.push(parseInt(this.inputs.qualification));

                    this.wpapinode.jobs().create({
                        title: this.inputs.title,
                        content: this.inputs.description,
                        categories: _category,
                        salaries: _salaries,
                        job_type: _jobtype,
                        qualification: _qualification,
                        meta: {
                            experience: parseInt(this.inputs.experience),
                            employer_id: self.me.id
                        },
                        'status': 'pending',
                    }).then(function (resp) {
                        self.loading = false;
                        window.location.href = job_handler_api.account_url;
                    }).catch(function (err) {

                    });
                }
            },
            props: ['me', 'wpapinode'],
            delimiters: ['${', '}']
        };

        // Application
        new Vue({
            el: '#add-annonce',
            components: {
                'create-company': CreateCompany,
                'comp-login': CompLogin,
                'create-annonce': CreateAnnonce
            },
            data: function () {
                return {
                    isClient: false,
                    hasCompany: false,
                    Me: {},
                    WPAPI: null,
                    stateView: '',
                }
            },
            created: function () {
                // Check if is client
                // var job_handler_api is global js variable in localize for add-annonce widget
                this.isClient = parseInt(job_handler_api.current_user_id) !== 0;
                if (typeof wpApiSettings === 'undefined') {
                    return;
                }
                this.vfClient();
            },
            methods: {
                vfClient: function () {
                    const self = this;
                    this.WPAPI = new WPAPI({
                        endpoint: wpApiSettings.root,
                        nonce: wpApiSettings.nonce
                    });
                    this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                        // Listing any of these parameters will assign the built-in
                        // chaining method that handles the parameter:
                        params: ['context']
                    });
                    // Si le client est connecter, On verifie s'il existe deja une entreprise
                    if (this.isClient) {
                        this.WPAPI.users().me().context('edit')
                            .then(function (resp) {
                                self.Me = lodash.clone(resp);
                                self.hasCompany = self.Me.meta.company_id !== 0;
                            });
                    }
                },
                hasCompanyAccountfn: function ($event) {
                    console.log($event);
                    this.hasCompany = $event;
                },

            },
            delimiters: ['${', '}']
        });

    });
})(jQuery);

