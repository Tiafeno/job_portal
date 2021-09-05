jQuery(function ($) {
    $().ready(() => {
        const __compCandidate = {
            template: "#comp-candidate-template",
            props: ['u'],
            data: function() {
                return {
                    candidate: null,
                }
            },
            computed: {
                Experiences: function() {
                    const experiences = this.u.experiences;
                    return experiences ? experiences : [];
                },
                Educations: function() {
                    const educations = this.u.educations;
                    return educations ? educations : [];
                }
            },
            created: function() {
                this.candidate = _.clone(this.u);
                this._buildForm();
            },
            methods: {
                _buildForm: function() {
                    // Create form with builder javascript library
                }
            }
        };
        const __compEmployer = {
            template: "#comp-employer-template"
        };
        // Wordpress api is loaded!
        wp.api.loadPromise.done(() => {
            new Vue({
                el: '#user_app',
                components: {
                    'comp-candidate': __compCandidate,
                    'comp-employer': __compEmployer
                },
                data: function () {
                    return {
                        loading: false,
                        userRole: null,
                        user: null
                    }
                },
                created: function () {
                    this.init();
                },
                methods: {
                    init: function () {
                        this.loading = true;
                        const user = new wp.api.models.User({id: WPAPIUserSettings.uId});
                        user.fetch({data: {context: 'edit'}}).then(resp => {
                            this.user = resp;
                            this.userRole = _.first(resp.roles);
                            this.loading = false;
                        });
                    }
                }
            });
        });
    });
});