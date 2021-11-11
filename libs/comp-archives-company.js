"use strict";

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

(function ($) {
  $().ready(function () {
    var Layout = {
      template: '#layout-archive',
      data: function data() {
        return {};
      }
    };
    var _compUserAvatar = {
      props: ['user', 'class_css'],
      template: "#avatar-template",
      data: function data() {
        return {
          loading: false,
          defaultAvatar: 'https://1.gravatar.com/avatar/a4d6419d4de237c812db96deae8999de?s=96&d=mm&r=g'
        };
      },
      computed: {
        getUrl: function getUrl() {
          var avatar = this.user.avatar;
          return _.isEmpty(avatar) ? this.defaultAvatar : avatar.upload_dir.baseurl + '/' + avatar.image.file;
        }
      }
    };
    var ArchivesCompanies = {
      template: '#company-archive-item',
      components: {
        'comp-avatar': _compUserAvatar
      },
      data: function data() {
        return {
          loading: false,
          totals: 0,
          page: 1,
          //default
          companies: [],
          companyCollections: null
        };
      },
      mounted: function mounted() {
        this.initComponent();
      },
      methods: {
        initComponent: function initComponent() {
          var _this = this;

          this.loading = true;

          try {
            // Initialise collection api
            this.companyCollections = axios.create({
              baseURL: apiSettings.root + 'job/v2',
              headers: {
                'X-WP-Nonce': apiSettings.nonce
              }
            });
            this.companyCollections.get("/companies").then(function (resp) {
              if (resp.status === 200) {
                var results = resp.data;
                _this.totals = results.total;
                _this.companies = lodash.clone(results.data);
              }

              _this.loading = false;
            });
          } catch (e) {
            this.loading = false;
            console.warn(e);
          }
        }
      }
    };
    var JobsCompany = {
      props: ['employerid'],
      components: {
        'comp-avatar': _compUserAvatar
      },
      template: "#company-jobs",
      data: function data() {
        return {
          loading: false,
          jobs: []
        };
      },
      created: function created() {
        this.initComponent();
      },
      methods: {
        initComponent: function initComponent() {
          var _this2 = this;

          var employerCollection = new wp.api.collections.Emploi();
          this.loading = true;
          employerCollection.fetch({
            data: {
              per_page: 8,
              meta_key: 'employer_id',
              meta_value: this.employerid
            }
          }).then(function (resp) {
            try {
              _this2.jobs = lodash.map(resp, function (job) {
                var title = job.title.rendered;
                job.title_truncate = lodash.truncate(title, {
                  length: 21,
                  separator: '...'
                });
                return job;
              });
              _this2.loading = false;
            } catch (e) {
              console.warn(e);
              _this2.loading = false;
            }
          });
        }
      }
    };
    var SingleCompany = {
      template: '#company-details',
      components: {
        'comp-company-jobs': JobsCompany,
        'comp-avatar': _compUserAvatar
      },
      data: function data() {
        return {
          companyId: 0,
          employerId: 0,
          loading: false,
          company: null
        };
      },
      created: function created() {
        this.initComponent();
      },
      methods: {
        initComponent: function () {
          var _initComponent = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
            var _this3 = this;

            var InstanceAxios, employerResponse;
            return regeneratorRuntime.wrap(function _callee$(_context) {
              while (1) {
                switch (_context.prev = _context.next) {
                  case 0:
                    this.companyId = parseInt(this.$route.params.id, 10); // Initialise collection api

                    InstanceAxios = axios.create({
                      baseURL: apiSettings.root + 'job/v2',
                      headers: {
                        'X-WP-Nonce': apiSettings.nonce
                      }
                    });
                    this.loading = true; // Recuperer l'employer de cette entreprise

                    _context.next = 5;
                    return InstanceAxios.get("/companies/".concat(this.companyId, "/employer"));

                  case 5:
                    employerResponse = _context.sent;

                    // Recuperer l'entreprise
                    if (employerResponse.status === 200) {
                      InstanceAxios.get("/companies/".concat(this.companyId)).then(function (resp) {
                        var employer = employerResponse.data.data;
                        _this3.employerId = employer.id;

                        if (resp.status === 200) {
                          var responseHTTP = resp.data;

                          if (responseHTTP.success) {
                            _this3.company = lodash.clone(responseHTTP.data);
                          } else {
                            // Rediriger vers la page d'accueil si l'entreprise est introuvable
                            window.location.href = window.location.origin;
                          }
                        }

                        _this3.loading = false;
                      });
                    } else {
                      this.loading = false;
                    }

                  case 7:
                  case "end":
                    return _context.stop();
                }
              }
            }, _callee, this);
          }));

          function initComponent() {
            return _initComponent.apply(this, arguments);
          }

          return initComponent;
        }()
      }
    };
    var routes = [{
      path: '/',
      component: Layout,
      redirect: '/companies',
      children: [{
        path: 'companies',
        name: 'Companies',
        component: ArchivesCompanies
      }, {
        path: 'companies/:id',
        name: 'SingleCompany',
        component: SingleCompany
      }]
    }];
    var router = new VueRouter({
      routes: routes // short for `routes: routes`

    });
    new Vue({
      el: '#company-archive',
      router: router
    });
  });
})(jQuery);