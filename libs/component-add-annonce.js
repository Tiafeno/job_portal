"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

(function ($) {
  $().ready(function () {
    // Return random password
    var getRandomPassword = function getRandomPassword() {
      var lenght = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 8;
      var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
      var string_length = lenght;
      var randomstring = '';

      for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
      }

      return randomstring;
    };

    var fileFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
    /**
     * Cette fonction permet de redimensionner une image
     *
     * @param imgObj - the image element
     * @param newWidth - the new width
     * @param newHeight - the new height
     * @param startX - the x point we start taking pixels
     * @param startY - the y point we start taking pixels
     * @param ratio - the ratio
     * @returns {string}
     */

    var drawImage = function drawImage(imgObj, newWidth, newHeight, startX, startY, ratio) {
      //set up canvas for thumbnail
      var tnCanvas = document.createElement('canvas');
      var tnCanvasContext = tnCanvas.getContext('2d');
      tnCanvas.width = newWidth;
      tnCanvas.height = newHeight;
      /* use the sourceCanvas to duplicate the entire image. This step was crucial for iOS4 and under devices. Follow the link at the end of this post to see what happens when you don’t do this */

      var bufferCanvas = document.createElement('canvas');
      var bufferContext = bufferCanvas.getContext('2d');
      bufferCanvas.width = imgObj.width;
      bufferCanvas.height = imgObj.height;
      bufferContext.drawImage(imgObj, 0, 0);
      /* now we use the drawImage method to take the pixels from our bufferCanvas and draw them into our thumbnail canvas */

      tnCanvasContext.drawImage(bufferCanvas, startX, startY, newWidth * ratio, newHeight * ratio, 0, 0, newWidth, newHeight);
      return tnCanvas.toDataURL();
    };
    /**
     * Récuperer les valeurs dispensable pour une image pré-upload
     * @param {File} file
     * @returns {Promise<any>}
     */


    var getFileReader = function getFileReader(file) {
      return new Promise(function (resolve, reject) {
        var byteLimite = 2097152; // 2Mb

        if (file && file.size <= byteLimite) {
          var fileReader = new FileReader();

          fileReader.onload = function (Event) {
            var img = new Image();
            img.src = Event.target.result;

            img.onload = function () {
              var imgCrop = drawImage(img, img.width, img.height, 0, 0, 1);
              resolve({
                src: imgCrop
              });
            };
          };

          fileReader.readAsDataURL(file);
        } else {
          reject('Le fichier sélectionné est trop volumineux. La taille maximale est 2Mo.');
        }
      });
    }; // Ajouter une entreprise


    var CreateCompany = {
      template: '#create-company',
      data: function data() {
        return {
          loading: false,
          heading: "Ajouter une entreprise",
          sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
          wordpress_api: new WPAPI({
            endpoint: window.job_handler_api.root,
            nonce: window.job_handler_api.nonce
          }),
          company_logo: '//semantic-ui.com/images/wireframe/square-image.png',
          errors: [],
          formData: {
            name: '',
            logo: '',
            category: '',
            // email: '',
            address: '',
            nif: '',
            stat: '',
            phone: '',
            country: '',
            city: '',
            zipcode: '',
            website: '',
            employees: "1-5",
            description: ''
          }
        };
      },
      methods: {
        checkForm: function checkForm(e) {
          e.preventDefault();
          this.errors = [];
          var data = this.formData;
          var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

          if (lodash.isEmpty(data.name)) {
            this.errors.push('Le titre est requis');
          }

          if (data.category === "" || data.category === " ") {
            this.errors.push('Champ categorie est requis');
          } // if (lodash.isEmpty(data.email) || !data.email.match(validRegex)) {
          //     this.errors.push('Le champ email est requis ou verifier que c\'est une adresse email valide');
          // }


          if (lodash.isEmpty(data.nif)) {
            this.errors.push('Champ "NIF" est requis');
          }

          if (lodash.isEmpty(data.stat)) {
            this.errors.push('Champ "Numéro statistique" est requis');
          }

          if (lodash.isEmpty(data.address)) {
            this.errors.push('Votre adresse est requis');
          }

          if (data.country === "" || data.country === " ") {
            this.errors.push('Champ pays est requis');
          }

          if (lodash.isEmpty(data.city)) {
            this.errors.push('Champ ville est requis');
          }

          if (lodash.isEmpty(data.description)) {
            this.errors.push('Champ à propos est requis');
          }

          if (lodash.isEmpty(this.errors)) {
            this.addCompany(data);
          }
        },
        previewFiles: function previewFiles(event) {
          var _this = this;

          var files = event.target.files;
          this.formData.logo = files.item(0);
          getFileReader(files.item(0)).then(function (draw) {
            _this.company_logo = draw.src;
          });
        },
        uploadLogoHandler: function uploadLogoHandler(event) {
          event.preventDefault();
          $('input#company-logo').trigger('click');
        },
        addCompany: function () {
          var _addCompany = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(item) {
            var _this2 = this;

            var self, randomMail, _email, _name, fileId;

            return regeneratorRuntime.wrap(function _callee$(_context) {
              while (1) {
                switch (_context.prev = _context.next) {
                  case 0:
                    self = this;
                    randomMail = getRandomPassword(10);
                    _email = "".concat(randomMail, "@jobjiaby.com"); // Create custom mail

                    _name = item.name;
                    fileId = ''; // Upload avatar

                    this.loading = true;

                    if (!(_typeof(item.logo) === 'object' && item.logo !== '')) {
                      _context.next = 9;
                      break;
                    }

                    _context.next = 9;
                    return this.wordpress_api.media() // Specify a path to the file you want to upload, or a Buffer
                    .file(item.logo).create({
                      title: "logo ".concat(item.name),
                      alt_text: item.name,
                      description: item.description
                    }).then(function (response) {
                      // Your media is now uploaded: let's associate it with a post
                      fileId = response.id;
                    });

                  case 9:
                    this.wordpress_api.users().create({
                      name: _name,
                      nickname: _email,
                      username: _name,
                      password: getRandomPassword(),
                      email: _email,
                      first_name: "",
                      last_name: "",
                      roles: ['company'],
                      description: item.description,
                      avatar: fileId,
                      validated: 0,
                      // Ne pas activer l'entreprise pour être en attente de validation
                      blocked: 0,
                      // Ne pas blocked l'utilisateur
                      meta: {
                        country: item.country,
                        category: item.category,
                        city: item.city,
                        address: item.address,
                        phone: item.phone,
                        nif: item.nif,
                        stat: item.stat,
                        website: item.website,
                        zipcode: item.zipcode,
                        employees: item.employees,
                        newsletter: 0,
                        // bool value to subscribe or not
                        employer_id: job_handler_api.current_user_id
                      }
                    }).then(function (user) {
                      // Add this company for the employee
                      self.wordpress_api.users().me().update({
                        meta: {
                          company_id: user.id
                        }
                      }).then(function () {
                        self.loading = false; // Company add successfuly

                        self.$router.push({
                          name: 'Annonce'
                        });
                      });
                    })["catch"](function (err) {
                      _this2.loading = false;

                      _this2.errorHandler(err);
                    });

                  case 10:
                  case "end":
                    return _context.stop();
                }
              }
            }, _callee, this);
          }));

          function addCompany(_x) {
            return _addCompany.apply(this, arguments);
          }

          return addCompany;
        }(),
        errorHandler: function errorHandler(response) {
          var title = '';

          switch (response.code) {
            case 'existing_user_email':
              title = 'Erreur';
              break;

            default:
              title = 'Information';
              break;
          }

          alertify.alert(title, response.message);
        },
        formatHTML: function formatHTML(str) {
          return str.replace(/(<([^>]+)>)/ig, "");
        }
      },
      created: function created() {
        var _this3 = this;

        this.loading = true;
        this.wordpress_api.users().me().context('view').then(function (response) {
          var me = lodash.cloneDeep(response);
          var hasCompany = me.meta.company_id !== 0;

          if (hasCompany) {
            _this3.$router.push({
              name: 'Annonce'
            });
          }

          _this3.loading = false;
        })["catch"](function (err) {
          this.loading = false;
          this.errorHandler(err);
        });
      },
      mounted: function mounted() {
        $('select').dropdown({
          clearable: true,
          placeholder: ''
        });
      },
      delimiters: ['${', '}']
    }; // Ajouter une annonce

    var CreateAnnonce = {
      template: '#create-annonce',
      data: function data() {
        return {
          me: {},
          heading: "Ajouter une annonce",
          sectionClass: 'utf_create_company_area padd-bot-80',
          hasActiveCompany: false,
          loading: false,
          errors: [],
          companyId: 0,
          inputs: {
            title: '',
            salary_range: '',
            address: '',
            category: '',
            // Secteur d'activite
            region: 0,
            // Taxonomy
            experience: 0,
            type: '',
            //CDI, CDD etc..
            description: ''
          }
        };
      },
      mounted: function mounted() {
        $('select').dropdown({
          clearable: true,
          placeholder: 'Selectionnez une option'
        });
        this.inputs.description = new MediumEditor('#advert-description', {
          toolbar: {
            /* These are the default options for the toolbar,
               if nothing is passed this is what is used */
            allowMultiParagraphSelection: true,
            buttons: ['bold', 'italic', 'underline', 'strikethrough', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'orderedlist', 'unorderedlist', 'outdent', 'indent', 'h2', 'h3'],
            firstButtonClass: 'medium-editor-button-first',
            lastButtonClass: 'medium-editor-button-last',
            standardizeSelectionStart: false,
            "static": false,

            /* options which only apply when static is true */
            align: 'center',
            sticky: true,
            updateOnEmptySelection: false,
            paste: {
              cleanAttrs: ['class', 'style', 'dir'],
              cleanTags: ['meta'],
              cleanPastedHTML: true,
              forcePlainText: false
            }
          }
        });
      },
      created: function created() {
        this.WPAPI = new WPAPI({
          endpoint: job_handler_api.root,
          nonce: job_handler_api.nonce
        });
        this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
          // Listing any of these parameters will assign the built-in
          // chaining method that handles the parameter:
          params: ['context']
        }); // Si le client est connecter, On verifie s'il existe deja une entreprise

        this.initComponent();
      },
      methods: {
        errorHandler: function errorHandler(inputName) {
          var err = "Le champ <b>\"".concat(inputName, "\"</b> est obligatoire");
          this.errors.push(err);
        },
        isActiveCompany: function () {
          var _isActiveCompany = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(idCompany) {
            var company;
            return regeneratorRuntime.wrap(function _callee2$(_context2) {
              while (1) {
                switch (_context2.prev = _context2.next) {
                  case 0:
                    _context2.next = 2;
                    return this.WPAPI.users().id(idCompany).get();

                  case 2:
                    company = _context2.sent;
                    this.hasActiveCompany = !!company.validated;
                    this.loading = false;

                  case 5:
                  case "end":
                    return _context2.stop();
                }
              }
            }, _callee2, this);
          }));

          function isActiveCompany(_x2) {
            return _isActiveCompany.apply(this, arguments);
          }

          return isActiveCompany;
        }(),
        initComponent: function () {
          var _initComponent = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
            var _this4 = this;

            return regeneratorRuntime.wrap(function _callee3$(_context3) {
              while (1) {
                switch (_context3.prev = _context3.next) {
                  case 0:
                    this.loading = true;
                    this.WPAPI.users().me().context('edit').then(function (resp) {
                      _this4.me = lodash.clone(resp); // Verifier le role du client

                      var roles = _this4.me.roles;

                      if (lodash.indexOf(roles, 'employer') < 0) {
                        _this4.$router.push({
                          name: "DenialAccess"
                        });

                        return;
                      } // Verifier si l'utilisateur possede deja une entreprise ou societe
                      // et que cette entreprise est valide ou verifier


                      _this4.companyId = parseInt(_this4.me.meta.company_id);
                      var hasCompany = _this4.companyId !== 0;

                      if (!hasCompany) {
                        _this4.$router.push({
                          name: 'Company'
                        });

                        return;
                      } // Check the company is valid or not


                      _this4.isActiveCompany(_this4.companyId);
                    });

                  case 2:
                  case "end":
                    return _context3.stop();
                }
              }
            }, _callee3, this);
          }));

          function initComponent() {
            return _initComponent.apply(this, arguments);
          }

          return initComponent;
        }(),

        /** Event on submit form */
        checkAddForm: function checkAddForm(ev) {
          ev.preventDefault();
          this.errors = [];
          var description = this.inputs.description.getContent();

          if (lodash.isEmpty(this.inputs.title)) {
            this.errorHandler('Poste à pourvoir');
          }

          if (lodash.isEmpty(description)) {
            this.errorHandler('Description');
          }

          if (this.inputs.region === 0) {
            this.errorHandler('Region');
          }

          if (lodash.isEmpty(this.inputs.address)) {
            this.errorHandler('Adresse');
          } // Verifier si l'utilisateur est blocke


          if (this.me.blocked == 1) {
            alertify.alert('Information', "Votre compte a été bloquer par l'administrateur");
            return;
          } // Return if an error exist and company isn't activate


          if (!lodash.isEmpty(this.errors) || !this.hasActiveCompany) return;
          this.submitForm();
        },
        submitForm: function submitForm() {
          var self = this;
          this.loading = true;
          var _category = [],
              _region = [],
              _salaries = [],
              _jobtype = [];
          if (this.inputs.category) _category.push(parseInt(this.inputs.category));
          if (this.inputs.salary_range) _salaries.push(parseInt(this.inputs.salary_range));
          if (this.inputs.type) _jobtype.push(parseInt(this.inputs.type));
          if (this.inputs.region) _region.push(parseInt(this.inputs.region));
          this.WPAPI.jobs().create({
            title: this.inputs.title,
            content: this.inputs.description.getContent(),
            categories: _category,
            // taxonomy
            region: _region,
            // taxonomy
            salaries: _salaries,
            // taxonomy
            job_type: _jobtype,
            // taxonomy - type de travail
            meta: {
              experience: parseInt(this.inputs.experience),
              address: this.inputs.address,
              employer_id: self.me.id,
              company_id: parseInt(self.me.meta.company_id)
            },
            status: 'pending'
          }).then(function (resp) {
            self.loading = false;
            alertify.alert('Information', "Votre annonce a bien été publier avec succès", function () {
              window.location.href = job_handler_api.account_url;
            });
          })["catch"](function (err) {
            self.loading = false;
            alertify.alert('Erreur', err.message);
          });
        }
      },
      delimiters: ['${', '}']
    }; // Denial access template

    var DenialAccess = {
      template: "#denial-access" // Include in theme.liquid

    };
    var PendingAccess = {
      template: "#pending-access" // Include in theme.liquid

    }; // Application

    var Layout = {
      template: '<div>Chargement</div>',
      data: function data() {
        return {};
      },
      created: function created() {
        // Check if is client
        // var job_handler_api is global js variable in localize for add-annonce widget
        this.isClient = parseInt(job_handler_api.current_user_id) !== 0;
        if (typeof job_handler_api === 'undefined') return;
      },
      methods: {},
      delimiters: ['${', '}']
    };
    var routes = [{
      path: '/',
      component: Layout,
      redirect: '/create-annonce'
    }, {
      path: '/create-company',
      component: CreateCompany,
      name: 'Company',
      beforeEnter: function beforeEnter(to, from, next) {
        var isAuth = parseInt(job_handler_api.current_user_id) !== 0;
        if (to.name != 'Login' && !isAuth) next({
          name: 'Login'
        });else next();
      }
    }, {
      path: '/create-annonce',
      component: CreateAnnonce,
      name: 'Annonce',
      beforeEnter: function beforeEnter(to, from, next) {
        var isAuth = parseInt(job_handler_api.current_user_id) !== 0;
        if (to.name != 'Login' && !isAuth) next({
          name: 'Login'
        });else next();
      }
    }, {
      path: '/login',
      name: 'Login',
      component: CompLogin,
      beforeEnter: function beforeEnter(to, from, next) {
        if (job_handler_api.isLogged) next({
          name: 'Annonce'
        });else next();
      }
    }, {
      path: '/denial-access',
      component: DenialAccess,
      name: 'DenialAccess'
    }, {
      path: '/pending-access',
      component: PendingAccess,
      name: 'PendingAccess'
    }];
    var router = new VueRouter({
      routes: routes // short for `routes: routes`

    });
    new Vue({
      el: '#add-annonce',
      router: router
    });
  });
})(jQuery);