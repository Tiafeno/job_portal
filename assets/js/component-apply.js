(function ($){
    $().ready(function() {
        const jobapiAxiosInstance = axios.create({
            baseURL: apiSettings.root + 'job/v2',
            headers: {
                'X-WP-Nonce': apiSettings.nonce
            }
        });
        const wpapiAxiosInstance = axios.create({
            baseURL: apiSettings.root + 'wp/v2',
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
                            template: '<router-link :to="{ path: \'/apply\'}"  class="btn-job theme-btn job-apply">Apply now</router-link>',
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
                                    renderLoginModel();
                                    $('#signin').modal('show');
                                } else {
                                    this.loading = true;
                                    jobapiAxiosInstance.post(`apply/${apiSettings.jobId}`, {}).then(function(response) {
                                        const dataResponse = lodash.clone(response.data);
                                        self.message = dataResponse;
                                        self.loading = false;
                                    }).catch(function() {
                                        self.loading = false;
                                    })
                                }
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