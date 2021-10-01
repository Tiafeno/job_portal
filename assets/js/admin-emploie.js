jQuery(function ($) {
    $().ready(() => {
        const __compAdminEmploiEditor = {
            delimiters: ['${', '}'],
            template: "#comp-editor-template",
            props: ['u'],
            data: function() {
                return {
                    loading: false,
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
            created: function() {
                this.emploi = _.clone(this.u);
                this._buildForm();
            },
            mounted: function() {
                $('.ui.dropdown')
                    .dropdown({
                        clearable: true,
                        placeholder: ''
                    });
            },
            methods: {
                _buildForm: function() {
                    this.form.address = this.emploi.meta.address;
                    this.form.experience = this.emploi.meta.experience;
                    const allEmployer = new wp.api.collections.Users();
                    this.loading = true;
                    allEmployer.fetch({data: {context: 'edit', roles: ['employer'], per_page: 100}})
                        .then(resp => {
                            this.ptEmployers = _.clone(resp);
                            this.form.employer_id = this.emploi.employer;
                            this.loading = false;
                        });
                },
                eventChangeEmployer: function(ev) {
                    ev.preventDefault();
                    const employer = _.find(this.ptEmployers, {id: this.form.employer_id});
                    if (_.isUndefined(employer)) return;
                    this.form.company_id = employer.meta.company_id;
                    this.loading = true;
                    const currentPost = new wp.api.models.Emploi({id: this.emploi.id});
                    currentPost.fetch({data : {context: 'edit'}})
                        .then((employe) => {
                            currentPost.set('employer', employer.id);
                            currentPost.save().done(() => {
                                this.loading = false;
                            });
                        });

                },
                submitForm: function(ev) {
                    ev.preventDefault();
                    this.loading = true;
                    const currentPost = new wp.api.models.Emploi({id: this.emploi.id});
                    currentPost.fetch({data : {context: 'edit'}})
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
                        const post = new wp.api.models.Emploi({id: WPAPIEmploiSettings.postId});
                        post.fetch({data: {context: 'edit'}}).then(resp => {
                            this.post = resp;
                            this.loading = false;
                        });
                    },
                }
            });
        });
    });
});