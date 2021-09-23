// Babel text type
const CompLogin = {
    template: '#component-login-template',
    data: function () {
        return {
            loading: false,
            errors: [],
            user_login: '',
            user_password: '',
            remember_me: '',
            security: '',
            defaultBtnText: "Se connecter",
            buttonText: ''
        }
    },
    created: function () {
        this.buttonText = lodash.clone(this.defaultBtnText);
        if (typeof com_login_params === 'undefined') return;
        this.security = com_login_params.nonce_field
    },
    watch: {
        loading: function () {
            this.buttonText = this.loading ? 'Chargement...' : this.defaultBtnText;
        }
    },
    methods: {
        checkLoginForm: function (e) {
            e.preventDefault();
            this.errors = [];
            if (lodash.isEmpty(this.user_login)) {
                this.errors.push("L'adresse email est requis");
            }
            if (lodash.isEmpty(this.user_password)) {
                this.errors.push('Le mot de passe est obligatoire');
            }
            if (!lodash.isEmpty(this.errors)) {
                return false;
            }
            this.submitLogin();
        },
        setSession: function (user) {
            return new Promise((resolve, reject) => {
                let Storage = {
                    session_time: new Date().getTime(),
                    uId: 0,
                    uRole: null,
                    uObject: null,
                };
                // Effacer tous les enregistrements
                sessionStorage.removeItem('job_session');
                let role = lodash.indexOf(user.roles, 'employer') >= 0 ? 'employer' : 'candidate';
                Storage.uId = user.id;
                Storage.uRole = role;
                Storage.uObject = lodash.clone(user);
                if ('employer' === role) {
                    // Verifier si le meta est vide ou pas?
                    let companyId = user.meta.company_id;
                    if (0 === companyId) resolve(Storage);
                    const apiGetCompany = new wp.api.models.User({id: companyId});
                    apiGetCompany.fetch({data: {context: 'view'}}).done(response => {
                        const data = response.data;
                        Storage.uObject.company = data;
                        resolve(Storage);
                    })
                } else {
                    resolve(Storage);
                }
            });
        },
        submitLogin: function () {
            this.loading = true;
            let data = new FormData();
            data.append('username', this.user_login);
            data.append('password', this.user_password);
            data.append('remember', true);
            data.append('security', this.security);
            data.append('action', 'ajax_login');
            axios.post(com_login_params.ajax_url, data).then((response) => {
                var responseData = response.data;
                this.loading = false;
                if (!responseData.success) {
                    if (responseData.data.code === 406) {
                        alertify.alert("Information", responseData.data.message);
                        return;
                    }
                    alertify.alert('Erreur', "Adresse email ou mot de passe incorrect.");
                    return;
                } else {
                    let pathname = window.location.pathname;
                    if (lodash.includes(pathname, 'register')) {
                        // Redirection page client
                        window.location.href = window.location.origin + '/espace-client';
                    } else {
                        window.location.reload();
                    }

                }
            }).catch((err) => {
                this.loading = false;
            });
        }
    },
    delimiters: ['${', '}']
};