(function ($){
    $().ready(function() {
        const jobapiAxiosInstance = axios.create({
            baseURL: apiSettings.root + 'job/v2',
            headers: {
                'X-WP-Nonce': apiSettings.nonce
            }
        });
        const AppLayout = {
            template: '<div class="side-list no-border"><router-view></router-view></div>',
        }
        const routes = [
            {
                path: '/',
                component: AppLayout,
                redirect: '/item',
                children: [
                    {
                        path: 'item',
                        name: 'ItemApply',
                        component: {
                            template: '<router-link :to="{ path: \'/apply\'}" class="btn btn-job theme-btn btn-outlined job-apply">Je postule</router-link>',
                        }
                    },
                    {
                        path: 'apply',
                        name:'Apply',
                        component: {
                            template: '#apply-job',
                            data: function() {
                                return {
                                    loading: false,
                                    isLogged: false,
                                    message: null,
                                }
                            },
                            mounted: function() {
                                const self = this;
                                this.isLogged = !!apiSettings.isLogged;
                                if (!this.isLogged) {
                                    // Call login modal
                                    showLoginModal();
                                } else {
                                    this.loading = true;
                                    jobapiAxiosInstance.post(`/${apiSettings.jobId}/apply`, {}).then(function(response) {
                                        const dataResponse = lodash.clone(response.data);
                                        self.message = dataResponse;
                                        self.loading = false;
                                    }).catch(function() {
                                        self.loading = false;
                                    })
                                }
                            }
                        },
                        beforeEnter: (to, from, next) => {
                            if (apiSettings.isLogged) {
                                next();
                            }
                            else {
                                if (!apiSettings.isLogged) {
                                    showLoginModal();
                                }
                                next({name: 'ItemApply'});
                            }
                        },
                    }
                ],
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });
        new Vue({
            el: "#apply-app",
            router
        });
    });
})(jQuery);