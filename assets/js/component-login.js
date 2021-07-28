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
        submitLogin: function() {
            const self = this;
            this.loading = true;
            let data = new FormData();
            data.append('username', self.user_login);
            data.append('password', self.user_password);
            data.append('remember', true);
            data.append('security', self.security);
            data.append('action', 'ajax_login');
            axios.post(com_login_params.ajax_url, data).then(function(response) {
                var responseData = response.data;
                if (!responseData.success){
                    self.loading = false;
                    alertify.alert('Notification', responseData.data, function () {});
                    return;
                }
                console.warn('Emit event: login-success');
                setTimeout(()=> {
                    self.loading = false;
                    window.location.reload();
                }, 1500);
            }).catch(function(err) {
                self.loading = false;
            });
        }
    },
    delimiters: ['${', '}']
};