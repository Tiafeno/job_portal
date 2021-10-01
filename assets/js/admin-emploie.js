jQuery(function ($) {
    $().ready(() => {
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
                    this._loadDropdown();
                },
                eventChangeEmployer: function (ev) {
                    ev.preventDefault();
                    const employer = _.find(this.ptEmployers, {
                        id: this.form.employer_id
                    });
                    if (_.isUndefined(employer)) return;
                    this.form.company_id = employer.meta.company_id;
                    this.loading = true;
                    const currentPost = new wp.api.models.Emploi({
                        id: this.emploi.id
                    });
                    currentPost.fetch({
                            data: {
                                context: 'edit'
                            }
                        })
                        .then((employe) => {
                            currentPost.set('employer', employer.id);
                            currentPost.save().done(() => {
                                this.loading = false;
                            });
                        });

                },
                eventSearchEmployer: function (ev) {
                    const query = ev.target.value;
                    if (this.inSearch || _.isEmpty(query)) return;
                    const user = new wp.api.collections.Users();
                    this.inSearch = true;
                    user.fetch({
                            data: {
                                context: 'edit',
                                roles: ['employer'],
                                search: query
                            }
                        })
                        .then(U => {
                            this.ptEmployers = _.clone(U);
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
                            });
                        });

                }
            }
        };
        // Wordpress api is loaded!
        wp.api.loadPromise.done(() => {
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