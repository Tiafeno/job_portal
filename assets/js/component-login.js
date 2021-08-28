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
    created: function() {
        this.buttonText = lodash.clone(this.defaultBtnText);
        if (typeof com_login_params === 'undefined') return;
        this.security = com_login_params.nonce_field
    },
    watch: {
        loading: function() {
            this.buttonText = this.loading ? 'Chargement...' : this.defaultBtnText;
        }
    },
    methods: {
        checkLoginForm: function(e) {
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
                    session_date: new Date().getTime(),
                    user_id: 0,
                    user_role: null,
                    user_object: {},
                };
                // Verifier s'il y a une enregistrement
                let currentSession = sessionStorage.getItem('job_session');
                if (!lodash.isNull(currentSession)) {
                    currentSession = window.atob(currentSession);
                    sessionDate = new Date(currentSession.session_date);
                    // Verifier la date d'expiration
                    let dateNow = new Date();
                    if (sessionDate.getDay() < dateNow.getDay()) {
                        sessionStorage.removeItem('job_session');
                    }
                }
                let role = lodash.indexOf(user.roles, 'employer') >= 0 ? 'employer' : 'candidate';
                Storage.user_id = user.id;
                Storage.user_role = role;
                Storage.user_object[ role ] = lodash.clone(user);

                if ('employer' === role) {
                    const apiGetCompany = new wp.api.models.User( {id: user.meta.company_id });
                    apiGetCompany.fetch({data: {context: 'view'}}).done(response => {
                        const data = response.data;
                        Storage.user_object.company = data;
                        resolve(window.btoa(Storage));
                    })
                    
                } else {
                    resolve(window.btoa(Storage));
                }
                
                
            });   
        },
        submitLogin: function() {
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
                if (!responseData.success){
                    if (responseData.data === 800) {
                        window.location.reload();
                        return;
                    }
                    alertify.alert('Erreur', "Adresse email ou mot de passe incorrect.", function () {});
                    return;
                } else  {
                    console.warn('Emit event: login-success');
                    this.setSession(responseData.data).then(storage => {
                        sessionStorage.setItem('job_session', storage);
                        this.loading = false;
                        window.location.reaload();
                    });
                }
            }).catch((err) => {
                this.loading = false;
            });
        }
    },
    delimiters: ['${', '}']
};