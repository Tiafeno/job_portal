"use strict";

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

var urlSearchParams = new URLSearchParams(window.location.search);
var params = Object.fromEntries(urlSearchParams.entries());
var paramKeys = Object.keys(params); // return array of keys

(function ($, _) {
  $().ready(function () {
    var __compRegion = {
      props: ['regions'],
      template: "#filter-region-template",
      data: function data() {
        return {
          valueSelected: null,
          items: []
        };
      },
      created: function created() {
        if (_.indexOf(paramKeys, 'region') >= 0) {
          this.valueSelected = parseInt(params['region']);
        }

        if (_.isArray(this.regions)) {
          this.items = _.map(this.regions, function (cat) {
            return cat;
          });
        }
      },
      methods: {
        selectedFilter: function selectedFilter($event) {
          $event.preventDefault();
          var values = []; // Get all input selected

          var inputChecked = $('select.region-filter');
          inputChecked.each(function (index, el) {
            values.push($(el).val());
          });
          var inputName = inputChecked.attr('name');
          this.$emit('changed', values, inputName);
        }
      }
    };
    var __compCats = {
      props: ['categories'],
      template: "#filter-category-template",
      data: function data() {
        return {
          valueSelected: null,
          items: []
        };
      },
      created: function created() {
        if (_.indexOf(paramKeys, 'cat') >= 0) {
          this.valueSelected = parseInt(params['cat']);
        }

        if (_.isArray(this.categories)) {
          this.items = _.map(this.categories, function (cat) {
            return cat;
          });
        }
      },
      methods: {
        selectedFilter: function selectedFilter($event) {
          $event.preventDefault();
          var values = []; // Get all input selected

          var inputChecked = $($event.currentTarget);
          inputChecked.each(function (index, el) {
            values.push($(el).val());
          });
          var inputName = inputChecked.attr('name');
          this.$emit('changed', values, inputName);
        }
      }
    };
    var __compSalary = {
      props: ['salaries'],
      template: '#filter-salary-template',
      data: function data() {
        return {
          items: []
        };
      },
      created: function created() {
        if (_.isArray(this.salaries)) {
          this.items = _.map(this.salaries, function (salary) {
            var valueFloat = parseFloat(salary.name);
            var amount = valueFloat.toLocaleString("en-GB", {
              style: "currency",
              currency: "MGA",
              minimumFractionDigits: 0
            });
            salary.filter_name = '+ ' + amount.toString();
            return salary;
          });
        }
      },
      methods: {
        selectedFilter: function selectedFilter($event) {
          $event.preventDefault();
          var target = $event.target;
          var values = []; // Get all input selected

          var inputChecked = $('input:checked.salary-filter');
          inputChecked.each(function (index, el) {
            values.push($(el).val());
          });
          var inputName = inputChecked.attr('name');
          this.$emit('changed', values, inputName);
        }
      }
    };
    var __compContract = {
      props: ['contracts'],
      template: '#filter-contract-template',
      data: function data() {
        return {
          items: []
        };
      },
      created: function created() {
        if (_.isArray(this.contracts)) {
          this.items = _.clone(this.contracts);
        }
      },
      methods: {
        selectedFilter: function selectedFilter($event) {
          $event.preventDefault();
          var target = $event.target;
          var values = []; // Get all input selected

          var inputChecked = $('input:checked.contract-filter');
          inputChecked.each(function (index, el) {
            values.push($(el).val());
          });
          var inputName = inputChecked.attr('name');
          this.$emit('changed', values, inputName);
        }
      }
    };
    var __compSearch = {
      template: '#filter-search-template',
      data: function data() {
        return {};
      },
      methods: {
        searchKey: function searchKey(ev) {
          ev.preventDefault();
          var el = ev.target;

          if (ev.keyCode === 13) {
            // Enter press...
            var elValue = $(el).val();
            this.$emit('changed', elValue, 'search');
          }
        }
      }
    };
    var jobVerticalLists = {
      props: ['item'],
      template: "#job-vertical-lists",
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
          var avatar = this.item.company.avatar;
          return _.isEmpty(avatar) ? this.defaultAvatarSrc : avatar.upload_dir.baseurl + '/' + avatar.image.file;
        },
        getCompanyUrl: function getCompanyUrl() {
          return "".concat(archiveApiSettings.company_archive_url, "/#/companies/").concat(this.item.company.id);
        }
      }
    };
    var Pagination = {
      template: '#pagination-jobs-template',
      props: ['paging', 'pagesize'],
      data: function data() {
        return {
          source: []
        };
      },
      mounted: function mounted() {
        var self = this;

        if (typeof this.paging.totalPages !== 'undefined') {
          this.source = _.range(0, parseInt(this.paging.totalPages));
        } // Pagination view: http://pagination.js.org/docs/index.html


        $('#pagination-archive').pagination({
          dataSource: self.source,
          pageSize: 1,
          ulClassName: 'pagination',
          className: '',
          callback: function callback(data, pagination) {},
          beforePaging: function beforePaging(page) {
            self.$emit('change-route-page', parseInt(page), 'page');
          }
        });
      },
      methods: {},
      watch: {
        paging: function paging() {
          if (typeof this.paging.totalPages === 'undefined') return [];
          this.source = _.range(0, parseInt(this.paging.totalPages));
          return this.paging;
        }
      }
    };
    var archiveJobs = {
      template: "#job-archive-template",
      props: ['taxonomies'],
      components: {
        'filter-salary': __compSalary,
        'filter-search': __compSearch,
        'filter-region': __compRegion,
        'filter-category': __compCats,
        'filter-contract': __compContract,
        'job-vertical-lists': jobVerticalLists,
        'com-pagination': Pagination
      },
      data: function data() {
        return {
          loadArchive: false,
          archives: [],
          // content
          WPAPI: null,
          hasURLSearchParam: false,
          Request: {},
          // object request
          ParamsFilter: {},
          // for all params search filter
          paging: null,
          // content pagination
          per_page: 10,
          // per page default value
          page: 1,
          // default page value
          totalResults: 0,
          // total number results
          inputPerPages: _.range(10, 50, 10),
          // node api params
          _context: 'view',
          _status: 'publish'
        };
      },
      mounted: function mounted() {
        var _this = this;

        if (typeof archiveApiSettings === 'undefined') {
          return;
        }

        this.WPAPI = new WPAPI({
          endpoint: archiveApiSettings.root,
          nonce: archiveApiSettings.nonce
        });
        this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
          params: ['page', 'per_page', 'offset', 'context', 'param', 'search', 'filter']
        }); // Verifier s'il y a des parametres dans l'URL

        if (!_.isEmpty(paramKeys)) {
          this.hasURLSearchParam = true;
          paramKeys.forEach(function (valueKey) {
            _this.applyFilter(params[valueKey], valueKey, true);
          });
        }

        this.getRequest();
      },
      methods: {
        requestHandler: function requestHandler() {
          return this.Request = _.cloneDeep(this.WPAPI.jobs());
        },
        Route: function Route(page) {
          var view = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'per_page';

          /**
           * Changer les routes pour l'affichage des annonces
           * TODO: Ajouter les variables dans local storage pour avoir des valeur par default
           * @type {boolean}
           */
          var edited = false;

          if (view === 'per_page') {
            if (page === this.per_page) return;
            this.per_page = page;
            edited = true;
          }

          if (view === 'page') {
            if (page === this.page) return;
            this.page = page;
            edited = true;
          }

          if (edited) this.getRequest();
        },
        resetFilter: function resetFilter($event) {
          $event.preventDefault();
          this.ParamsFilter = {};
          this.getRequest(); // Reset input radio/checkbox filter

          $('#archive-jobs input[type="radio"]').prop('checked', false);
          $('#archive-jobs input[type="checkbox"]').prop('checked', false);
        },
        applyFilter: function applyFilter(data, TEvent) {
          var multipleFilter = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
          if (_.isEmpty(TEvent)) return;
          var _params = null;

          switch (TEvent) {
            case 'salaries':
              if (_.isEmpty(data)) {
                this.ParamsFilter.salaries = {};
                break;
              }

              _params = _.map(data, _.unary(parseInt));
              this.ParamsFilter.salaries = {
                props: 'salaries',
                type: 'taxonomy',
                param: _params
              };
              break;

            case 'job_type':
              if (_.isEmpty(data)) {
                this.ParamsFilter.job_type = {};
                break;
              }

              _params = _.map(data, _.unary(parseInt));
              this.ParamsFilter.job_type = {
                props: 'job_type',
                type: 'taxonomy',
                param: _params
              };
              break;

            case 'region':
              var _param = parseInt(data);

              if (_.indexOf([0, '0', ' ', ''], _param) >= 0) {
                this.ParamsFilter.region = {};
                break;
              }

              this.ParamsFilter.region = {
                props: 'region',
                type: 'taxonomy',
                param: _param
              };
              break;

            case 'cat':
              var catId = parseInt(data);

              if (_.indexOf([0, '0'], catId) >= 0) {
                this.ParamsFilter.cat = {};
                break;
              }

              this.ParamsFilter.cat = {
                props: 'categories',
                type: 'taxonomy',
                param: parseInt(catId)
              };
              break;

            case 'search':
              if (data === '' || data === ' ') {
                this.ParamsFilter.search = {};
                break;
              }

              this.ParamsFilter.search = {
                props: 'search',
                type: null,
                param: data.trim()
              };
              break;

            default:
              break;
          }

          if (!multipleFilter) this.getRequest();
        },
        getRequest: function getRequest() {
          var self = this; // Initialise request

          this.requestHandler();

          if (!_.isEmpty(this.ParamsFilter)) {
            var pKeys = Object.keys(this.ParamsFilter);
            pKeys.forEach(function (value) {
              // Recuperer le filtre
              var filter = self.ParamsFilter[value]; // Si le type du filtre est une taxonomie (salaries, region e.g)

              if (filter.type === 'taxonomy') {
                self.Request.param(filter.props, filter.param);
              } // La requete n'est pas le même pour la recherche par mot.
              // Ici c'est spécialement pour `search`


              if (filter.props === 'search') {
                self.Request.search(filter.param);
              }
            });
          } // Promise response


          var archivesPromise = this.Request.per_page(this.per_page).page(this.page).get();
          this.loadArchive = true;
          archivesPromise.then(function (response) {
            // Si la reponse est vide
            if (_.isEmpty(response)) {
              self.archives = [];
              self.paging = null;
              self.loadArchive = false;
              return;
            } // On recupere la reponse


            var archivesResponse = _.cloneDeep(response);

            self.paging = _.clone(response._paging); // Update paging value
            // Add property value

            self.archives = _.map(archivesResponse, function (archive) {
              var title = archive.title.rendered;
              archive.title.rendered = _.truncate(title, {
                length: 40,
                separator: ' '
              });
              archive.get_type_name = ''; // add type of contract for annonce

              archive.get_cat_name = '';
              var type = archive.job_type; // Type de contrat

              if (_.isArray(type) && !_.isEmpty(type)) {
                var i = _.head(type);

                var j = _.find(self.taxonomies.Types, {
                  'id': parseInt(i)
                });

                archive.get_type_name = j.name;
              } // Categorie


              var categories = archive.categories;

              if (_.isArray(categories) && !_.isEmpty(categories)) {
                var k = _.head(categories);

                var l = _.find(self.taxonomies.Categories, {
                  'id': parseInt(k)
                });

                archive.get_cat_name = l.name;
              }

              return archive;
            });
            self.loadArchive = false;
          });
        }
      }
    }; // Application

    new Vue({
      el: '#archive-jobs',
      components: {
        'comp-archive-jobs': archiveJobs
      },
      data: function data() {
        return {
          loading: false,
          Taxonomies: {
            Types: [],
            Salaries: [],
            Categories: [],
            Regions: []
          },
          axiosInstance: null,
          itemsCount: 8
        };
      },
      created: function created() {
        if (typeof archiveApiSettings === 'undefined') {
          return;
        }

        this.init();
      },
      methods: {
        init: function () {
          var _init = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
            var self, categoriesRequest, typesRequest, salaryRequest, regionRequest;
            return regeneratorRuntime.wrap(function _callee$(_context) {
              while (1) {
                switch (_context.prev = _context.next) {
                  case 0:
                    self = this;
                    this.axiosInstance = axios.create({
                      baseURL: archiveApiSettings.root + 'wp/v2',
                      headers: {
                        'X-WP-Nonce': archiveApiSettings.nonce
                      }
                    });
                    categoriesRequest = this.axiosInstance.get('categories?per_page=80&hide_empty=false');
                    typesRequest = this.axiosInstance.get('job_type?per_page=50&hide_empty=true');
                    salaryRequest = this.axiosInstance.get('salaries?per_page=50&hide_empty=true');
                    regionRequest = this.axiosInstance.get('region?per_page=50&hide_empty=true');
                    this.loading = true;
                    _context.next = 9;
                    return axios.all([typesRequest, categoriesRequest, salaryRequest, regionRequest]).then(axios.spread(function () {
                      for (var _len = arguments.length, responses = new Array(_len), _key = 0; _key < _len; _key++) {
                        responses[_key] = arguments[_key];
                      }

                      self.Taxonomies.Categories = _.clone(responses[1].data);
                      self.Taxonomies.Types = _.clone(responses[0].data);
                      self.Taxonomies.Salaries = _.clone(responses[2].data);
                      self.Taxonomies.Regions = _.clone(responses[3].data);
                      self.loading = false;
                    }))["catch"](function (errors) {});

                  case 9:
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
        }()
      }
    });
  });
})(jQuery, lodash);