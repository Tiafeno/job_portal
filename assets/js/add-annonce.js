(function ($) {
    $().ready(() => {

        // Ajouter une entreprise
        Vue.component('create-company', {
            template: '#create-company',
            data: function () {
                return {
                    heading: "Ajouter une entreprise",
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                    wordpress_api: new WPAPI({
                        endpoint: window.wpApiSettings.root,
                        nonce: window.wpApiSettings.nonce
                    }),
                }
            },
            methods: {
                newSection: function (ev) {
                    this.$emit('changed', ev);
                }
            },
            created: function() {},
            mounted: function () {
                $('select').niceSelect();

                this.wordpress_api.users().me().context('edit').then(resp => {
                    console.log(resp);
                });
            },
            props: ['st'],
            delimiters: ['${', '}']

        });

        // Ajouter une annonce
        Vue.component('create-annonce', {
            template: '#create-annonce',
            data: function () {
                return {
                    heading: "Ajouter une annonce",
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                }
            },
            created: function () {

            },
            props: ['st'],
            delimiters: ['${', '}']
        });

        new Vue({
            el: '#add-annonce',
            data: {
                stateView: 'create-company'
            },
            methods: {
                changeTemplate: function($event) {
                    this.stateView = $event;
                    console.log(this.stateView);
                }
            },
            delimiters: ['${', '}']
        });

    });
})(jQuery)

