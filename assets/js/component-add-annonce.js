(function ($) {
    $().ready(() => {
        // Return random password
        const getRandomPassword = () => {
            const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            const string_length = 8;
            let randomstring = '';
            for (var i = 0; i < string_length; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                randomstring += chars.substring(rnum, rnum + 1);
            }
            return randomstring;
        };
        const fileFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
        /**
         * Cette fonction permet de redimensionner une image
         *
         * @param imgObj - the image element
         * @param newWidth - the new width
         * @param newHeight - the new height
         * @param startX - the x point we start taking pixels
         * @param startY - the y point we start taking pixels
         * @param ratio - the ratio
         * @returns {string}
         */
        const drawImage = (imgObj, newWidth, newHeight, startX, startY, ratio) => {
            //set up canvas for thumbnail
            const tnCanvas = document.createElement('canvas');
            const tnCanvasContext = tnCanvas.getContext('2d');
            tnCanvas.width = newWidth;
            tnCanvas.height = newHeight;

            /* use the sourceCanvas to duplicate the entire image. This step was crucial for iOS4 and under devices. Follow the link at the end of this post to see what happens when you don’t do this */
            const bufferCanvas = document.createElement('canvas');
            const bufferContext = bufferCanvas.getContext('2d');
            bufferCanvas.width = imgObj.width;
            bufferCanvas.height = imgObj.height;
            bufferContext.drawImage(imgObj, 0, 0);

            /* now we use the drawImage method to take the pixels from our bufferCanvas and draw them into our thumbnail canvas */
            tnCanvasContext.drawImage(bufferCanvas, startX, startY, newWidth * ratio, newHeight * ratio, 0, 0, newWidth, newHeight);
            return tnCanvas.toDataURL();
        };

        /**
         * Récuperer les valeurs dispensable pour une image pré-upload
         * @param {File} file
         * @returns {Promise<any>}
         */
        const getFileReader = (file) => {
            return new Promise((resolve, reject) => {
                const byteLimite = 2097152; // 2Mb
                if (file && file.size <= byteLimite) {
                    let fileReader = new FileReader();
                    fileReader.onload = (Event) => {
                        const img = new Image();
                        img.src = Event.target.result;
                        img.onload = () => {
                            const imgCrop = drawImage(img, img.width, img.height, 0, 0, 1);
                            resolve({
                                src: imgCrop
                            });
                        };
                    };
                    fileReader.readAsDataURL(file);
                } else {
                    reject('Le fichier sélectionné est trop volumineux. La taille maximale est 2Mo.');
                }
            });
        };
        // Ajouter une entreprise
        const CreateCompany = {
            template: '#create-company',
            data: function () {
                return {
                    loading: false,
                    heading: "Ajouter une entreprise",
                    sectionClass: 'utf_create_company_area padd-top-80 padd-bot-80',
                    wordpress_api: new WPAPI({
                        endpoint: window.job_handler_api.root,
                        nonce: window.job_handler_api.nonce
                    }),
                    company_logo: '//semantic-ui.com/images/wireframe/square-image.png',
                    errors: [],
                    formData: {
                        name: '',
                        logo: '',
                        category: '',
                        email: '',
                        address: '',
                        nif: '',
                        stat: '',
                        phone: '',
                        country: '',
                        city: '',
                        zipcode: '',
                        website: '',
                        employees: 0,
                        description: ''
                    }
                }
            },
            methods: {
                checkForm: function (e) {
                    e.preventDefault();
                    this.errors = [];
                    const data = this.formData;
                    var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
                    if (lodash.isEmpty(data.name)) {
                        this.errors.push('Le titre est requis');
                    }
                    if (lodash.isEmpty(data.category)) {
                        this.errors.push('Champ categorie est requis');
                    }
                    if (lodash.isEmpty(data.email) || !data.email.match(validRegex)) {
                        this.errors.push('Le champ email est requis ou verifier que c\'est une adresse email valide');
                    }
                    if (lodash.isEmpty(data.nif)) {
                        this.errors.push('Champ "NIF" est requis');
                    }
                    if (lodash.isEmpty(data.stat)) {
                        this.errors.push('Champ "Numéro statistique" est requis');
                    }
                    if (lodash.isEmpty(data.address)) {
                        this.errors.push('Votre adresse est requis');
                    }
                    if (lodash.isEmpty(data.country)) {
                        this.errors.push('Champ pays est requis');
                    }
                    if (lodash.isEmpty(data.city)) {
                        this.errors.push('Champ ville est requis');
                    }
                    if (lodash.isEmpty(data.description)) {
                        this.errors.push('Champ à propos est requis');
                    }

                    if (lodash.isEmpty(this.errors)) {
                        this.addCompany(data);
                    }
                },
                previewFiles: function (event) {
                    const files = event.target.files;
                    this.formData.logo = files.item(0);
                    getFileReader(files.item(0)).then(draw => {
                        this.company_logo = draw.src;
                    });
                },
                uploadLogoHandler: function (event) {
                    event.preventDefault();
                    $('input#company-logo').trigger('click');
                },
                addCompany: async function (item) {
                    const self = this;
                    const _email = item.email;
                    const _name = item.name;
                    let fileId = '';
                    // Upload avatar
                    this.loading = true;
                    if (typeof item.logo === 'object' && item.logo !== '') {
                        await this.wordpress_api.media()
                            // Specify a path to the file you want to upload, or a Buffer
                            .file(item.logo)
                            .create({
                                title: `logo ${item.name}`,
                                alt_text: item.name,
                                description: item.description
                            }).then(response => {
                                // Your media is now uploaded: let's associate it with a post
                                fileId = response.id;
                            });
                    }
                    this.wordpress_api.users().create({
                        name: _name,
                        nickname: _email,
                        username: _name,
                        password: getRandomPassword(),
                        email: _email,
                        first_name: "",
                        last_name: "",
                        roles: ['company'],
                        description: item.description,
                        avatar: fileId,
                        meta: {
                            country: item.country,
                            city: item.city,
                            address: item.address,
                            nif: item.nif,
                            stat: item.stat,
                            newsletter: 0, // bool value to subscribe or not
                        }
                    }).then(function (user) {
                        // Add this company for the employee
                        self.wordpress_api.users().me().update({
                            meta: {company_id: user.id}
                        }).then(() => {
                            self.loading = false;
                            // Company add successfuly
                            self.$router.push({name: 'Annonce'});
                        });
                    }).catch(err => {
                        this.loading = false;
                        this.errorHandler(err);
                    });
                },
                errorHandler: function (response) {
                    let title = '';
                    switch (response.code) {
                        case 'existing_user_email':
                            title = 'Erreur';
                            break;
                        default:
                            title = 'Information';
                            break
                    }
                    alertify.alert(title, response.message, function () {
                    });
                },
                formatHTML: function (str) {
                    return str.replace(/(<([^>]+)>)/ig, "");
                }
            },
            created: function () {
                const self = this;
                this.loading = true;
                this.wordpress_api.users().me().context('view').then((response) => {
                    const me = lodash.cloneDeep(response);
                    const hasCompany = me.meta.company_id !== 0;
                    if (hasCompany) {
                        self.$router.push({name: 'Annonce'});
                    }
                    self.loading = false;
                }).catch(function (err) {
                    self.loading = false;
                    self.errorHandler(err);
                });

            },
            mounted: function () {
                $('select').dropdown({
                    clearable: true,
                    placeholder: ''
                })
            },
            delimiters: ['${', '}']

        };
        // Ajouter une annonce
        const CreateAnnonce = {
            template: '#create-annonce',
            data: function () {
                return {
                    me: {},
                    heading: "Ajouter une annonce",
                    sectionClass: 'utf_create_company_area padd-bot-80',
                    loading: false,
                    errors: [],
                    inputs: {
                        title: '',
                        salary_range: '',
                        address: '',
                        category: '', // Secteur d'activite
                        region: 0, // Taxonomy
                        experience: 0,
                        type: '', //CDI, CDD etc..
                        description: ''
                    },
                }
            },
            mounted: function () {
                $('select').dropdown({
                    clearable: true,
                    placeholder: 'Selectionnez une option'
                });
                this.inputs.description = new MediumEditor('#advert-description', {
                    toolbar: {
                        /* These are the default options for the toolbar,
                           if nothing is passed this is what is used */
                        allowMultiParagraphSelection: true,
                        buttons: ['bold', 'italic', 'underline', 'strikethrough', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'orderedlist', 'unorderedlist', 'outdent', 'indent', 'h2', 'h3'],

                        firstButtonClass: 'medium-editor-button-first',
                        lastButtonClass: 'medium-editor-button-last',
                        standardizeSelectionStart: false,
                        static: false,
                        /* options which only apply when static is true */
                        align: 'center',
                        sticky: true,
                        updateOnEmptySelection: false,
                        paste: {
                            cleanAttrs: ['class', 'style', 'dir'],
                            cleanTags: ['meta'],
                            cleanPastedHTML: true,
                            forcePlainText: false
                        }
                    }
                });
            },
            created: function () {
                this.WPAPI = new WPAPI({
                    endpoint: job_handler_api.root,
                    nonce: job_handler_api.nonce
                });
                this.WPAPI.jobs = this.WPAPI.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                    // Listing any of these parameters will assign the built-in
                    // chaining method that handles the parameter:
                    params: ['context']
                });
                // Si le client est connecter, On verifie s'il existe deja une entreprise
                this.loading = true;
                this.WPAPI.users().me().context('edit')
                    .then( resp => {
                        this.me = lodash.clone(resp);
                        this.loading = false;
                        // Verifier le role du client
                        let roles = this.me.roles;
                        if (lodash.indexOf(roles, 'employer') < 0) {
                            this.$router.push({name: "DenialAccess"});
                            return;
                        }
                        // Verifier si l'utilisateur possede deja une entreprise ou societe
                        var hasCompany = this.me.meta.company_id !== 0;
                        if (!hasCompany) this.$router.push({name: 'Company'});
                    });
            },
            methods: {
                errorHandler: function (inputName) {
                    var err = `Le champ <b>"${inputName}"</b> est obligatoire`;
                    this.errors.push(err);
                },
                checkAddForm: function (ev) {
                    ev.preventDefault();
                    this.errors = [];
                    var description = this.inputs.description.getContent();
                    if (lodash.isEmpty(this.inputs.title)) {
                        this.errorHandler('Poste à pourvoir');
                    }
                    if (lodash.isEmpty(description)) {
                        this.errorHandler('Description');
                    }
                    if (this.inputs.region === 0) {
                        this.errorHandler('Region');
                    }
                    if (this.inputs.experience === 0) {
                        this.errorHandler('Experience');
                    }
                    if (lodash.isEmpty(this.inputs.address)) {
                        this.errorHandler('Adresse');
                    }
                    if (!lodash.isEmpty(this.errors)) {
                        return;
                    }
                    this.submitForm();
                },
                submitForm: function () {
                    const self = this;
                    this.loading = true;
                    let _category = [],
                        _region = [],
                        _salaries = [],
                        _jobtype = [];

                    if (this.inputs.category) _category.push(parseInt(this.inputs.category));
                    if (this.inputs.salary_range) _salaries.push(parseInt(this.inputs.salary_range));
                    if (this.inputs.type) _jobtype.push(parseInt(this.inputs.type));
                    if (this.inputs.region) _region.push(parseInt(this.inputs.region));
                    this.WPAPI.jobs().create({
                        title: this.inputs.title,
                        content: this.inputs.description.getContent(),
                        categories: _category, // taxonomy
                        region: _region, // taxonomy
                        salaries: _salaries, // taxonomy
                        job_type: _jobtype, // taxonomy - type de travail
                        meta: {
                            experience: parseInt(this.inputs.experience),
                            address: this.inputs.address,
                            employer_id: self.me.id,
                            company_id: parseInt(self.me.meta.company_id)
                        },
                        status: 'pending',
                    }).then(function (resp) {
                        self.loading = false;
                        alertify.alert('Information', "Votre annonce a bien été publier avec succès", function () {
                            window.location.href = job_handler_api.account_url;
                        });
                    }).catch(function (err) {
                        self.loading = false;
                        alertify.alert('Erreur', err.message, function () {
                        });
                    });
                }
            },
            delimiters: ['${', '}']
        };
        const DenialAccess = {
            template: "#denial-access", // Include in theme.liquid
        }

        // Application
        const Layout = {
            template: '<div>Chargement</div>',
            data: function () {
                return {}
            },
            created: function () {
                // Check if is client
                // var job_handler_api is global js variable in localize for add-annonce widget
                this.isClient = parseInt(job_handler_api.current_user_id) !== 0;
                if (typeof job_handler_api === 'undefined') return;
            },
            methods: {},
            delimiters: ['${', '}']
        };
        const routes = [
            {
                path: '/',
                component: Layout,
                redirect: '/create-annonce',
            },
            {
                path: '/create-company',
                component: CreateCompany,
                name: 'Company',
                beforeEnter: (to, from, next) => {
                    let isAuth = parseInt(job_handler_api.current_user_id) !== 0;
                    if (to.name != 'Login' && !isAuth) next({
                        name: 'Login'
                    });
                    else next();
                },
            },
            {
                path: '/create-annonce',
                component: CreateAnnonce,
                name: 'Annonce',
                beforeEnter: (to, from, next) => {
                    let isAuth = parseInt(job_handler_api.current_user_id) !== 0;
                    if (to.name != 'Login' && !isAuth) next({
                        name: 'Login'
                    });
                    else next();
                },
            },
            {
                path: '/login',
                name: 'Login',
                component: CompLogin,
                beforeEnter: (to, from, next) => {
                    if (job_handler_api.isLogged) next({name: 'Annonce'})
                    else next();
                },
            },
            {
                path: '/denial-access',
                component: DenialAccess,
                name: 'DenialAccess',
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });
        new Vue({
            el: '#add-annonce',
            router
        });

    });
})(jQuery);

