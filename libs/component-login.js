"use strict";

// Babel text type
var CompLogin = {
  template: '#component-login-template',
  data: function data() {
    return {
      loading: false,
      errors: [],
      user_login: '',
      user_password: '',
      remember_me: '',
      security: '',
      defaultBtnText: "Se connecter",
      buttonText: ''
    };
  },
  created: function created() {
    this.buttonText = lodash.clone(this.defaultBtnText);
    if (typeof com_login_params === 'undefined') return;
    this.security = com_login_params.nonce_field;
  },
  watch: {
    loading: function loading() {
      this.buttonText = this.loading ? 'Chargement...' : this.defaultBtnText;
    }
  },
  methods: {
    checkLoginForm: function checkLoginForm(e) {
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
    setSession: function setSession(user) {
      return new Promise(function (resolve, reject) {
        var Storage = {
          session_time: new Date().getTime(),
          uId: 0,
          uRole: null,
          uObject: null
        }; // Effacer tous les enregistrements

        sessionStorage.removeItem('job_session');
        var role = lodash.indexOf(user.roles, 'employer') >= 0 ? 'employer' : 'candidate';
        Storage.uId = user.id;
        Storage.uRole = role;
        Storage.uObject = lodash.clone(user);

        if ('employer' === role) {
          // Verifier si le meta est vide ou pas?
          var companyId = user.meta.company_id;
          if (0 === companyId) resolve(Storage);
          var apiGetCompany = new wp.api.models.User({
            id: companyId
          });
          apiGetCompany.fetch({
            data: {
              context: 'view'
            }
          }).done(function (response) {
            var data = response.data;
            Storage.uObject.company = data;
            resolve(Storage);
          });
        } else {
          resolve(Storage);
        }
      });
    },
    submitLogin: function submitLogin() {
      var _this = this;

      this.loading = true;
      var data = new FormData();
      data.append('username', this.user_login);
      data.append('password', this.user_password);
      data.append('remember', true);
      data.append('security', this.security);
      data.append('action', 'ajax_login');
      axios.post(com_login_params.ajax_url, data).then(function (response) {
        var responseData = response.data;
        _this.loading = false;

        if (!responseData.success) {
          if (responseData.data.code === 406) {
            alertify.alert("Information", responseData.data.message);
            return;
          }

          alertify.alert('Erreur', "Adresse email ou mot de passe incorrect.");
          return;
        } else {
          var pathname = window.location.pathname;

          if (lodash.includes([pathname], 'register')) {
            // Redirection page client
            window.location.href = window.location.origin + '/espace-client';
          } else {
            window.location.reload();
          }
        }
      })["catch"](function (err) {
        _this.loading = false;
      });
    }
  },
  delimiters: ['${', '}']
};