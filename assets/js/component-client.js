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
                        .then(function (response) {
                            self.Client = lodash.clone(response);
                            self.Loading = true;
                        });
                }
            }
        };
        const Home = {
            template: '<p>rdsjgfkdhsfjghdsjkf hlg dsflgl djg</p>'
        };
        const CV = {
            template: '<p>CV Content</p>'
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
                    if (to.name != 'Login' && parseInt(clientApiSettings.current_user_id) == 0 ) next({ name: 'Login' })
                    else next();
                },
            },
            {
                path: '/login',
                name: 'Login',
                component: CompLogin
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });

       
        // Application
        new Vue({el: '#client', router});

    });
})(jQuery);