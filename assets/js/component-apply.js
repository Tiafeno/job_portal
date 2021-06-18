(function ($){
    $().ready(function() {
        const Layout = {
            template: '<div class="side-list no-border"><router-view></router-view></div>',
        }
        const SinglePage = {
            template: '<router-link :to="{ path: \'/apply\'}" class="btn-job theme-btn job-apply">Apply now</router-link>',
        };
        const ApplyJob = {
            template: '#apply-job',
            data: function() {
                return {
                    loading: false,
                    isLogged: false,
                    Client: null,
                    wpapiAxiosInstance: null,
                    jobapiAxiosInstance: null,
                }
            },
            mounted: function() {
                const self = this;
                this.wpapiAxiosInstance = axios.create({
                    baseURL: apiSettings.root + 'wp/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                this.jobapiAxiosInstance = axios.create({
                    baseURL: apiSettings.root + 'job/v2',
                    headers: {
                        'X-WP-Nonce': apiSettings.nonce
                    }
                });
                this.isLogged = !!apiSettings.isLogged;
                if (!this.isLogged) {
                    // Call login modal
                    renderLoginModel();
                    $('#signin').modal('show');
                } else {
                    this.loading = true;
                    const userResponse = this.wpapiAxiosInstance.get(`users/${apiSettings.userId}`);
                    userResponse.then(function(resp) {
                        console.log(resp);
                        self.Client = lodash.clone(resp.data);
                        self.jobapiAxiosInstance.post(`apply/${apiSettings.jobId}`, {}).then(function(response) {
                            console.log(response);
                            self.loading = false;
                        }).catch(function() {
                            self.loading = false;
                        })
                    });
                }
            },
            methods: {
                sendApply: function() {

                }
            }
        };
        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/single',
                children: [
                    {
                        path: 'single',
                        name: 'Single',
                        component: SinglePage
                    },
                    {
                        path: 'apply',
                        name:'Apply',
                        component: ApplyJob,
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
        })
    });
})(jQuery);