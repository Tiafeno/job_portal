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
            security: ''
        }
    },
    created: function() {
        if (typeof com_login_params === 'undefined') return;
        this.security = com_login_params.nonce_field
    },
    methods: {
        checkLoginForm: function(e) {
            e.preventDefault();
            this.errors = [];
            if (lodash.isEmpty(this.user_login)) {
                this.errors.push('Adresse email ou indentifiant est requis');
            }
            if (lodash.isEmpty(this.user_password)) {
                this.errors.push('Le mot de passe est requis');
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
                self.loading = false;
                var responseData = response.data;
                if (!responseData.success){
                    alertify.alert('Notification', responseData.data, function () {});
                    return;
                }
                console.warn('Emit event: login-success');
                setTimeout(()=> {
                    window.location.reload();
                }, 1500);
            }).catch(function(err) {
                self.loading = false;
            });
        }
    },
    delimiters: ['${', '}']
};