(function ($) {
    $().ready(() => {

        // Ajouter une entreprise
        Vue.component('create-company', {
            template: '#create-company',
            data: function () {
                return {
                    heading: "Ajouter une entreprise",
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                }
            },
            methods: {

            },
            created: function() {},
            mounted: function () {
                $('select').niceSelect();
            },
            props: ['st'],
            delimiters: ['${', '}']

        });

        // Ajouter une annonce
        Vue.component('create-annonce', {
            template: '#create-annonce',
            data: function () {
                return {
                    heading: "Ajouter une annonce"
                }
            },
            created: function () {

            },
            props: ['stateView'],
            delimiters: ['${', '}']
        });

        new Vue({
            el: '#add-annonce',
            data: {
                stateView: 'create-company'
            },
            delimiters: ['${', '}']
        });

    });
})(jQuery)

