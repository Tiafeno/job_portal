jQuery(function ($) {
    $().ready(() => {
        // Wordpress api is loaded!
        wp.api.loadPromise.done(() => {
            const __compAdminEmploiEditor = {
                delimiters: ['${', '}'],
                template: "#comp-editor-template",
                props: ['u'],
                data: function () {
                    return {
                        loading: false,
                        inSearch: false,
                        emploi: null,
                        ptEmployers: [],
                        form: {
                            experience: 0,
                            address: '',
                            employer_id: 0,
                            company_id: 0
                        }
                    }
                },
                created: function () {
                    this.emploi = _.clone(this.u);
                    this._buildForm();
                },
                methods: {
                    _loadDropdown: function () {
                        setTimeout(() => {
                            $('.ui.dropdown').dropdown('restore defaults');
                            $('#employer-field input.search').on('keypress', (ev) => {
                                setTimeout(() => {
                                    this.eventSearchEmployer(ev);
                                }, 500);
                            });
                        }, 1000);
                    },
                    _buildForm: function () {
                        this.form.address = this.emploi.meta.address;
                        this.form.experience = this.emploi.meta.experience;
                        if (this.emploi.employer) {
                            const Employer = new wp.api.models.User({
                                id: parseInt(this.emploi.employer)
                            });
                            Employer.fetch({
                                    data: { context: 'view' }
                                })
                                .then((u) => { 
                                    this.ptEmployers.push(u);
                                    this.form.employer_id = this.emploi.employer;
                                 });
                        }
                        this._loadDropdown();
                    },
                    eventChangeEmployer: function (ev) {
                        ev.preventDefault();
    
                    },
                    eventSearchEmployer: function (ev) {
                        const query = ev.target.value;
                        if (this.inSearch || _.isEmpty(query)) return;
                        const user = new wp.api.collections.Users();
                        this.inSearch = true;
                        user.fetch({
                                data: {
                                    context: 'view',
                                    roles: ['company'],
                                    search: query
                                }
                            })
                            .then(U => {
                                this.ptEmployers = lodash.uniqBy(_.union(this.ptEmployers, U), function(e) { return e.id; });
                                this.inSearch = false;
                            });
                    },
                    submitForm: function (ev) {
                        ev.preventDefault();
                        this.loading = true;
                        const currentPost = new wp.api.models.Emploi({
                            id: this.emploi.id
                        });
                        currentPost.fetch({
                                data: {
                                    context: 'edit'
                                }
                            })
                            .then((employer) => {
                                currentPost.setMeta('experience', this.form.experience);
                                currentPost.setMeta('address', this.form.address);
                                currentPost.save().done(() => {
                                    this.loading = false;
                                    window.location.reload();
                                });
                            });
    
                    }
                }
            };
            new Vue({
                el: '#emploi_app',
                components: {
                    'comp-admin-emploi-editor': __compAdminEmploiEditor,
                },
                data: function () {
                    return {
                        loading: false,
                        post: null,
                        wpapi: new WPAPI({
                            endpoint: WPAPIEmploiSettings.root,
                            nonce: WPAPIEmploiSettings.nonce
                        }),
                    }
                },
                created: function () {
                    this.init();
                },
                methods: {
                    init: function () {
                        this.loading = true;
                        const post = new wp.api.models.Emploi({
                            id: WPAPIEmploiSettings.postId
                        });
                        post.fetch({
                            data: {
                                context: 'edit'
                            }
                        }).then(resp => {
                            this.post = resp;
                            this.loading = false;
                        });
                    },
                }
            });
        });
    });
});