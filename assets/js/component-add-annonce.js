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
                        meta: []
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
                            alertify.alert('Erreur', response.message, function () {});
                            break;
                        default:
                            alertify.alert('Information', response.message, function () {});
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
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                }
            },
            created: function () {

            },
            props: [],
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
            data: {
                isClient: false,
                hasCompany: false,
                Me: {},
                WPAPI: null,
                stateView: '',
            },
            created: function () {
                // Check if is client
                // var job_handler_api is global js variable in localize for add-annonce widget
                this.isClient = parseInt(job_handler_api.current_user_id) !== 0;
                if (typeof wpApiSettings === 'undefined') {
                    return;
                }
                this.verifyClient();
            },
            methods: {
                verifyClient: function() {
                    const self = this;
                    this.WPAPI = new WPAPI({
                        endpoint: wpApiSettings.root,
                        nonce: wpApiSettings.nonce
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
                hasCompanyAccountfn: function($event) {
                    console.log($event);
                    this.hasCompany = $event;
                },
                loggedIn: function(data) {
                    window.location.reload();
                }

            },
            delimiters: ['${', '}']
        });

    });
})(jQuery);

