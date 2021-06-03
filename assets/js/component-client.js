(function ($) {
    $().ready(function () {

        const Layout = {
            template: '#client-layout',
            data: function () {
                return {
                    Loading: false,
                    isLogged: false,
                    isCandidate: false,
                    isEmployer: false,
                    Client: null,
                    Wordpress: null,
                }
            },
            created: function () {
                if (typeof clientApiSettings === 'undefined') return;
                this.Wordpress = new WPAPI({
                    endpoint: clientApiSettings.root,
                    nonce: clientApiSettings.nonce
                });
                this.init();
            },
            methods: {
                init: async function () {
                    const self = this;
                    if (parseInt(clientApiSettings.current_user_id) == 0 || !clientApiSettings.current_user_id) {
                        this.isLogged = false
                        return false;
                    }
                    this.isLogged = true;
                    await this.Wordpress.users()
                        .context('edit')
                        .me()
                        .then(response => {
                            self.Client = lodash.clone(response);
                            // Check if is Candidate or Employer
                            if (lodash.indexOf(self.Client.roles, 'candidate') >= 0) {
                                this.isCandidate = true;
                            }
                            if (lodash.indexOf(self.Client.roles, 'employer') >= 0) {
                                this.isEmployer = true;
                            }
                            self.Loading = true;
                        });
                }
            }
        };
        const Home = {
            template: '<p>rdsjgfkdhsfjghdsjkf hlg dsflgl djg</p>'
        };

        const CV = {
            template: '#client-cv',
            data: function () {
                return {
                    hasCV: false,
                    currentUser: null,
                    Loading: false,

                }
            },
            created: function () {
                const parent = this.$parent;
                this.currentUser = lodash.cloneDeep(parent.Client);
            },
            mounted: function() {
                // Education sortable list
                new Sortable(document.getElementById('education-list'), {
                    handle: '.edu-history', // handle's class
                    animation: 150,
                    // Element dragging ended
                    onEnd: function (/**Event*/evt) {
                        var itemEl = evt.item;  // dragged HTMLElement
                        evt.to;    // target list
                        evt.from;  // previous list
                        evt.oldIndex;  // element's old index within old parent
                        evt.newIndex;  // element's new index within new parent
                        evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
                        evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements
                        evt.clone // the clone element
                        evt.pullMode;  // when item is in another sortable: `"clone"` if cloning, `true` if moving
                        console.log(evt);
                    },
                });
            },
            methods: {
                getMeta: function (id) {
                    return this.currentUser.meta[id];
                },
                updateExperiences: function (data) {
                    let experiences = [{_:1, pos:1, title:'Creation site web', desc:'Lorem upsum dolor sit amet', s: '10/02/2010', e: ''}];
                    this.$parent.Wordpress.users().me().update({
                        meta: {
                            experiences: JSON.stringify(experiences)
                        }
                    })
                },
                getExperiences: function () {
                    return JSON.parse(this.getMeta('experiences'));
                }
            }
        };

        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/home',
                children: [
                    {
                        path: 'home',
                        name: 'Home',
                        component: Home
                    },
                    {
                        path: 'cv',
                        name: 'CV',
                        component: CV
                    },
                ],
                beforeEnter: (to, from, next) => {
                    if (to.name != 'Login' && parseInt(clientApiSettings.current_user_id) == 0) next({name: 'Login'})
                    else next();
                },
            },
            {
                path: '/login',
                name: 'Login',
                component: CompLogin,
                beforeEnter: (to, from, next) => {
                    if (parseInt(clientApiSettings.current_user_id) !== 0) next({name: 'Home'})
                    else next();
                },
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });


        // Application
        new Vue({el: '#client', router});

    });
})(jQuery);