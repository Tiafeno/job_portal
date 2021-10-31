const jobAXIOSInstance = axios.create({
    baseURL: clientApiSettings.root + 'job/v2',
    headers: {'X-WP-Nonce': clientApiSettings.nonce}
});
const fileFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
const getRandomPassword = (length = 8) => {
    const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    const string_length = length;
    let randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
    }
    return randomstring;
};
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
(function ($) {
    $().ready(function () {
        Vue.component('v-select', VueSelect.VueSelect);
        Vue.filter('jobStatus', function (value) {
            if (!value) return 'Inconnue'
            value = value.toString()
            return value === 'pending' ? 'En attente de validation' : (value === 'private' ? 'Supprimer' : 'Publiée');
        });
        Vue.filter('cvStatus', function (user) {
            if (!user) return 'Inconnue';
            const isPublic = user.is_active; // boolean
            const hasCV = user.meta.has_cv; // boolean
            if (!hasCV) return "Indisponible";
            return isPublic ? "Publier" : "En attent de validation";

        });
        // Return random password
        const jobHTTPInstance = axios.create({
            baseURL: clientApiSettings.root + 'job/v2',
            headers: {'X-WP-Nonce': clientApiSettings.nonce}
        });
        const getRandomId = () => {
            const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            const string_length = 8;
            let randomstring = '';
            for (var i = 0; i < string_length; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                randomstring += chars.substring(rnum, rnum + 1);
            }
            return randomstring;
        };
        const _componentUploadAvatar = {
            props: ['userid', 'wpapi', 'title'],
            template: "#upload-avatar-template",
            data: function () {
                return {
                    wpUploadUrl: null,
                    btnTitle: 'Ajouter',
                    loading: false,
                    defaultPreviewLogo: '//semantic-ui.com/images/wireframe/square-image.png',
                    logoReadUrl: null,
                    avatarFile: null,
                }
            },
            computed: {
                previewUrl: function () {
                    return lodash.isNull(this.logoReadUrl) ? this.defaultPreviewLogo : this.logoReadUrl;
                }
            },
            methods: {
                previewFiles: function (event) {
                    const files = event.target.files;
                    this.avatarFile = files.item(0);
                    getFileReader(this.avatarFile).then(draw => {
                        this.logoReadUrl = draw.src;
                        this.upload();
                    });
                },
                eventClickHandler: function (event) {
                    event.preventDefault();
                    $('input#upload-avatar').trigger('click');
                },
                upload: async function () {
                    this.loading = true;
                    await this.wpapi.media()
                        // Specify a path to the file you want to upload, or a Buffer
                        .file(this.avatarFile)
                        .create({
                            title: "",
                            alt_text: "",
                            description: this.userid,
                        }).then(uploadMedia => {
                            this.__putUserAvatar(uploadMedia);
                        });
                },
                __putUserAvatar: function(media) {
                    // Your media is now uploaded: let's associate it with a post
                    this.wpapi.users().id(this.userid).update({avatar: media.id})
                        .then(resp => {
                            this.loading = false;
                            alertify.notify("Photo de profil mis a jour avec succes", 'success');
                        });
                }
            },
            created: function () {
                this.btnTitle = this.title;
                // build url
                const ABS = '/';
                const wpUserModel = new wp.api.models.User({id: this.userid});
                wpUserModel.fetch().done(u => {
                    const avatar = u.avatar;
                    if (lodash.isEmpty(avatar)) return;
                    this.defaultPreviewLogo = avatar.upload_dir.baseurl + ABS + avatar.image.file;
                });
            },
            delimiters: ['${', '}'],
        };
        const _componentPricing = {
            props: ['item'],
            template: "#pricing_account",
            data: function () {
                return {
                    loading: false,
                }
            },
            methods: {
                goToPurchase: function (ev, _id) {
                    ev.preventDefault();
                    const uId = clientApiSettings.current_user_id;
                    this.loading = true;
                    jobHTTPInstance.post(`pay/account/${_id}/${uId}`, {}).then((resp) => {
                        if (resp.status === 200) {
                            const response = resp.data;
                            if (response.success) {
                                console.log(response)
                            }
                        }
                        this.loading = false;
                    });
                    return;
                }
            }
        };
        const _componentCVStatus = {
            template: "#cv-status-template",
            props: ['client'],
            data: function() {
                return {
                    optStatus: [],
                    loading: false,
                    status: 0,
                }
            },
            methods: {
                // Update candidate status
                onUpdate: function(value) {
                    let form = new FormData();
                    form.append('uid', this.client.id);
                    form.append('val', value);
                    this.loading = true;
                    // Send request for update status
                    jobAXIOSInstance.post('/cv-status', form, function(resp) {
                       this.loading = false;
                    });
                }
            },
            created: function() {
                this.loading = true;
                jobAXIOSInstance.get('/cv-status').then(resp => {
                    this.optStatus = resp.data;
                    this.status = this.client.cv_status;
                    this.loading = false;
                });
            }
        };
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
                this.Wordpress.jobs = this.Wordpress.registerRoute('wp/v2', '/emploi/(?P<id>\\d+)', {
                    // Listing any of these parameters will assign the built-in
                    // chaining method that handles the parameter:
                    params: ['context', 'per_page', 'offset', 'param', 'status']
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
                            this.isCandidate = lodash.indexOf(self.Client.roles, 'candidate') >= 0;
                            this.isEmployer = lodash.indexOf(self.Client.roles, 'employer') >= 0;
                            self.Loading = true;
                        });
                }
            }
        };
        const EditPassword = {
            template: '#edit-password-template',
            data: function () {
                return {
                    loading: false,
                    validators: [],
                    pwd: '',
                    pwd_conf: '',
                }
            },
            methods: {
                errorHandler: function (item) {
                    this.validators.push(item);
                },
                submitNewPassword: function (ev) {
                    ev.preventDefault();
                    this.validators = [];
                    var self = this;
                    if (lodash.isEmpty(this.pwd) || lodash.isEmpty(this.pwd_conf)) {
                        this.errorHandler("Veuillez remplire correctement les champs requis");
                    }
                    if (this.pwd !== this.pwd_conf) {
                        this.errorHandler("Les deux (2) mot de passe ne sont pas identique");
                    }
                    if (!lodash.isEmpty(this.validators)) {
                        return;
                    }
                    var form = new FormData();
                    form.append('action', 'change_my_pwd');
                    form.append('pwd', this.pwd);
                    form.append('pwd_nonce', clientApiSettings.nonce_form);
                    this.loading = true;
                    axios.post(clientApiSettings.ajax_url, form).then(function (resp) {
                        var response = resp.data;
                        if (response.success) {
                            alertify.alert('information', response.data, function () {
                                window.location.reload();
                            });
                        }
                    }).catch(function (err) {
                    }).done(function () {
                        self.loading = false;
                    })

                }
            }
        };
        /**
         * Cette composant permet de modifier le profil
         *
         * @type {{
         * template: string, data: (
         *  function(): {
         *      currentUser: null,
         *      validators: [],
         *      isCandidate: boolean,
         *      isEmployer: boolean,
         *      currentUserCompany: null
         * }),
         *  methods: {
         *      init: (function(): Promise<void>),
         *      profilHandler: ProfilEdit.methods.profilHandler,
         *      submitProfil: ProfilEdit.methods.submitProfil},
         *      mounted: ProfilEdit.mounted }
         *  }
         */
        const ProfilEdit = {
            template: "#profil-client-template",
            data: function () {
                return {
                    loading: false,
                    validators: [],
                    isCandidate: false,
                    isEmployer: false,
                    user: null,
                    userCompany: null,
                }
            },
            created: function () {
                this.init();
            },
            computed: {
                hisRole: function() {
                    return this.isCandidate ? 'Candidat' : 'Employeur';
                }
            },
            methods: {
                submitProfil: function (ev) {
                    ev.preventDefault();
                },
                init: async function () {
                    wp.api.loadPromise.done(() => {
                        this.loading = true;
                        const cUser = new wp.api.models.User({id: clientApiSettings.current_user_id});
                        cUser.fetch({data: {context: 'edit'}}).done(user => {
                            this.user = lodash.clone(user);
                            this.isCandidate = lodash.indexOf(user.roles, 'candidate') >= 0;
                            this.isEmployer = lodash.indexOf(user.roles, 'employer') >= 0;
                            // If employer or company
                            if (this.isEmployer) {
                                const companyId = parseInt(user.meta.company_id, 10);
                                if (0 === companyId) return;
                                const companyModel = new wp.api.models.User({id: companyId});
                                companyModel.fetch({data: {context: 'edit'}}).done(companyResponse => {
                                    this.userCompany = lodash.clone(companyResponse);
                                    this.loading = false;
                                });
                            }

                        });
                    });
                },
            }
        };
        const Home = {
            template: '#dashboard',
            components: {
                'comp-edit-pwd': EditPassword,
                'comp-edit-profil': ProfilEdit
            },
            data: function () {
                return {
                    loading: false,
                }
            },
            methods: {}
        };
        const CVComponents = {
            experience: {
                props: ['year_range', 'item'],
                template: '#experience-template',
            },
            education: {
                props: ['year_range', 'item'],
                template: '#education-template',
            }
        };
        const CVComp = {
            template: '#client-cv',
            components: {
                'comp-education': CVComponents.education,
                'comp-experience': CVComponents.experience,
                'comp-cv-status': _componentCVStatus,
                'upload-avatar': _componentUploadAvatar
            },
            beforeRouteLeave(to, from, next) {
                const answer = window.confirm('Do you really want to leave? you have unsaved changes!')
                if (answer) {
                    next()
                } else {
                    next(false)
                }
            },
            data: function () {
                return {
                    hasCV: false,
                    publicCV: false,
                    errors: [],
                    first_name: '',
                    last_name: '',
                    phone: '',
                    address: "",
                    city: '',
                    region: 0,
                    gender: "",
                    birthday: "",
                    profil: "", // Biographie
                    languages: [],
                    categories: [],

                    optLanguages: [],
                    optCategories: [],
                    optRegions: [],

                    currentUser: null,
                    Loading: true,
                    yearRange: [],
                    // Si la valeur est different de null, c'est qu'il a selectioner une liste a modifier
                    // Ne pas oublier de reinisialiser la valeur apres mise a jour
                    // Default value: null
                    eduValidator: [],
                    formEduSelected: null,
                    formEduEdit: {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        desc: '',
                        b: '',
                        /** begin year */
                        e: '' /** end year */
                    },
                    expValidator: [],
                    formExpSelected: null,
                    formExpEdit: {
                        _id: getRandomId(),
                        office: '',
                        enterprise: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '',
                        /** end year */
                        desc: '',
                    },
                    WPApiModel: null,
                    Emploi: null

                }
            },
            created: function () {
                let currentDate = new Date();
                this.yearRange = lodash.range(1950, currentDate.getFullYear());
            },
            mounted: async function () {
                this.Loading = true;
                await this.$parent.Wordpress.users().me().context('edit').then((response) => {
                    this.currentUser = lodash.cloneDeep(response);
                    //Populate data value
                    this.first_name = this.currentUser.first_name;
                    this.last_name = this.currentUser.last_name;
                    this.phone = this.currentUser.meta.phone;
                    this.address = this.currentUser.meta.address;
                    this.gender = this.currentUser.meta.gender;
                    this.city = this.currentUser.meta.city;
                    this.birthday = this.currentUser.meta.birthday;
                    this.profil = this.currentUser.meta.profil;
                    this.region = this.currentUser.meta.region;
                    let languages = this.currentUser.meta.languages;
                    languages = lodash.isEmpty(languages) ? [] : JSON.parse(languages);
                    this.languages = lodash.clone(languages);
                    let categories = this.currentUser.meta.categories;
                    categories = lodash.isEmpty(categories) ? [] : JSON.parse(categories);
                    this.categories = lodash.clone(categories);
                    this.hasCV = !!this.currentUser.meta.has_cv;
                    this.publicCV = !!this.currentUser.is_active;
                    this.Loading = false;
                });
                // Education sortable list
                new Sortable(document.getElementById('education-list'), {
                    handle: '.edu-history', // handle's class
                    animation: 150,
                    // Element dragging ended
                    onEnd: function ( /**Event*/ evt) {
                        var itemEl = evt.item; // dragged HTMLElement
                        evt.to; // target list
                        evt.from; // previous list
                        evt.oldIndex; // element's old index within old parent
                        evt.newIndex; // element's new index within new parent
                        evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
                        evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements
                        evt.clone // the clone element
                        evt.pullMode; // when item is in another sortable: `"clone"` if cloning, `true` if moving
                        console.log(evt);
                    },
                });
                // Recuperer les langues
                fetch(clientApiSettings.root + 'wp/v2/language?per_page=50').then(res => {
                    res.json().then(json => (this.optLanguages = json));
                });
                // Recuperer les categories
                fetch(clientApiSettings.root + 'wp/v2/categories?per_page=50').then(res => {
                    res.json().then(json => (this.optCategories = json));
                });
                // Recuperer les items de region
                fetch(clientApiSettings.root + 'wp/v2/region?per_page=50').then(res => {
                    res.json().then(json => (this.optRegions = json));
                });
            },
            computed: {
                getExperiences() {
                    let experiences = this.getMeta('experiences');
                    let response = lodash.isEmpty(experiences) ? [] : JSON.parse(experiences);
                    return response;
                },
                getEducations() {
                    let educations = this.getMeta('educations');
                    let response = lodash.isEmpty(educations) ? [] : JSON.parse(educations);
                    return response;
                },
            },
            methods: {
                errorHandler: function (field) {
                    return `Le champ <b>"${field}"</b> est obligatoire`;
                },
                getMeta: function (value) {
                    let metaValue = lodash.isNull(this.currentUser) ? JSON.stringify([]) :
                        (typeof this.currentUser.meta == 'undefined' ? JSON.stringify([]) : this.currentUser.meta[value]);
                    return metaValue;
                },
                updateExperiences: function (data) {
                    const self = this;
                    this.Loading = true;
                    this.$parent.Wordpress.users().me().update({
                        meta: {
                            experiences: JSON.stringify(data)
                        }
                    }).then(function (response) {
                        self.currentUser = lodash.clone(response);
                        /** reset experience form value to default */
                        self.resetExperience();
                        self.Loading = false;
                        $('.modal').modal('hide');
                    }).catch(function (err) {
                        self.Loading = false;
                    });
                },
                updateEducations: function (data) {
                    const self = this;
                    this.Loading = true;
                    this.$parent.Wordpress.users().me().update({
                        meta: {
                            educations: JSON.stringify(data)
                        }
                    }).then(function (response) {
                        self.currentUser = lodash.clone(response);
                        /** reset experience form value to default */
                        self.resetEducation();
                        self.Loading = false;
                        $('.modal').modal('hide');
                    }).catch(function (err) {
                        self.Loading = false;
                    });
                },
                resetExperience: function () {
                    this.formExpEdit = {
                        _id: getRandomId(),
                        office: '',
                        enterprise: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '',
                        /** end year */
                        desc: '',
                    };
                    this.formExpSelected = null;
                },
                resetEducation: function () {
                    this.formEduEdit = {
                        _id: getRandomId(),
                        establishment: '',
                        diploma: '',
                        city: '',
                        country: '',
                        b: '',
                        /** begin year */
                        e: '' /** end year */
                    };
                    this.formEduSelected = null;
                },
                /** Envt click button modal */
                addExperience: function () {
                    this.resetExperience();
                    $('#experience').modal('show');
                },
                addEducation: function () {
                    this.resetEducation();
                    $('#education').modal('show');
                },
                editExperience: function (evt, id) {
                    evt.preventDefault();
                    const self = this;
                    const experiences = this.getExperiences;
                    let expSelected = lodash.find(experiences, exp => exp._id === id);
                    Object.keys(expSelected).forEach((item, index) => {
                        self.formExpEdit[item] = expSelected[item];
                    });
                    this.formExpSelected = id;
                    $('#experience').modal('show');
                },
                deleteExperience: function (evt, id) {
                    evt.preventDefault();
                    const experiences = this.getMeta('experiences');
                    let currentExperiences = lodash.remove(experiences, exp => {
                        return exp._id === id;
                    });
                    this.updateExperiences(currentExperiences);
                },
                deleteEducation: function (evt, id) {
                    evt.preventDefault();
                    const educations = this.getMeta('educations');
                    let currentEducations = lodash.remove(educations, edu => {
                        return edu._id === id;
                    });
                    this.updateEducations(currentEducations);
                },
                editEducation: function (evt, id) {
                    evt.preventDefault();
                    const self = this;
                    const educations = this.getEducations;
                    let eduSelected = lodash.find(educations, {
                        _id: id
                    });
                    Object.keys(eduSelected).forEach((item, index) => {
                        self.formEduEdit[item] = eduSelected[item];
                    });
                    this.formEduSelected = id;
                    $('#education').modal('show');
                },
                validateExpForm: function (ev) {
                    ev.preventDefault();
                    this.expValidator = [];
                    const form = this.formExpEdit;
                    if (lodash.isEmpty(form.office)) {
                        this.expValidator.push(this.errorHandler('Poste'));
                    }
                    if (lodash.isEmpty(form.enterprise)) {
                        this.expValidator.push(this.errorHandler('Entreprise'));
                    }
                    if (lodash.isEmpty(form.city)) {
                        this.expValidator.push(this.errorHandler('Ville'));
                    }
                    if (lodash.isEmpty(form.country)) {
                        this.expValidator.push(this.errorHandler('Pays'));
                    }
                    if (!form.b) {
                        this.expValidator.push(this.errorHandler('Année de début'))
                    }
                    if (!lodash.isEmpty(this.expValidator)) {
                        return;
                    }
                    this.submitExpForm();
                },
                validateEduForm: function (ev) {
                    ev.preventDefault();
                    this.eduValidator = [];
                    const form = this.formEduEdit;
                    if (lodash.isEmpty(form.city)) {
                        this.eduValidator.push(this.errorHandler('Ville'));
                    }
                    if (lodash.isEmpty(form.country)) {
                        this.eduValidator.push(this.errorHandler('Pays'))
                    }
                    if (lodash.isEmpty(form.diploma)) {
                        this.eduValidator.push(this.errorHandler('Diplôme'))
                    }
                    if (lodash.isEmpty(form.establishment)) {
                        this.eduValidator.push(this.errorHandler('Etablissement'))
                    }
                    if (!form.b) {
                        this.eduValidator.push(this.errorHandler('Année de début'))
                    }
                    if (!lodash.isEmpty(this.eduValidator)) {
                        return;
                    }
                    this.submitEduForm();
                },
                submitExpForm: function () {
                    const self = this;
                    let experiences = this.getExperiences;
                    if (this.formExpSelected === null) {
                        experiences.push(this.formExpEdit);
                    } else {
                        /** update exist experience */
                        experiences = lodash.map(experiences, exp => {
                            if (exp._id === self.formExpSelected) {
                                Object.keys(exp).forEach((expKey) => {
                                    exp[expKey] = self.formExpEdit[expKey];
                                });
                            }
                            return exp;
                        });
                    }
                    this.updateExperiences(experiences);
                },
                submitEduForm: function () {
                    const self = this;
                    let educations = this.getEducations;
                    if (this.formEduSelected === null) {
                        educations.push(this.formEduEdit);
                    } else {
                        /** update exist experience */
                        educations = lodash.map(educations, edu => {
                            if (edu._id === self.formEduSelected) {
                                Object.keys(edu).forEach((key) => {
                                    edu[key] = self.formEduEdit[key];
                                });
                            }
                            return edu;
                        });
                    }
                    this.updateEducations(educations);
                },
                submitCV: function (ev) {
                    ev.preventDefault();
                    const self = this;
                    let experiences = this.getMeta('experiences');
                    let educations = this.getMeta('educations');
                    this.errors = [];
                    if (lodash.isEmpty(this.languages)) {
                        this.errors.push(this.errorHandler('Langue'));
                    }
                    if (lodash.isEmpty(this.categories)) {
                        this.errors.push(this.errorHandler('Emploi recherché ou métier'));
                    }
                    if (lodash.isEmpty(this.gender)) {
                        this.errors.push(this.errorHandler('Genre'));
                    }
                    if (lodash.isEmpty(this.address)) {
                        this.errors.push(this.errorHandler('Adresse'));
                    }
                    if (!this.region || this.region === 0 || this.region == '0') {
                        this.errors.push(this.errorHandler('Region'));
                    }
                    if (lodash.isEmpty(this.city)) {
                        this.errors.push(this.errorHandler('Ville'));
                    }
                    // Verifier s'il y a une experience et education au minimum
                    let msgExperienceEmpty = "Ajoutez au moins une experience dans votre CV";
                    if (lodash.isEmpty(experiences)) {
                        this.errors.push(msgExperienceEmpty);
                    } else {
                        experiences = JSON.parse(experiences);
                        if (lodash.isEmpty(experiences)) {
                            this.errors.push(msgExperienceEmpty);
                        }
                    }
                    let msgEducationEmpty = "Ajoutez au moins un parcour à votre CV";
                    if (lodash.isEmpty(educations)) {
                        this.errors.push(msgEducationEmpty);
                    } else {
                        educations = JSON.parse(educations);
                        if (lodash.isEmpty(educations)) {
                            this.errors.push(msgEducationEmpty);
                        }
                    }

                    if (!lodash.isEmpty(this.errors)) {
                        return false;
                    }
                    this.Loading = true;
                    let _languages = JSON.stringify(this.languages);
                    let _categories = JSON.stringify(this.categories);
                    let userId = parseInt(clientApiSettings.current_user_id);
                    this.$parent.Wordpress.users().me()
                        .update({
                            last_name: this.last_name,
                            first_name: this.first_name,
                            is_active:  this.publicCV,
                            meta: {
                                phone: this.phone,
                                address: this.address,
                                gender: this.gender,
                                region: this.region,
                                city: this.city,
                                languages: _languages,
                                categories: _categories,
                                birthday: this.birthday,
                                reference: `CV${userId}`,
                                profil: this.profil,
                                // Render visible this CV
                                has_cv: true,
                            }
                        })
                        .then(function (resp) {
                            alertify.notify('Enregistrer avec succès', 'success', 5, function () {
                                self.Loading = false;
                                self.hasCV = true;
                            });
                        })
                        .catch(function (er) {
                            self.Loading = false;
                        });
                }
            }
        };
        const CompanyComp = {
            template: '#create-company',
            components: {
                'upload-avatar': _componentUploadAvatar
            },
            data: function () {
                return {
                    loading: false,
                    sectionClass: 'utf_create_company_area padd-bot-80',
                    wpapi: new WPAPI({
                        endpoint: clientApiSettings.root,
                        nonce: clientApiSettings.nonce
                    }),
                    account_id: 0,
                    company_account: {},
                    isUpdate: false,
                    categories: [],
                    countries: [],
                    errors: [],
                    formData: {
                        name: '',
                        logo: '',
                        category: '',
                        // email: '',
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
                initComponent: function () {
                    wp.api.loadPromise.done(async () => {
                        this.loading = true;
                        this.account_id = clientApiSettings.current_user_id;
                        const wpCatsModel = new wp.api.collections.Categories();
                        const wpCountryModel = new wp.api.collections.Country();
                        const categories = await wpCatsModel.fetch({data: { per_page: 50}});
                        const countries = await wpCountryModel.fetch({data: { per_page: 50}});
                        axios.all([categories, countries]).then(axios.spread(
                            (...wpapiAll) => {
                                this.categories = lodash.clone(wpapiAll[0]);
                                this.countries = lodash.clone(wpapiAll[1]);
                            }
                        )).catch(errors => {
                        });
                        this.wpapi.users().me().context('edit').then((response) => {
                            const me = lodash.cloneDeep(response);
                            const hasCompany = me.meta.company_id !== 0;
                            if (hasCompany) {
                                // S'il possede deja une entreprise
                                const wpCompanyModel = new wp.api.models.User({id: me.meta.company_id});
                                wpCompanyModel.fetch({data: {context: 'edit'}}).done(company => {
                                    this.isUpdate = true;
                                    this.company_account = lodash.clone(company);
                                    // Ajouter les valeurs dans le formulaires
                                    this.formData = {
                                        name: company.username,
                                        category: company.meta.category,
                                        // email: company.email,
                                        address: company.meta.address,
                                        nif: company.meta.nif,
                                        stat: company.meta.stat,
                                        phone: company.meta.phone,
                                        country: company.meta.country,
                                        city: company.meta.city,
                                        zipcode: company.meta.zipcode,
                                        website: company.meta.website,
                                        employees: company.meta.employees,
                                        description: company.description
                                    }
                                    this.loading = false;
                                });
                            } else {
                                this.loading = false;
                            }
                        }).catch((err) => {
                            this.loading = false;
                        });
                    });

                },
                checkForm: function (e) {
                    e.preventDefault();
                    this.errors = [];
                    const data = this.formData;
                    var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
                    if (lodash.isEmpty(data.name)) {
                        this.errors.push('Le titre est requis');
                    }
                    if (data.category === "" || data.category === " ") {
                        this.errors.push('Champ categorie est requis');
                    }
                    // if (lodash.isEmpty(data.email) || !data.email.match(validRegex)) {
                    //     this.errors.push('Le champ email est requis ou verifier que c\'est une adresse email valide');
                    // }
                    if (lodash.isEmpty(data.nif)) {
                        this.errors.push('Champ "NIF" est requis');
                    }
                    if (lodash.isEmpty(data.stat)) {
                        this.errors.push('Champ "Numéro statistique" est requis');
                    }
                    if (lodash.isEmpty(data.address)) {
                        this.errors.push('Votre adresse est requis');
                    }
                    if (data.country === "" || data.country === " ") {
                        this.errors.push('Champ pays est requis');
                    }
                    if (lodash.isEmpty(data.city)) {
                        this.errors.push('Champ ville est requis');
                    }
                    if (lodash.isEmpty(data.description)) {
                        this.errors.push('Champ à propos est requis');
                    }
                    if (lodash.isEmpty(this.errors)) {
                        this.updateCompany(data);
                    }
                },
                updateCompany: async function (item) {
                    const randomMail = getRandomPassword(10);
                    const _email = `${randomMail}@jobjiaby.com`;
                    const _name = item.name;
                    // Upload avatar
                    this.loading = true;
                    let request = null;
                    if (this.isUpdate) {
                        request = this.wpapi.users().id(this.company_account.id).update({
                            description: item.description,
                            meta: {
                                country: item.country,
                                category: item.category,
                                city: item.city,
                                address: item.address,
                                phone: item.phone,
                                nif: item.nif,
                                stat: item.stat,
                                website: item.website,
                                zipcode: item.zipcode,
                                employees: item.employees,
                                newsletter: 0, // bool value to subscribe or not
                                employer_id: clientApiSettings.current_user_id,
                            }
                        });
                    } else {
                        request = this.wpapi.users().create({
                            name: _name,
                            nickname: _email,
                            username: _name,
                            password: getRandomPassword(),
                            email: _email,
                            first_name: "",
                            last_name: "",
                            roles: ['company'],
                            description: item.description,
                            meta: {
                                country: item.country,
                                category: item.category,
                                city: item.city,
                                address: item.address,
                                phone: item.phone,
                                nif: item.nif,
                                stat: item.stat,
                                website: item.website,
                                zipcode: item.zipcode,
                                employees: item.employees,
                                newsletter: 0, // bool value to subscribe or not
                                employer_id: clientApiSettings.current_user_id,
                            }
                        });
                    }
                    // Callback
                    request.then(user => {
                        // Add this company for the employee
                        this.wpapi.users().me().update({
                            meta: {company_id: user.id}
                        }).then(() => {
                            alertify.notify("Donnee mis a jour avec succes", 'success');
                            this.loading = false;
                        });
                    }).catch(err => {
                        this.loading = false;
                        this.errorHandler(err);
                    });
                },
                errorHandler: function (response) {
                    alertify.alert(response.code, response.message);
                },
                formatHTML: function (str) {
                    return str.replace(/(<([^>]+)>)/ig, "");
                }
            },
            created: function () {
                this.initComponent();
            },
            mounted: function () {
                $('select').dropdown({
                    clearable: true,
                    placeholder: ''
                });
            },
        };
        const AnnonceComp = {
            template: "#client-annonce",
            data: function () {
                return {
                    loading: false,
                    annonces: []
                }
            },
            created: function () {
                this.Populate();
            },
            methods: {
                trashAnnonce: function (ev, jobId) {
                    ev.preventDefault();
                    alertify.confirm("Voulez vous vraiment supprimer cette annonce. ID: " + jobId, () => {
                        this.loading = true;
                        this.$parent.Wordpress.jobs().id(jobId).update({
                            status: 'private'
                        }).then(() => {
                            this.Populate();
                        });
                    }, () => {
                    });
                },
                Populate: function () {
                    this.loading = true;
                    this.$parent.Wordpress.jobs()
                        .status(['pending', 'publish', 'private'])
                        .param('meta_key', 'employer_id')
                        .param('meta_value', clientApiSettings.current_user_id)
                        .per_page(10)
                        .then((response) => {
                            this.annonces = lodash.map(response, annonce => {
                                let title = annonce.title.rendered;
                                annonce.title.rendered = lodash.truncate(title, {
                                    'length': 35,
                                    'separator': '[...]'
                                });
                                return annonce;
                            });
                            this.loading = false;
                        });
                }
            }
        };
        const AnnonceDetails = {
            template: "#annonce-apply",
            data: function () {
                return {
                    loading: false,
                    job: null,
                    candidateApply: [],
                    jHTTPInstance: null,
                }
            },
            mounted: function () {
                this.jHTTPInstance = axios.create({
                    baseURL: clientApiSettings.root + 'job/v2',
                    headers: {'X-WP-Nonce': clientApiSettings.nonce}
                });
                this.loading = true;
                const job_id = this.$route.params.id;
                this.jHTTPInstance.get(`${job_id}/apply`).then(response => {
                    const details = response.data;
                    if (details.success) {
                        this.candidateApply = lodash.map(details.data.candidates, candidate => {
                            candidate.link = clientApiSettings.page_candidate + '#/candidate/' + candidate.id;
                            return candidate;
                        });
                        this.job = lodash.clone(details.data.job);
                    }
                    this.loading = false;
                }).catch(function () {
                    this.loading = false;
                });
            },
            methods: {
                purchased: function (candidateId) {
                    let _form = new FormData();
                    _form.append('candidate_id', candidateId);
                    this.jHTTPInstance.post(`${this.job.id}/purchase`, _form).then(resp => {
                        console.log(resp);
                    })

                }
            },
            computed: {}
        };
        const AdApplied = {
            template: "#ad-applied",
            data: function () {
                return {
                    jobs: [],
                    loading: false,
                }
            },
            mounted: function () {
                this.initComponent();
            },
            methods: {
                initComponent: function () {
                    this.loading = true;
                    const clientId = clientApiSettings.current_user_id;
                    axios.get(clientApiSettings.ajax_url, {
                        params: {
                            cid: clientId,
                            action: 'ad_handler_apply'
                        }
                    }).then((resp) => {
                        if (resp.status === 200) {
                            let jobs = lodash.clone(resp.data);
                            jobs = lodash.map(jobs, job => {
                                return lodash.isNull(job.id) ? null : job;
                            });
                            this.jobs = lodash.compact(jobs);
                        }
                        this.loading = false;
                    });
                }
            }
        };
        const PricingLayout = {
            template: "<div><router-view></router-view></div>",
        };
        const PricingTable = {
            template: "#pricing-table",
            components: {
                'comp-pricing': _componentPricing
            },
            data: function () {
                return {
                    loading: false,
                    products: []
                };
            },
            created: function () {
                this.loading = true;
                jobHTTPInstance.get('pricing').then((resp) => {
                    if (resp.status === 200) {
                        this.products = lodash.clone(resp.data);
                    }
                    this.loading = false;
                });

                /**
                 * Effectuer un paiement direct
                 */
                // const jWCHTTPInstance = axios.create({
                //     baseURL: clientApiSettings.root + 'wc/v2',
                //     headers: {'X-WP-Nonce': clientApiSettings.nonce}
                // });
                // jWCHTTPInstance.get(clientApiSettings.root + 'wc/v2/pricing/176/11').then((resp) => {
                //     console.log(resp);
                // });
            }
        };
        const PricingPurchase = {};
        const routes = [{
            path: '/',
            component: Layout,
            redirect: '/home',
            children: [
                {
                    path: 'home',
                    name: 'Home',
                    props: true,
                    component: Home
                },
                {
                    path: 'cv',
                    name: 'CV',
                    component: CVComp,
                },
                {
                    path: 'jobs',
                    name: 'Annonce',
                    component: AnnonceComp,
                },
                {
                    path: 'company',
                    name: 'Company',
                    component: CompanyComp,
                },
                {
                    path: 'job/:id/details',
                    name: 'AnnonceDetails',
                    component: AnnonceDetails
                },
                {
                    path: 'ad_applied',
                    name: 'AdApplied',
                    component: AdApplied
                },
                {
                    path: 'pricing',
                    name: 'Pricing',
                    component: PricingLayout,
                    redirect: '/pricing/items',
                    children: [
                        {
                            path: 'items',
                            name: 'PricingTable',
                            component: PricingTable
                        },
                        {
                            path: ':id/purchase',
                            name: 'PricingPurchase',
                            component: PricingPurchase,
                        }
                    ]
                },
            ],
            beforeEnter: (to, from, next) => {
                let isAuth = parseInt(clientApiSettings.current_user_id) !== 0;
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
                    if (parseInt(clientApiSettings.current_user_id) !== 0) next({
                        name: 'Home'
                    })
                    else next();
                },
            }
        ];
        const router = new VueRouter({
            routes // short for `routes: routes`
        });
        new Vue({
            el: '#client',
            router
        });
    });
})(jQuery);