"use strict";

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

(function ($) {
  $().ready(function () {
    moment.locale('fr');
    var jobHTTPInstance = axios.create({
      baseURL: apiSettings.root + 'job/v2',
      headers: {
        'X-WP-Nonce': apiSettings.nonce
      }
    });
    var Pagination = {
      template: '#pagination-candidate-template',
      props: ['paging', 'pagesize'],
      data: function data() {
        return {
          source: []
        };
      },
      mounted: function mounted() {
        var self = this;

        if (typeof this.paging.totalPages !== 'undefined') {
          this.source = lodash.range(0, parseInt(this.paging.totalPages));
        } // Pagination view: http://pagination.js.org/docs/index.html


        $('#pagination-archive').pagination({
          dataSource: self.source,
          pageSize: self.pagesize,
          ulClassName: 'pagination',
          className: '',
          callback: function callback(data, pagination) {},
          beforePageOnClick: function beforePageOnClick(el) {
            var page = el.currentTarget;
            var data = page.dataset;
            self.$emit('change-route-page', parseInt(data.num));
          }
        });
      },
      methods: {},
      watch: {
        paging: function paging() {
          if (typeof this.paging.totalPages === 'undefined') return [];
          this.source = lodash.range(0, parseInt(this.paging.totalPages));
          return this.paging;
        }
      }
    };
    var Layout = {
      template: '#layout-archive'
    };
    var ArchivesCandidate = {
      template: '#candidate-archive-item',
      components: {
        'com-pagination': Pagination
      },
      data: function data() {
        return {
          loading: false,
          s: '',
          region: '',
          cat: '',
          request: '',
          per_page: 10,
          // default
          page: 1,
          //default
          paging: null,
          annonces: [],
          categories: [],
          wpAxiosInstance: null,
          wordpress: null
        };
      },
      created: function created() {
        if (typeof apiSettings === 'undefined') return;
        this.wpAxiosInstance = axios.create({
          baseURL: apiSettings.root + 'wp/v2',
          headers: {
            'X-WP-Nonce': apiSettings.nonce
          }
        });
        this.wordpress = new WPAPI({
          endpoint: apiSettings.root,
          nonce: apiSettings.nonce
        });
        this.init();
      },
      methods: {
        init: function () {
          var _init = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
            var _this = this;

            var self, categoriesRequest;
            return regeneratorRuntime.wrap(function _callee$(_context) {
              while (1) {
                switch (_context.prev = _context.next) {
                  case 0:
                    self = this;
                    this.loading = true;
                    this.request = this.wordpress.users() //.param('roles', 'candidate') // Not allow for client not logged in
                    .param('validated', 1).param('has_cv', 1).param('blocked', 0).perPage(this.per_page).page(this.page);
                    categoriesRequest = this.wpAxiosInstance.get('categories?per_page=50');
                    _context.next = 6;
                    return axios.all([categoriesRequest]).then(axios.spread(function () {
                      for (var _len = arguments.length, responses = new Array(_len), _key = 0; _key < _len; _key++) {
                        responses[_key] = arguments[_key];
                      }

                      self.categories = lodash.clone(responses[0].data);
                    }))["catch"](function (errors) {});

                  case 6:
                    this.request.then(function (resp) {
                      _this._buildAnnonceHandler(resp);

                      _this.loading = false;
                    });

                  case 7:
                  case "end":
                    return _context.stop();
                }
              }
            }, _callee, this);
          }));

          function init() {
            return _init.apply(this, arguments);
          }

          return init;
        }(),
        _buildAnnonceHandler: function _buildAnnonceHandler(wpResponse) {
          var _this2 = this;

          var annonces = lodash.clone(wpResponse);
          this.annonces = lodash.map(annonces, function (annonce) {
            annonce.job = '';
            var currentCategories = JSON.parse(annonce.meta.categories);
            if (!lodash.isArray(currentCategories)) return annonce;
            var job = lodash.find(_this2.categories, {
              'id': lodash.head(currentCategories)
            });
            if (lodash.isUndefined(job) || !job) return annonce;
            annonce.job = job.name;
            return annonce;
          });
          this.paging = lodash.isUndefined(wpResponse._paging) ? null : wpResponse._paging;
        },
        filterHandler: function filterHandler(ev) {
          ev.preventDefault();
          var self = this;
          this.loading = true;
          var filterReq = this.request.param('s', this.s).param('region', this.region).param('cat', this.cat).get();
          filterReq.then(function (resp) {
            self._buildAnnonceHandler(resp);

            self.loading = false;
          });
        },
        Route: function Route(page) {
          var _this3 = this;

          if (page === this.page) return;
          this.page = page; // Promise response

          var archivesPromise = this.request.per_page(this.per_page).page(this.page).get();
          this.loading = true;
          archivesPromise.then(function (response) {
            _this3._buildAnnonceHandler(response);

            _this3.loading = false;
          });
        }
      }
    };
    var SingleCandidate = {
      template: '#candidate-details',
      data: function data() {
        return {
          loading: false,
          status: [],
          candidate: null,
          crtCandidateLanguages: [],
          categories: [],
          regions: [],
          languages: [],
          experiences: [],
          educations: []
        };
      },
      computed: {
        hasCandidateLanguage: function hasCandidateLanguage() {
          return !lodash.isEmpty(this.crtCandidateLanguages);
        },
        statusToObj: function statusToObj() {
          // je cherche ...
          if (this.candidate === null) return null;
          if (typeof this.candidate.status._id == 'undefined') return null;
          return lodash.find(this.status, {
            '_id': this.candidate.status._id
          });
        },
        getRegisterDate: function getRegisterDate() {
          // Date d'inscription dans le site
          return moment(this.candidate.registered_date).format('LLL');
        }
      },
      mounted: function () {
        var _mounted = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
          var _this4 = this;

          var candidateInstance, responseCandidate, Candidate, axiosInstance, categoriesRequest, languagesRequest, regionsRequest, cvStatusRequest;
          return regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) {
              switch (_context2.prev = _context2.next) {
                case 0:
                  this.loading = true;
                  this.userId = parseInt(this.$route.params.id);
                  candidateInstance = axios.create({
                    baseURL: apiSettings.root + 'job/v2'
                  });
                  _context2.next = 5;
                  return candidateInstance.get("/candidate/".concat(this.userId));

                case 5:
                  responseCandidate = _context2.sent;
                  responseCandidate = responseCandidate.data;

                  if (responseCandidate) {
                    _context2.next = 10;
                    break;
                  }

                  alertify.error("Une erreur s'est produit");
                  return _context2.abrupt("return");

                case 10:
                  Candidate = lodash.cloneDeep(responseCandidate);
                  axiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                      'X-WP-Nonce': apiSettings.nonce
                    }
                  });
                  categoriesRequest = axiosInstance.get('categories?per_page=50');
                  languagesRequest = axiosInstance.get('language?per_page=50');
                  regionsRequest = axiosInstance.get('region?per_page=50');
                  cvStatusRequest = jobHTTPInstance.get('/cv-status');
                  _context2.next = 18;
                  return axios.all([categoriesRequest, languagesRequest, regionsRequest, cvStatusRequest]).then(axios.spread(function () {
                    for (var _len2 = arguments.length, responses = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
                      responses[_key2] = arguments[_key2];
                    }

                    _this4.categories = lodash.clone(responses[0].data);
                    _this4.languages = lodash.clone(responses[1].data);
                    _this4.regions = lodash.clone(responses[2].data);
                    _this4.status = lodash.clone(responses[3].data); // Populate

                    // Populate
                    var languages = Candidate.languages;
                    var useLg = lodash.isEmpty(languages) ? [] : languages;
                    _this4.crtCandidateLanguages = lodash.clone(useLg); // item categories

                    // item categories
                    Candidate.itemCategories = lodash.clone(Candidate.categories);
                  }));

                case 18:
                  this.experiences = Candidate.experiences;
                  this.educations = Candidate.educations;
                  this.candidate = lodash.clone(Candidate);
                  this.loading = false;

                case 22:
                case "end":
                  return _context2.stop();
              }
            }
          }, _callee2, this);
        }));

        function mounted() {
          return _mounted.apply(this, arguments);
        }

        return mounted;
      }()
    };
    var routes = [{
      path: '/',
      component: Layout,
      redirect: '/candidates',
      children: [{
        path: 'candidates',
        name: 'Archives',
        component: ArchivesCandidate
      }, {
        path: 'candidate/:id',
        name: 'UserDetails',
        component: SingleCandidate
      }]
    }]; // short for `routes: routes`

    var router = new VueRouter({
      routes: routes
    });
    wp.api.loadPromise.done(function () {
      Vue.component('v-select', VueSelect.VueSelect);
      new Vue({
        el: '#candidate-archive',
        router: router
      });
    });
  });
})(jQuery);