"use strict";

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

(function ($, _) {
  $().ready(function () {
    var jobGrid = {
      template: "#job-grid-template",
      props: ['item', 'taxonomies'],
      data: function data() {
        return {
          defaultAvatarSrc: ''
        };
      },
      created: function created() {
        this.defaultAvatarSrc = this.item.company.avatar_urls[96];
      },
      computed: {
        avatarSrc: function avatarSrc() {
          var company = this.item.company;
          var avatar = company.avatar;
          return _.isEmpty(avatar) ? this.defaultAvatarSrc : avatar.upload_dir.baseurl + '/' + avatar.image.file;
        }
      },
      filters: {
        jobType: function jobType(value, Tax) {
          if (!lodash.isArray(value)) return '';
          var firstValue = value[0];

          var result = _.find(Tax.Types, {
            id: parseInt(firstValue)
          });

          return result.name;
        },
        jobCategories: function jobCategories(value, Tax) {
          if (!lodash.isArray(value)) return '';
          var firstValue = value[0];

          var result = _.find(Tax.Categories, {
            id: parseInt(firstValue)
          });

          return result.name;
        },
        capitalize: function capitalize(value) {
          if (!value) return '';
          value = value.toString();
          return value.charAt(0).toUpperCase() + value.slice(1);
        }
      },
      delimiters: ['${', '}']
    }; // Application

    wp.api.loadPromise.done(function () {
      new Vue({
        el: '#job-grid',
        components: {
          'job-grid': jobGrid
        },
        data: function data() {
          return {
            loading: false,
            Taxonomies: {},
            EmploiCollection: null,
            axiosInstance: null,
            itemsCount: 7,
            moreClickCount: 0,
            jobs: []
          };
        },
        created: function created() {
          if (typeof apiSettings === 'undefined') {
            return;
          }

          this.axiosInstance = axios.create({
            baseURL: apiSettings.root + 'wp/v2',
            headers: {
              'X-WP-Nonce': apiSettings.nonce
            }
          });
          this.init();
        },
        methods: {
          init: function () {
            var _init = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
              var _this = this;

              var self, categoriesRequest, typesRequest;
              return regeneratorRuntime.wrap(function _callee$(_context) {
                while (1) {
                  switch (_context.prev = _context.next) {
                    case 0:
                      self = this;
                      this.loading = true;
                      categoriesRequest = this.axiosInstance.get('categories?per_page=50');
                      typesRequest = this.axiosInstance.get('job_type?per_page=50');
                      _context.next = 6;
                      return axios.all([typesRequest, categoriesRequest]).then(axios.spread(function () {
                        for (var _len = arguments.length, responses = new Array(_len), _key = 0; _key < _len; _key++) {
                          responses[_key] = arguments[_key];
                        }

                        self.Taxonomies.Categories = lodash.clone(responses[1].data);
                        self.Taxonomies.Types = lodash.clone(responses[0].data);
                      }))["catch"](function (errors) {});

                    case 6:
                      this.EmploiCollection = new wp.api.collections.Emploi();
                      this.EmploiCollection.fetch({
                        data: {
                          per_page: this.itemsCount,
                          orderby: 'date',
                          order: 'desc'
                        }
                      }).then(function (resp) {
                        _this._build(resp);

                        _this.loading = false;
                      });

                    case 8:
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
          _build: function _build(jobs) {
            this.jobs = _.map(jobs, function (job) {
              var title = job.title.rendered;
              job.title.truncate = _.truncate(title, {
                length: 17,
                separator: '...'
              });
              return job;
            });
          },
          moreEmploi: function moreEmploi(ev) {
            var _this2 = this;

            if (this.moreClickCount >= 2 || !this.EmploiCollection.hasMore()) {
              return true;
            }

            ev.preventDefault();

            if (this.EmploiCollection.hasMore()) {
              this.loading = true;
              this.EmploiCollection.more().then(function (resp) {
                var jobs = _this2.jobs.concat(resp);

                _this2._build(jobs);

                _this2.loading = false;
              });
            }

            this.moreClickCount += 1;
            return false;
          }
        },
        delimiters: ['${', '}']
      });
    });
  });
})(jQuery, lodash);