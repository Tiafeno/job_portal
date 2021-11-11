"use strict";

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

var jobAXIOSInstance = axios.create({
  baseURL: clientApiSettings.root + 'job/v2',
  headers: {
    'X-WP-Nonce': clientApiSettings.nonce
  }
});
var fileFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

var getRandomPassword = function getRandomPassword() {
  var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 8;
  var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  var string_length = length;
  var randomstring = '';

  for (var i = 0; i < string_length; i++) {
    var rnum = Math.floor(Math.random() * chars.length);
    randomstring += chars.substring(rnum, rnum + 1);
  }

  return randomstring;
};
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
};

(function ($) {
  $(document).ready(function () {
    Vue.component('v-select', VueSelect.VueSelect);
    Vue.filter('jobStatus', function (value) {
      if (!value) return 'Inconnue';
      value = value.toString();
      return value === 'pending' ? 'En attente de validation' : value === 'private' ? 'Supprimer' : 'Publiée';
    });
    Vue.filter('cvStatus', function (user) {
      if (!user) return 'Inconnue';
      var isPublic = user.validated; // boolean

      var hasCV = user.meta.has_cv; // boolean

      if (!hasCV) return "Indisponible";
      return isPublic ? "Publier" : "En attent de validation";
    }); // Return random password

    var jobHTTPInstance = axios.create({
      baseURL: clientApiSettings.root + 'job/v2',
      headers: {
        'X-WP-Nonce': clientApiSettings.nonce
      }
    });

    var getRandomId = function getRandomId() {
      var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
      var string_length = 8;
      var randomstring = '';

      for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
      }

      return randomstring;
    };

    var _componentUploadAvatar = {
      props: ['userid', 'wpapi', 'title'],
      template: "#upload-avatar-template",
      data: function data() {
        return {
          wpUploadUrl: null,
          btnTitle: 'Ajouter',
          loading: false,
          defaultPreviewLogo: '//semantic-ui.com/images/wireframe/square-image.png',
          logoReadUrl: null,
          avatarFile: null
        };
      },
      computed: {
        previewUrl: function previewUrl() {
          return lodash.isNull(this.logoReadUrl) ? this.defaultPreviewLogo : this.logoReadUrl;
        }
      },
      methods: {
        previewFiles: function previewFiles(event) {
          var _this = this;

          var files = event.target.files;
          this.avatarFile = files.item(0);
          getFileReader(this.avatarFile).then(function (draw) {
            _this.logoReadUrl = draw.src;

            _this.upload();
          });
        },
        eventClickHandler: function eventClickHandler(event) {
          event.preventDefault();
          $('input#upload-avatar').trigger('click');
        },
        upload: function () {
          var _upload = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
            var _this2 = this;

            return regeneratorRuntime.wrap(function _callee$(_context) {
              while (1) {
                switch (_context.prev = _context.next) {
                  case 0:
                    this.loading = true;
                    _context.next = 3;
                    return this.wpapi.media() // Specify a path to the file you want to upload, or a Buffer
                    .file(this.avatarFile).create({
                      title: "",
                      alt_text: "",
                      description: this.userid
                    }).then(function (uploadMedia) {
                      _this2.__putUserAvatar(uploadMedia);
                    });

                  case 3:
                  case "end":
                    return _context.stop();
                }
              }
            }, _callee, this);
          }));

          function upload() {
            return _upload.apply(this, arguments);
          }

          return upload;
        }(),
        __putUserAvatar: function __putUserAvatar(media) {
          var _this3 = this;

          // Your media is now uploaded: let's associate it with a post
          this.wpapi.users().id(this.userid).update({
            avatar: media.id
          }).then(function (resp) {
            _this3.loading = false;
            alertify.notify("Photo de profil mis a jour avec succes", 'success');
          });
        }
      },
      created: function created() {
        var _this4 = this;

        this.btnTitle = this.title; // build url

        var ABS = '/';
        var wpUserModel = new wp.api.models.User({
          id: this.userid
        });
        wpUserModel.fetch().done(function (u) {
          var avatar = u.avatar;
          if (lodash.isEmpty(avatar)) return;
          _this4.defaultPreviewLogo = avatar.upload_dir.baseurl + ABS + avatar.image.file;
        });
      },
      delimiters: ['${', '}']
    };
    var _componentPricing = {
      props: ['item'],
      template: "#pricing_account",
      data: function data() {
        return {
          loading: false
        };
      },
      methods: {
        goToPurchase: function goToPurchase(ev, _id) {
          var _this5 = this;

          ev.preventDefault();
          var uId = clientApiSettings.current_user_id;
          this.loading = true;
          jobHTTPInstance.post("pay/account/".concat(_id, "/").concat(uId), {}).then(function (resp) {
            if (resp.status === 200) {
              var response = resp.data;

              if (response.success) {
                console.log(response);
              }
            }

            _this5.loading = false;
          });
          return;
        }
      }
    };
    var _componentCVStatus = {
      template: "#cv-status-template",
      props: ['client'],
      data: function data() {
        return {
          optStatus: [],
          loading: false,
          status: 0
        };
      },
      methods: {
        // Update candidate status
        onUpdate: function onUpdate(value) {
          var form = new FormData();
          form.append('uid', this.client.id);
          form.append('val', value);
          this.loading = true; // Send request for update status

          jobAXIOSInstance.post('/cv-status', form, function (resp) {
            this.loading = false;
          });
        }
      },
      created: function created() {
        var _this6 = this;

        this.loading = true;
        jobAXIOSInstance.get('/cv-status').then(function (resp) {
          _this6.optStatus = resp.data;
          _this6.status = _this6.client.cv_status;
          _this6.loading = false;
        });
      }
    };
    var Layout = {
      template: '#client-layout',
      data: function data() {
        return {
          Loading: false,
          isLogged: false,
          isCandidate: false,
          isEmployer: false,
          Client: null,
          Wordpress: null
        };
      },
      created: function created() {
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
        init: function () {
          var _init = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
            var _this7 = this;

            var self;
            return regeneratorRuntime.wrap(function _callee2$(_context2) {
              while (1) {
                switch (_context2.prev = _context2.next) {
                  case 0:
                    self = this;

                    if (!(parseInt(clientApiSettings.current_user_id) == 0 || !clientApiSettings.current_user_id)) {
                      _context2.next = 4;
                      break;
                    }

                    this.isLogged = false;
                    return _context2.abrupt("return", false);

                  case 4:
                    this.isLogged = true;
                    _context2.next = 7;
                    return this.Wordpress.users().context('edit').me().then(function (response) {
                      self.Client = lodash.clone(response); // Check if is Candidate or Employer

                      // Check if is Candidate or Employer
                      _this7.isCandidate = lodash.indexOf(self.Client.roles, 'candidate') >= 0;
                      _this7.isEmployer = lodash.indexOf(self.Client.roles, 'employer') >= 0;
                      self.Loading = true;
                    });

                  case 7:
                  case "end":
                    return _context2.stop();
                }
              }
            }, _callee2, this);
          }));

          function init() {
            return _init.apply(this, arguments);
          }

          return init;
        }()
      }
    };
    var EditPassword = {
      template: '#edit-password-template',
      data: function data() {
        return {
          loading: false,
          validators: [],
          pwd: '',
          pwd_conf: ''
        };
      },
      methods: {
        errorHandler: function errorHandler(item) {
          this.validators.push(item);
        },
        submitNewPassword: function submitNewPassword(ev) {
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
          })["catch"](function (err) {}).done(function () {
            self.loading = false;
          });
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

    var ProfilEdit = {
      template: "#profil-client-template",
      data: function data() {
        return {
          loading: false,
          validators: [],
          isCandidate: false,
          isEmployer: false,
          user: null,
          userCompany: null
        };
      },
      created: function created() {
        this.init();
      },
      computed: {
        hisRole: function hisRole() {
          return this.isCandidate ? 'Candidat' : 'Employeur';
        }
      },
      methods: {
        submitProfil: function submitProfil(ev) {
          ev.preventDefault();
        },
        init: function () {
          var _init2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
            var _this8 = this;

            return regeneratorRuntime.wrap(function _callee3$(_context3) {
              while (1) {
                switch (_context3.prev = _context3.next) {
                  case 0:
                    wp.api.loadPromise.done(function () {
                      _this8.loading = true;
                      var cUser = new wp.api.models.User({
                        id: clientApiSettings.current_user_id
                      });
                      cUser.fetch({
                        data: {
                          context: 'edit'
                        }
                      }).done(function (user) {
                        _this8.user = lodash.clone(user);
                        _this8.isCandidate = lodash.indexOf(user.roles, 'candidate') >= 0;
                        _this8.isEmployer = lodash.indexOf(user.roles, 'employer') >= 0; // If employer or company

                        if (_this8.isEmployer) {
                          var companyId = parseInt(user.meta.company_id, 10);
                          if (0 === companyId) return;
                          var companyModel = new wp.api.models.User({
                            id: companyId
                          });
                          companyModel.fetch({
                            data: {
                              context: 'edit'
                            }
                          }).done(function (companyResponse) {
                            _this8.userCompany = lodash.clone(companyResponse);
                            _this8.loading = false;
                          });
                        }
                      });
                    });

                  case 1:
                  case "end":
                    return _context3.stop();
                }
              }
            }, _callee3);
          }));

          function init() {
            return _init2.apply(this, arguments);
          }

          return init;
        }()
      }
    };
    var Home = {
      template: '#dashboard',
      components: {
        'comp-edit-pwd': EditPassword,
        'comp-edit-profil': ProfilEdit
      },
      data: function data() {
        return {
          loading: false
        };
      },
      methods: {}
    };
    var CVComponents = {
      experience: {
        props: ['year_range', 'item'],
        template: '#experience-template'
      },
      education: {
        props: ['year_range', 'item'],
        template: '#education-template'
      }
    };
    var CVComp = {
      template: '#client-cv',
      components: {
        'comp-education': CVComponents.education,
        'comp-experience': CVComponents.experience,
        'comp-cv-status': _componentCVStatus,
        'upload-avatar': _componentUploadAvatar
      },
      beforeRouteLeave: function beforeRouteLeave(to, from, next) {
        var answer = window.confirm('Do you really want to leave? you have unsaved changes!');

        if (answer) {
          next();
        } else {
          next(false);
        }
      },
      data: function data() {
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
          profil: "",
          // Biographie
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
            e: '',

            /** end year */
            locked: 1
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
            locked: 1
          },
          WPApiModel: null,
          Emploi: null
        };
      },
      created: function created() {
        var currentDate = new Date();
        this.yearRange = lodash.range(1950, currentDate.getFullYear());
      },
      mounted: function () {
        var _mounted = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
          var _this9 = this;

          return regeneratorRuntime.wrap(function _callee4$(_context4) {
            while (1) {
              switch (_context4.prev = _context4.next) {
                case 0:
                  this.Loading = true;
                  _context4.next = 3;
                  return this.$parent.Wordpress.users().me().context('edit').then(function (response) {
                    _this9.currentUser = lodash.cloneDeep(response); //Populate data value

                    //Populate data value
                    _this9.first_name = _this9.currentUser.first_name;
                    _this9.last_name = _this9.currentUser.last_name;
                    _this9.phone = _this9.currentUser.meta.phone;
                    _this9.address = _this9.currentUser.meta.address;
                    _this9.gender = _this9.currentUser.meta.gender;
                    _this9.city = _this9.currentUser.meta.city;
                    _this9.birthday = _this9.currentUser.meta.birthday;
                    _this9.profil = _this9.currentUser.meta.profil;
                    _this9.region = _this9.currentUser.meta.region;
                    var languages = _this9.currentUser.meta.languages;
                    languages = lodash.isEmpty(languages) ? [] : JSON.parse(languages);
                    _this9.languages = lodash.clone(languages);
                    var categories = _this9.currentUser.meta.categories;
                    categories = lodash.isEmpty(categories) ? [] : JSON.parse(categories);
                    _this9.categories = lodash.clone(categories);
                    _this9.hasCV = !!_this9.currentUser.meta.has_cv;
                    _this9.publicCV = !!_this9.currentUser.validated;
                    _this9.Loading = false;
                  });

                case 3:
                  // Education sortable list
                  new Sortable(document.getElementById('education-list'), {
                    handle: '.edu-history',
                    // handle's class
                    animation: 150,
                    // Element dragging ended
                    onEnd: function onEnd(
                    /**Event*/
                    evt) {
                      var itemEl = evt.item; // dragged HTMLElement

                      evt.to; // target list

                      evt.from; // previous list

                      evt.oldIndex; // element's old index within old parent

                      evt.newIndex; // element's new index within new parent

                      evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements

                      evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements

                      evt.clone; // the clone element

                      evt.pullMode; // when item is in another sortable: `"clone"` if cloning, `true` if moving

                      console.log(evt);
                    }
                  }); // Recuperer les langues

                  fetch(clientApiSettings.root + 'wp/v2/language?per_page=50').then(function (res) {
                    res.json().then(function (json) {
                      return _this9.optLanguages = json;
                    });
                  }); // Recuperer les categories

                  fetch(clientApiSettings.root + 'wp/v2/categories?per_page=50').then(function (res) {
                    res.json().then(function (json) {
                      return _this9.optCategories = json;
                    });
                  }); // Recuperer les items de region

                  fetch(clientApiSettings.root + 'wp/v2/region?per_page=50').then(function (res) {
                    res.json().then(function (json) {
                      return _this9.optRegions = json;
                    });
                  });

                case 7:
                case "end":
                  return _context4.stop();
              }
            }
          }, _callee4, this);
        }));

        function mounted() {
          return _mounted.apply(this, arguments);
        }

        return mounted;
      }(),
      computed: {
        getExperiences: function getExperiences() {
          var experiences = this.getMeta('experiences');
          var response = lodash.isEmpty(experiences) ? [] : JSON.parse(experiences);
          return response;
        },
        getEducations: function getEducations() {
          var educations = this.getMeta('educations');
          var response = lodash.isEmpty(educations) ? [] : JSON.parse(educations);
          return response;
        }
      },
      methods: {
        errorHandler: function errorHandler(field) {
          return "Le champ <b>\"".concat(field, "\"</b> est obligatoire");
        },
        getMeta: function getMeta(value) {
          var metaValue = lodash.isNull(this.currentUser) ? JSON.stringify([]) : typeof this.currentUser.meta == 'undefined' ? JSON.stringify([]) : this.currentUser.meta[value];
          return metaValue;
        },
        updateExperiences: function updateExperiences(data) {
          var self = this;
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
          })["catch"](function (err) {
            self.Loading = false;
          });
        },
        updateEducations: function updateEducations(data) {
          var self = this;
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
          })["catch"](function (err) {
            self.Loading = false;
          });
        },
        resetExperience: function resetExperience() {
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
            desc: ''
          };
          this.formExpSelected = null;
        },
        resetEducation: function resetEducation() {
          this.formEduEdit = {
            _id: getRandomId(),
            establishment: '',
            diploma: '',
            city: '',
            country: '',
            b: '',

            /** begin year */
            e: ''
            /** end year */

          };
          this.formEduSelected = null;
        },

        /** Envt click button modal */
        addExperience: function addExperience() {
          this.resetExperience();
          $('#experience').modal('show');
        },
        addEducation: function addEducation() {
          this.resetEducation();
          $('#education').modal('show');
        },
        editExperience: function editExperience(evt, id) {
          evt.preventDefault();
          var self = this;
          var experiences = this.getExperiences;
          var expSelected = lodash.find(experiences, function (exp) {
            return exp._id === id;
          });
          Object.keys(expSelected).forEach(function (item, index) {
            self.formExpEdit[item] = expSelected[item];
          });
          this.formExpSelected = id;
          $('#experience').modal('show');
        },
        deleteExperience: function deleteExperience(evt, id) {
          evt.preventDefault();
          var experiences = this.getMeta('experiences');
          var currentExperiences = lodash.remove(experiences, function (exp) {
            return exp._id === id;
          });
          this.updateExperiences(currentExperiences);
        },
        deleteEducation: function deleteEducation(evt, id) {
          evt.preventDefault();
          var educations = this.getMeta('educations');
          var currentEducations = lodash.remove(educations, function (edu) {
            return edu._id === id;
          });
          this.updateEducations(currentEducations);
        },
        editEducation: function editEducation(evt, id) {
          evt.preventDefault();
          var self = this;
          var educations = this.getEducations;
          var eduSelected = lodash.find(educations, {
            _id: id
          });
          Object.keys(eduSelected).forEach(function (item, index) {
            self.formEduEdit[item] = eduSelected[item];
          });
          this.formEduSelected = id;
          $('#education').modal('show');
        },
        validateExpForm: function validateExpForm(ev) {
          ev.preventDefault();
          this.expValidator = [];
          var form = this.formExpEdit;

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
            this.expValidator.push(this.errorHandler('Année de début'));
          }

          if (!lodash.isEmpty(this.expValidator)) {
            return;
          }

          this.submitExpForm();
        },
        validateEduForm: function validateEduForm(ev) {
          ev.preventDefault();
          this.eduValidator = [];
          var form = this.formEduEdit;

          if (lodash.isEmpty(form.city)) {
            this.eduValidator.push(this.errorHandler('Ville'));
          }

          if (lodash.isEmpty(form.country)) {
            this.eduValidator.push(this.errorHandler('Pays'));
          }

          if (lodash.isEmpty(form.diploma)) {
            this.eduValidator.push(this.errorHandler('Diplôme'));
          }

          if (lodash.isEmpty(form.establishment)) {
            this.eduValidator.push(this.errorHandler('Etablissement'));
          }

          if (!form.b) {
            this.eduValidator.push(this.errorHandler('Année de début'));
          }

          if (!lodash.isEmpty(this.eduValidator)) {
            return;
          }

          this.submitEduForm();
        },
        submitExpForm: function submitExpForm() {
          var self = this;
          var experiences = this.getExperiences;

          if (this.formExpSelected === null) {
            experiences.push(this.formExpEdit);
          } else {
            /** update exist experience */
            experiences = lodash.map(experiences, function (exp) {
              if (exp._id === self.formExpSelected) {
                Object.keys(exp).forEach(function (expKey) {
                  exp[expKey] = self.formExpEdit[expKey];
                });
              }

              return exp;
            });
          }

          this.updateExperiences(experiences);
        },
        submitEduForm: function submitEduForm() {
          var self = this;
          var educations = this.getEducations;

          if (this.formEduSelected === null) {
            educations.push(this.formEduEdit);
          } else {
            /** update exist experience */
            educations = lodash.map(educations, function (edu) {
              if (edu._id === self.formEduSelected) {
                Object.keys(edu).forEach(function (key) {
                  edu[key] = self.formEduEdit[key];
                });
              }

              return edu;
            });
          }

          this.updateEducations(educations);
        },
        submitCV: function submitCV(ev) {
          ev.preventDefault();
          var self = this;
          var educations = this.getMeta('educations');
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
          } // Verifier s'il y a au moins une colonne pour l'education


          var msgEducationEmpty = "Ajoutez au moins un parcour à votre CV";

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

          var _languages = JSON.stringify(this.languages);

          var _categories = JSON.stringify(this.categories);

          var userId = parseInt(clientApiSettings.current_user_id);
          this.$parent.Wordpress.users().me().update({
            last_name: this.last_name,
            first_name: this.first_name,
            validated: this.publicCV,
            meta: {
              phone: this.phone,
              address: this.address,
              gender: this.gender,
              region: this.region,
              city: this.city,
              languages: _languages,
              categories: _categories,
              birthday: this.birthday,
              reference: "CV".concat(userId),
              profil: this.profil,
              // Render visible this CV
              has_cv: true
            }
          }).then(function (resp) {
            alertify.alert("Votre CV a bien été enregistré, il sera publié après une " + "verification en interne par notre équipe RH.");
            self.Loading = false;
            self.hasCV = true;
          })["catch"](function (er) {
            self.Loading = false;
          });
        }
      }
    };
    var CompanyComp = {
      template: '#create-company',
      components: {
        'upload-avatar': _componentUploadAvatar
      },
      data: function data() {
        return {
          loading: false,
          sectionClass: 'utf_create_company_area padd-bot-80',
          wpapi: new WPAPI({
            endpoint: clientApiSettings.root,
            nonce: clientApiSettings.nonce
          }),
          account_id: 0,
          company_account: {},
          isUpdate: false,
          categories: [],
          countries: [],
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
            employees: 0,
            description: ''
          }
        };
      },
      methods: {
        initComponent: function initComponent() {
          var _this10 = this;

          wp.api.loadPromise.done( /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
            var wpCatsModel, wpCountryModel, categories, countries;
            return regeneratorRuntime.wrap(function _callee5$(_context5) {
              while (1) {
                switch (_context5.prev = _context5.next) {
                  case 0:
                    _this10.loading = true;
                    _this10.account_id = clientApiSettings.current_user_id;
                    wpCatsModel = new wp.api.collections.Categories();
                    wpCountryModel = new wp.api.collections.Country();
                    _context5.next = 6;
                    return wpCatsModel.fetch({
                      data: {
                        per_page: 50
                      }
                    });

                  case 6:
                    categories = _context5.sent;
                    _context5.next = 9;
                    return wpCountryModel.fetch({
                      data: {
                        per_page: 50
                      }
                    });

                  case 9:
                    countries = _context5.sent;
                    axios.all([categories, countries]).then(axios.spread(function () {
                      _this10.categories = lodash.clone(arguments.length <= 0 ? undefined : arguments[0]);
                      _this10.countries = lodash.clone(arguments.length <= 1 ? undefined : arguments[1]);
                    }))["catch"](function (errors) {});

                    _this10.wpapi.users().me().context('edit').then(function (response) {
                      var me = lodash.cloneDeep(response);
                      var hasCompany = me.meta.company_id !== 0;

                      if (hasCompany) {
                        // S'il possede deja une entreprise
                        var wpCompanyModel = new wp.api.models.User({
                          id: me.meta.company_id
                        });
                        wpCompanyModel.fetch({
                          data: {
                            context: 'edit'
                          }
                        }).done(function (company) {
                          _this10.isUpdate = true;
                          _this10.company_account = lodash.clone(company); // Ajouter les valeurs dans le formulaires

                          _this10.formData = {
                            name: company.username,
                            category: company.meta.category,
                            // email: company.email,
                            address: company.meta.address,
                            nif: company.meta.nif,
                            stat: company.meta.stat,
                            phone: company.meta.phone,
                            country: company.meta.country,
                            city: company.meta.city,
                            zipcode: company.meta.zipcode,
                            website: company.meta.website,
                            employees: company.meta.employees,
                            description: company.description
                          };
                          _this10.loading = false;
                        });
                      } else {
                        _this10.loading = false;
                      }
                    })["catch"](function (err) {
                      _this10.loading = false;
                    });

                  case 12:
                  case "end":
                    return _context5.stop();
                }
              }
            }, _callee5);
          })));
        },
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
            this.updateCompany(data);
          }
        },
        updateCompany: function () {
          var _updateCompany = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee6(item) {
            var _this11 = this;

            var randomMail, _email, _name, request;

            return regeneratorRuntime.wrap(function _callee6$(_context6) {
              while (1) {
                switch (_context6.prev = _context6.next) {
                  case 0:
                    randomMail = getRandomPassword(10);
                    _email = "".concat(randomMail, "@jobjiaby.com");
                    _name = item.name; // Upload avatar

                    this.loading = true;
                    request = null;

                    if (this.isUpdate) {
                      request = this.wpapi.users().id(this.company_account.id).update({
                        description: item.description,
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
                          employer_id: clientApiSettings.current_user_id
                        }
                      });
                    } else {
                      request = this.wpapi.users().create({
                        name: _name,
                        nickname: _email,
                        username: _name,
                        password: getRandomPassword(),
                        email: _email,
                        first_name: "",
                        last_name: "",
                        roles: ['company'],
                        description: item.description,
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
                          employer_id: clientApiSettings.current_user_id
                        }
                      });
                    } // Callback


                    request.then(function (user) {
                      // Add this company for the employee
                      _this11.wpapi.users().me().update({
                        meta: {
                          company_id: user.id
                        }
                      }).then(function () {
                        alertify.notify("Donnee mis a jour avec succes", 'success');
                        _this11.loading = false;
                      });
                    })["catch"](function (err) {
                      _this11.loading = false;

                      _this11.errorHandler(err);
                    });

                  case 7:
                  case "end":
                    return _context6.stop();
                }
              }
            }, _callee6, this);
          }));

          function updateCompany(_x) {
            return _updateCompany.apply(this, arguments);
          }

          return updateCompany;
        }(),
        errorHandler: function errorHandler(response) {
          alertify.alert(response.code, response.message);
        },
        formatHTML: function formatHTML(str) {
          return str.replace(/(<([^>]+)>)/ig, "");
        }
      },
      created: function created() {
        this.initComponent();
      },
      mounted: function mounted() {
        $('select').dropdown({
          clearable: true,
          placeholder: ''
        });
      }
    };
    var AnnonceComp = {
      template: "#client-annonce",
      data: function data() {
        return {
          loading: false,
          annonces: []
        };
      },
      created: function created() {
        this.Populate();
      },
      methods: {
        trashAnnonce: function trashAnnonce(ev, jobId) {
          var _this12 = this;

          ev.preventDefault();
          alertify.confirm("Voulez vous vraiment supprimer cette annonce. ID: " + jobId, function () {
            _this12.loading = true;

            _this12.$parent.Wordpress.jobs().id(jobId).update({
              status: 'private'
            }).then(function () {
              _this12.Populate();
            });
          }, function () {});
        },
        Populate: function Populate() {
          var _this13 = this;

          this.loading = true;
          this.$parent.Wordpress.jobs().status(['pending', 'publish', 'private']).param('meta_key', 'employer_id').param('meta_value', clientApiSettings.current_user_id).per_page(10).then(function (response) {
            _this13.annonces = lodash.map(response, function (annonce) {
              var title = annonce.title.rendered;
              annonce.title.rendered = lodash.truncate(title, {
                'length': 35,
                'separator': '[...]'
              });
              return annonce;
            });
            _this13.loading = false;
          });
        }
      }
    };
    var AnnonceDetails = {
      template: "#annonce-apply",
      data: function data() {
        return {
          loading: false,
          job: null,
          candidateApply: [],
          jHTTPInstance: null
        };
      },
      mounted: function mounted() {
        var _this14 = this;

        this.jHTTPInstance = axios.create({
          baseURL: clientApiSettings.root + 'job/v2',
          headers: {
            'X-WP-Nonce': clientApiSettings.nonce
          }
        });
        this.loading = true;
        var job_id = this.$route.params.id;
        this.jHTTPInstance.get("".concat(job_id, "/apply")).then(function (response) {
          var details = response.data;

          if (details.success) {
            _this14.candidateApply = details.data.candidates;
            _this14.job = lodash.clone(details.data.job);
          }

          _this14.loading = false;
        })["catch"](function () {
          this.loading = false;
        });
      },
      methods: {},
      computed: {}
    };
    var AdApplied = {
      template: "#ad-applied",
      data: function data() {
        return {
          jobs: [],
          loading: false
        };
      },
      mounted: function mounted() {
        this.initComponent();
      },
      methods: {
        initComponent: function initComponent() {
          var _this15 = this;

          this.loading = true;
          var clientId = clientApiSettings.current_user_id;
          axios.get(clientApiSettings.ajax_url, {
            params: {
              cid: clientId,
              action: 'ad_handler_apply'
            }
          }).then(function (resp) {
            if (resp.status === 200) {
              var jobs = lodash.clone(resp.data);
              jobs = lodash.map(jobs, function (job) {
                return lodash.isNull(job.id) ? null : job;
              });
              _this15.jobs = lodash.compact(jobs);
            }

            _this15.loading = false;
          });
        }
      }
    };
    var PricingLayout = {
      template: "<div><router-view></router-view></div>"
    };
    var PricingTable = {
      template: "#pricing-table",
      components: {
        'comp-pricing': _componentPricing
      },
      data: function data() {
        return {
          loading: false,
          products: []
        };
      },
      created: function created() {
        var _this16 = this;

        this.loading = true;
        jobHTTPInstance.get('pricing').then(function (resp) {
          if (resp.status === 200) {
            _this16.products = lodash.clone(resp.data);
          }

          _this16.loading = false;
        });
        /**
         * Effectuer un paiement direct
         */
        // const jWCHTTPInstance = axios.create({
        //     baseURL: clientApiSettings.root + 'wc/v2',
        //     headers: {'X-WP-Nonce': clientApiSettings.nonce}
        // });
        // jWCHTTPInstance.get(clientApiSettings.root + 'wc/v2/pricing/176/11').then((resp) => {
        //     console.log(resp);
        // });
      }
    };
    var PricingPurchase = {};
    var routes = [{
      path: '/',
      component: Layout,
      redirect: '/home',
      children: [{
        path: 'home',
        name: 'Home',
        props: true,
        component: Home
      }, {
        path: 'cv',
        name: 'CV',
        component: CVComp
      }, {
        path: 'jobs',
        name: 'Annonce',
        component: AnnonceComp
      }, {
        path: 'company',
        name: 'Company',
        component: CompanyComp
      }, {
        path: 'job/:id/details',
        name: 'AnnonceDetails',
        component: AnnonceDetails
      }, {
        path: 'ad_applied',
        name: 'AdApplied',
        component: AdApplied
      }, {
        path: 'pricing',
        name: 'Pricing',
        component: PricingLayout,
        redirect: '/pricing/items',
        children: [{
          path: 'items',
          name: 'PricingTable',
          component: PricingTable
        }, {
          path: ':id/purchase',
          name: 'PricingPurchase',
          component: PricingPurchase
        }]
      }],
      beforeEnter: function beforeEnter(to, from, next) {
        var isAuth = parseInt(clientApiSettings.current_user_id) !== 0;
        if (to.name != 'Login' && !isAuth) next({
          name: 'Login'
        });else next();
      }
    }, {
      path: '/login',
      name: 'Login',
      component: CompLogin,
      beforeEnter: function beforeEnter(to, from, next) {
        if (parseInt(clientApiSettings.current_user_id) !== 0) next({
          name: 'Home'
        });else next();
      }
    }];
    var router = new VueRouter({
      routes: routes // short for `routes: routes`

    });
    new Vue({
      el: '#client',
      router: router
    });
  });
})(jQuery);