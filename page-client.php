<?php
/*
 * Template Name: Client Page
 * description: Page pour les clients, candidat ou employer
 */

wp_enqueue_script(
    'comp-client',
    get_stylesheet_directory_uri() . '/assets/js/component-client.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'sortable', 'comp-login', 'vue-select'],
    null,
    true
);
wp_localize_script('comp-client', 'clientApiSettings', [
    'root' => esc_url_raw(rest_url()),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wp_rest'),
    'nonce_form' => wp_create_nonce('ajax-client-form'),
    'current_user_id' => intval(get_current_user_id()),
    'page_candidate' => home_url('/candidate/')
]);

get_header();
?>
    <style type="text/css">
        .vs--searchable .vs__dropdown-toggle {
            height: 50px;
        }

        .vs__dropdown-toggle {
            border: 2px solid #dde6ef;
            -webkit-box-shadow: 0 1px 1px rgb(7 177 7 / 8%);
            box-shadow: 0 1px 1px rgb(7 177 7 / 8%);
        }

        .vs__selected {
            display: flex;
            align-items: center;
            background-color: #d1eddf;
            border: none;
            border-radius: 4px;
            color: #334e6f;
            line-height: 1.4;
            margin: 4px 2px 0px;
            padding: 0 1em;
            z-index: 0;
        }

        .dashboard_nav_item ul li a.router-link-exact-active.router-link-active {
            background: #26ae61;
            border: 2px solid #26ae61;
            box-shadow: 0 3px 3px rgb(0 0 0 / 10%);
            color: white;
            border-radius: 4px;
        }

        .dashboard_nav_item ul li a.router-link-exact-active.router-link-active i {
            background: rgba(50, 215, 121, 0.5);
            color: #fff;
        }

        .error-list {
            margin-top: 10px !important;
            padding-left: 20px;
            line-height: 15px;
            font-size: 11px;
        }

    </style>
    <script type="text/x-template" id="client-layout">
        <!-- ================ Profile Settings ======================= -->
        <section class="padd-top-80 padd-bot-80">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="dashboard_nav_item">
                            <ul>
                                <li>
                                    <router-link :to="{ path: '/' }"><i class="login-icon ti-dashboard"></i>
                                        Tableau de bord
                                    </router-link>
                                </li>
                                <li v-if="isCandidate">
                                    <router-link :to="{ path: '/cv' }"><i class="login-icon ti-dashboard"></i>
                                        Mon CV
                                    </router-link>
                                </li>
                                <li v-if="isCandidate">
                                    <router-link :to="{ path: '/ad_applied' }"><i class="login-icon ti-dashboard"></i>
                                        Offre postuler
                                    </router-link>
                                </li>
                                <li v-if="isEmployer">
                                    <router-link :to="{ path: '/jobs' }"><i class="login-icon ti-dashboard"></i>
                                        Mes Annonces
                                    </router-link>
                                </li>
                                <li v-if="isEmployer && false">
                                    <router-link :to="{ path: '/pricing' }"><i class="login-icon ti-dashboard"></i>
                                        Pricing
                                    </router-link>
                                </li>
                                <li v-if="isEmployer">
                                    <router-link :to="{ path: '/company' }"><i class="login-icon ti-dashboard"></i>
                                        Entreprise
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <router-view></router-view>
                    </div>
                </div>
            </div>
        </section>
        <!-- ================ End Profile Settings ======================= -->
    </script>

    <script type="text/x-template" id="pricing_account">
        <div class="card">
            <div class="content">
                <div class="header">
                    {{item.title}}
                </div>
                <div class="meta">
                    {{item.regular_price}}
                </div>
                <div class="description">
                   {{item.desc}}
                </div>
            </div>
            <div class="extra content">
                <div class="ui two buttons">
                    <div @click="goToPurchase($event, item._id)" class="ui basic green button">COMMANDER</div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/x-template" id="pricing-layout">
        <router-view></router-view>
    </script>
    <script type="text/x-template" id="pricing-table">
        <div class="ui cards">
            <comp-pricing v-for="product in products" :key="product._id" :item="product"></comp-pricing>
        </div>
    </script>

    <!--Edit password-->
    <script type="text/x-template" id="edit-password-template">
        <div class="widget-boxed">
            <div class="widget-boxed-header">
                <h4>Changer de mot de passe</h4>
            </div>
            <div class="widget-boxed-body">
                <form @submit="submitNewPassword" method="post" action="" novalidate>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" v-model="pwd" name="pwd" class="form-control"
                               placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmation
                        </label>
                        <input type="password" v-model="pwd_conf" name="pwd_conf" class="form-control"
                               placeholder="" required>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn theme-btn full-width btn-m">Modifier</button>
                    </div>
                    <div v-if="validators.length" style="margin-top: 40px; padding-left: 20px;" class="error-list">
                        <b>Please correct the following error(s):</b>
                        <ul>
                            <li style="color:red" v-for="validator in validators">{{validator}}</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>

    </script>
    <!--Create company-->
    <script id="create-company" type="text/x-template">
        <div v-bind:class="[sectionClass]">
            <div class="lds-roller" v-if="loading">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="" v-if="!loading">
                <form class="c-form" @submit="checkForm" method="post" action="" novalidate>
                    <!-- General Information -->
                    <div class=""><h4>General Information</h4></div>
                    <div class="row">
                        <div class="col-md-8 col-sm-8 col-xs-12 mrg-bot-30">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Nom de l'entreprise ou société <span style="color: red">*</span></label>
                                <input type="text" v-model="formData.name" :disabled="isUpdate" name="name"
                                       class="form-control"
                                       placeholder="Nom de l'entreprise ou société" required>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Categorie de l'entreprise ou société <span style="color: red">*</span></label>
                                <select v-model="formData.category" name="category" class="form-control ui dropdown"
                                        required>
                                    <option value="">Selectionner une catégorie</option>
                                    <option :value="cat.id" :key="cat.id" v-for="cat in categories">{{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>NIF <span style="color: red">*</span></label>
                                <input type="text" v-model="formData.nif" name="nif" class="form-control" placeholder=""
                                       required>
                                <span class="sub-description">Cette donnée sera uniquement utilisée à des fins d’analyse.</span>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Numéro statistique <span style="color: red">*</span></label>
                                <input type="text" v-model="formData.stat" name="stat" class="form-control"
                                       placeholder="" required>
                                <span class="sub-description">Cette donnée sera uniquement utilisée à des fins d’analyse.</span>
                            </div>
                        </div>

                        <upload-avatar :userid="company_account.id"
                                       :title="'Ajouter un logo'"
                                       :wpapi="wpapi"></upload-avatar>

                        <div class="clearfix"></div>

                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label>Email du responsable de l'entreprise <span style="color: red">*</span></label>
                            <input type="email" v-model="formData.email" :disabled="isUpdate" name="email"
                                   class="form-control" placeholder="Adresse email" required>
                            <span class="sub-description" style="color:red">Ne pas utiliser l'adresse email de votre compte actuel.</span>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label>Adresse physique de l'entreprise <span style="color: red">*</span></label>
                            <input type="text" v-model="formData.address" name="address" class="form-control"
                                   placeholder="Adresse physique de l'entreprise" required>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label>Numéro de téléphone</label>
                            <input type="text" v-model="formData.phone" name="phone" class="form-control"
                                   placeholder="+2613XX XXX XX">
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4 col-sm-6 col-xs-12 m-clear">
                            <label>Pays <span style="color: red">*</span></label>
                            <select v-model="formData.country" name="country" class="form-control ui dropdown" required>
                                <option value="">Selectionner un pays</option>
                                <option :value="country.id" :key="country.id" v-for="country in countries">{{
                                    country.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label>Ville <span style="color: red">*</span></label>
                            <input type="text" v-model="formData.city" name="city" class="form-control"
                                   placeholder="Ex: Antananarivo" required>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12 m-clear">
                            <label>Code postal <span style="color: red">*</span></label>
                            <input type="text" v-model="formData.zipcode" name="zipcode" class="form-control" required
                                   placeholder="Ex: 101">
                        </div>

                    </div>

                    <!-- Company Summery -->
                    <div class="box">
                        <div class="box-header">
                            <h4>A propos</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label>Employées</label>
                                    <select v-model="formData.employees" name="employees"
                                            class="ui dropdown form-control">
                                        <option value="1-5">1-5</option>
                                        <option value="5-10">5-10</option>
                                        <option value="10-50">10-50</option>
                                        <option value="100-500">100-500</option>
                                        <option value="500-1000">500-1000</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label>Site web de l'entreprise ou société</label>
                                    <input type="text" v-model="formData.website" name="website" class="form-control"
                                           placeholder="Ex: entreprise.com">
                                </div>
                                <div class="col-sm-12">
                                    <label>Description courte à propos de l'entreprise ou société <span
                                                style="color: red">*</span></label>
                                    <textarea v-model="formData.description" name="description"
                                              class="form-control height-120 textarea"
                                              placeholder="Votre description ici."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <button type="submit" :disabled="loading" class="btn light-gray-btn">Enregistrer mon
                                    entreprise
                                </button>
                            </div>
                        </div>

                    </div>

                    <div v-if="errors.length" style="margin-top: 40px" class="error-list">
                        <b>Please correct the following error(s):</b>
                        <ul>
                            <li style="color:#ff0000" v-for="error in errors">{{ error }}</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </script>
    <script type="text/x-template" id="profil-client-template">
        <div class="mrg-top-20" v-if="user !== null">
            <div class="emp-des mrg-bot-20">
                <h3>{{user.name}}</h3>
                <span class="theme-cl">{{ hisRole }}</span>
            </div>
            <div class="widget-boxed">
                <div class="widget-boxed-header">
                    <h4>Mes information</h4>
                </div>
                <div class="widget-boxed-body">
                    <div class="side-list no-border">
                        <ul>
                            <li v-if="user.meta.address != ''">
                                <i class="ti-credit-card padd-r-10"></i>{{user.meta.address}}
                            </li>
                            <li v-if="user.meta.city != ''">
                                <i class="ti-world padd-r-10"></i>{{user.meta.city}}
                            </li>
                            <li v-if="user.meta.phone != ''">
                                <i class="ti-mobile padd-r-10"></i>{{user.meta.phone}}
                            </li>
                            <li><i class="ti-email padd-r-10"></i>{{user.email}}</li>

                        </ul>
                    </div>
                </div>
            </div>

            <div class="widget-boxed" v-if="userCompany !== null">
                <div class="widget-boxed-header">
                    <h4>Information sur l'entreprise</h4>
                </div>
                <div class="widget-boxed-body">
                    <div class="side-list no-border">
                        <ul>
                            <li><i class="ti-credit-card padd-r-10"></i>
                                {{userCompany.meta.address}}
                                {{userCompany.meta.city}}
                            </li>
                            <li><i class="ti-mobile padd-r-10"></i>91 234 567 8765</li>
                            <li>NIF: {{userCompany.meta.nif}}</li>
                            <li>STAT: {{userCompany.meta.stat}}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <!--Tableau de bord template-->
    <script type="text/x-template" id="dashboard">
        <section class="utf_manage_jobs_area padd-top-0 mrg-top-0">

            <div class='row'>
                <div class="col-md-6">
                    <comp-edit-profil></comp-edit-profil>
                </div>
                <div class="col-md-4">
                    <comp-edit-pwd></comp-edit-pwd>
                </div>
            </div>
            <div class="row">

            </div>
        </section>
    </script>
    <!--Annonce handler template-->
    <script type="text/x-template" id="client-annonce">
        <!-- ======================== Manage Job ========================= -->
        <section class="utf_manage_jobs_area padd-top-0 mrg-top-0">
            <h2 class="bd-title">Mes annonces</h2>
            <p class="bd-lead">Tous vos annonces se trouvent ici</p>
            <div class="table-responsive">
                <div v-if="loading">Chargement en cours...</div>
                <div class="alert alert-secondary" role="alert" v-if="annonces.length <= 0 && !loading">Vous n'avez pas
                    d'annonce
                </div>
                <table class="table table-lg table-hover" v-if="annonces.length > 0 && !loading">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Designation</th>
                        <th>Date de publication</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="annonce in annonces" v-if="annonces.length > 0">
                        <td>{{ annonce.id }}</td>
                        <td><a :href="annonce.link" target="_blank"> {{ annonce.title.rendered }} </a></td>
                        <td><i class="ti-credit-card"></i> {{ annonce.date }}</td>
                        <td><span class="badge badge-info">{{ annonce.status | jobStatus }}</span></td>
                        <td>
                            <router-link class="mrg-5" :to="{ name: 'AnnonceDetails', params: {id: annonce.id} }"><i
                                        class="ti-view-list"></i></router-link>
                            <a v-if="annonce.status !== 'private'" class="cl-danger mrg-5" id="trash-annonce"
                               @click="trashAnnonce($event, annonce.id)">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- ====================== End Manage Company ================ -->
    </script>
    <!--Experience template-->
    <script type="text/x-template" id="experience-template">
        <div class="edu-history info"><i></i>
            <div class="detail-info" @click="$emit('edit', $event, item._id)">
                <h3>{{item.office}}</h3>
                <i>{{item.b}} - {{item.e ? item.e : "Jusqu'a aujourd'hui"}}</i>
                <p>{{item.desc}}</p>
            </div>
        </div>
    </script>
    <!--Education template-->
    <script type="text/x-template" id="education-template">
        <div class="edu-history info"><i></i>
            <div class="detail-info" @click="$emit('edit', $event, item._id)">
                <h3>{{item.establishment}}</h3>
                <i>{{item.b}} - {{item.e}}</i>
                <span>{{item.diploma}}</span>
                <p>{{item.desc}}</p>
            </div>
        </div>
    </script>
    <!--Client CV handler template-->
    <script type="text/x-template" id="client-cv">
        <div id="cv">
            <form class="cv-form" method="post" @submit="submitCV" novalidate>
                <div class="row">
                    <div class="col-md-12 col-sm-12" v-if="currentUser !== null">
                        <div class="emp-des mrg-bot-20">
                            <h3>CV{{currentUser.id}}</h3>
                            <span class="theme-cl">{{ currentUser | cvStatus }}</span>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="detail-wrapper">
                            <div class="row mrg-top-30">
                                <upload-avatar :userid="currentUser.id"
                                               :title="'Ajouter une photo'"
                                               :wpapi="$parent.Wordpress"
                                               v-if="currentUser !== null"></upload-avatar>
                                <div class="col-md-8">
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label>Genre</label>
                                            <select class=" wide form-control" v-model="gender" required>
                                                <option value="M.">M.</option>
                                                <option value="Mr">Mr</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label>Nom <span style="color: red">*</span></label>
                                            <input type="text" v-model="first_name" class="form-control" placeholder=""
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label>Prénom</label>
                                            <input type="text" v-model="last_name" class="form-control" placeholder=""
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label>Date de naissance <span style="color: red">*</span></label>
                                            <input type="date" class="form-control" placeholder="jj/mm/aaaa"
                                                   v-model="birthday" name="birthday">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12 mrg-top-15">
                                <div class="form-group">
                                    <label>Emploi recherché ou métier <span style="color: red">*</span></label>
                                    <v-select v-model="categories"
                                              multiple
                                              :selectable="() => categories.length < 2"
                                              :options="optCategories"
                                              :reduce="cat => cat.id"
                                              label="name">

                                    </v-select>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label>Langue maitrisée <span style="color: red">*</span></label>
                                    <v-select v-model="languages" multiple :options="optLanguages"
                                              :reduce="language => language.id" label="name"></v-select>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>Localisation</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label>Region</label>
                                        <select name="region" v-model="region" class="form-control">
                                            <option value="0">Selectionner une region</option>
                                            <option v-for="region in optRegions" :value="region.id">{{region.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label>Ville</label>
                                        <input type="text" v-model="city" class="form-control" placeholder="Ville"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label>Adresse</label>
                                        <input type="text" v-model="address" class="form-control"
                                               placeholder="Votre adresse" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <div class="form-group">
                                        <label>Numéro de téléphone</label>
                                        <input type="text" v-model="phone" class="form-control"
                                               placeholder="+261 32 XX XXX XX" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12" id="profil">
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>Mon personnalité (Qu'est-ce qui vous motive vraiment?)</h4>
                            </div>
                            <div class="detail-wrapper-body" id="education-list">
                                <textarea v-model="profil" class="form-control textarea" name="profil"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12" id="educations">
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>FORMATIONS / DIPLÔMES</h4>
                            </div>
                            <div class="detail-wrapper-body" id="education-list">
                                <comp-education v-for="education in getEducations" v-if="!Loading"
                                                @edit="editEducation"
                                                :key="education._id"
                                                :item="education"
                                                :year_range="yearRange">
                                </comp-education>
                            </div>
                            <div class="padd-l-15 padd-bot-15">
                                <button type="button" @click="addEducation()" class="btn-info btn btn-outlined">
                                    <i class="ti-plus"></i> Ajouter un parcours
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12" id="experiences">
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>EXPÉRIENCES PROFESSIONNELLES </h4>
                            </div>
                            <div class="detail-wrapper-body" id="experience-list">
                                <comp-experience v-for="experience in getExperiences" v-if="!Loading"
                                                 @edit="editExperience"
                                                 :key="experience._id"
                                                 :item="experience"
                                                 :year_range="yearRange">
                                </comp-experience>
                            </div>
                            <div class="padd-l-15 padd-bot-15">
                                <button type="button" @click="addExperience()" class="btn-info btn btn-outlined">
                                    <i class="ti-plus"></i> Ajouter une expérience
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary" :disabled="Loading">Enregistrer</button>
                        <div v-if="errors.length" style="margin-top: 40px" class="error-list">
                            <b>Please correct the following error(s):</b>
                            <ul>
                                <li style="color:#ff0000" v-for="error in errors" v-html="error"></li>
                            </ul>
                        </div>
                    </div>

                    <!-- experience modal Code -->
                    <div class="modal small ui" id="experience" >
                        <div class="header">
                            Experience
                        </div>
                        <div class="content" id="expModal">
                            <form @submit="validateExpForm" method="post" action="" novalidate>
                                        <div class="row">

                                            <div class="col-md-12">
                                                <label class="col-form-label">Poste <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="" autocomplete="off" name="office"
                                                           v-model="formExpEdit.office"
                                                           class="form-control" required="">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="col-form-label">Entreprise <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="" autocomplete="on"
                                                           v-model="formExpEdit.enterprise" name="enterprise"
                                                           class="form-control "
                                                           required="">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="col-form-label">Ville <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="Ex: Majunga" autocomplete="off"
                                                           v-model="formExpEdit.city" name="city"
                                                           class="form-control"
                                                           required="">
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <label class="col-form-label">Pays <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input placeholder="Ex: Madagascar"
                                                               v-model="formExpEdit.country" autocomplete="off"
                                                               name="country"
                                                               class="form-control"
                                                               required="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <div class="col-sm-12">
                                                        <p class="">De <span style="color: red">*</span></p>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <select name="b" v-model="formExpEdit.b"
                                                                    class="form-control" required="">
                                                                <option v-for="year in yearRange" :value="year">
                                                                    {{year}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <div class="col-sm-12">
                                                        <p class="text-uppercase">à </p>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <select class="form-control" v-model="formExpEdit.e"
                                                                    name="e">
                                                                <option :value="''">Poste actuel</option>
                                                                <option v-for="year in yearRange" :value="year">
                                                                    {{year}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="col-form-label ">Description courte </label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                <textarea placeholder="" cols="10" autocomplete="off"
                                                          v-model="formExpEdit.desc"
                                                          name="desc"
                                                          class="form-control"
                                                          required=""></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="flex-middle">
                                                    <button type="button"
                                                            @click="deleteExperience($event, formExpEdit._id)"
                                                            v-if="formExpSelected != null" class="btn btn-m ">Supprimer
                                                    </button>
                                                    <button type="submit" class="btn btn-m theme-btn ">Enregistrer
                                                    </button>
                                                </div>
                                                <div v-if="expValidator.length" style="margin-top: 40px"
                                                     class="error-list">
                                                    <b>Please correct the following error(s):</b>
                                                    <ul>
                                                        <li style="color:#ff0000" v-for="error in expValidator"
                                                            v-html="error"></li>
                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                        </div>
                    </div>
                    <!-- End experience modal -->
                    <!-- education modal Code -->
                    <div class="ui small modal" id="education">
                            <div class="header">Education</div>
                            <div class="content" id="eduModal">
                                <form @submit="validateEduForm" method="post" action="" novalidate>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="col-form-label ">Etablissement <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="Ex: Université de Majunga" autocomplete="off"
                                                           name="establishment"
                                                           v-model="formEduEdit.establishment"
                                                           class="form-control" required="">

                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="col-form-label ">Diplôme <span style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="Ex: Master II en Gestion d'entreprise"
                                                           autocomplete="on" name="diploma"
                                                           v-model="formEduEdit.diploma" class="form-control "
                                                           required="">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="col-form-label ">Ville <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <input placeholder="Ex: Majunga" autocomplete="off" name="city"
                                                           v-model="formEduEdit.city"
                                                           class="form-control"
                                                           required="">
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <label class="col-form-label ">Pays <span
                                                            style="color: red">*</span></label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input placeholder="Ex: Madagascar" autocomplete="off"
                                                               v-model="formEduEdit.country" name="country"
                                                               class="form-control"
                                                               required="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <div class="col-sm-12">
                                                        <p class="">Année de début <span style="color: red">*</span></p>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <select name="b" class="form-control"
                                                                    v-model="formEduEdit.b" required="">
                                                                <option :value="''">Année</option>
                                                                <option v-for="year in yearRange" :value="year">
                                                                    {{year}}
                                                                </option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <div class="col-sm-12">
                                                        <p>Année de fin</p>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <select class="form-control" v-model="formEduEdit.e"
                                                                    name="e">
                                                                <option :value="''">Année</option>
                                                                <option v-for="year in yearRange" :value="year">
                                                                    {{year}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="text-center">
                                                    <button type="button"
                                                            @click="deleteEducation($event, formEduEdit._id)"
                                                            v-if="formEduSelected != null" class="btn btn-m ">Supprimer
                                                    </button>
                                                    <button type="submit" class="btn btn-m theme-btn full-width">
                                                        Enregistrer
                                                    </button>
                                                </div>
                                                <div v-if="eduValidator.length" style="margin-top: 40px"
                                                     class="error-list">
                                                    <b>Please correct the following error(s):</b>
                                                    <ul>
                                                        <li style="color:#ff0000" v-for="error in eduValidator"
                                                            v-html="error"></li>
                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                            </div>
                    </div>
                    <!-- End education modal -->
                </div>
            </form>
        </div>
    </script>
    <!--Annonce apply handler template-->
    <script type="text/x-template" id="annonce-apply">
        <section class="utf_manage_jobs_area padd-top-0 mrg-top-0">
            <h4 class="mrg-bot-10" v-if="job != null">
                <router-link :to="{name: 'Annonce'}" class="padd-r-5"><i class="ti-arrow-circle-left"></i></router-link>
                {{job.title}}
            </h4>
            <div class="table-responsive">
                <table class="table table-lg table-hover">
                    <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Adresse</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="candidate in candidateApply" v-if="candidateApply.length !== 0 && !loading">
                        <td> {{ candidate.meta.reference }}</td>
                        <td><i class="ti-credit-card"></i> {{ candidate.meta.address }}</td>
                        <td>
                            <a class="cl-info mrg-5" :href="candidate.link" target="_blank"><i class="ti-info-alt"></i>
                                Voir le candidat
                            </a>
                            <button class="btn btn-info" @click="purchased(candidate.id)"> Purchase</button>
                        </td>
                    </tr>
                    <tr v-if="candidateApply.length === 0 && !loading">
                        <td>Aucune donnée disponible dans le tableau</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </script>

    <!--Annonce or offer applied handler template-->
    <script type="text/x-template" id="ad-applied">
        <section class="utf_manage_jobs_area padd-top-0 mrg-top-0">
            <h4 class="mrg-bot-10">Tous les offres que vous avez postuler</h4>
            <div class="table-responsive">
                <table class="table table-lg table-hover">
                    <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="job in jobs" v-if="jobs.length !== 0 && !loading">
                        <td> {{ job.title.rendered }}</td>
                        <td>
                            <a class="cl-info mrg-5" :href="job.link" target="_blank"><i class="ti-info-alt"></i>
                                Voir l'offre
                            </a>
                        </td>
                    </tr>
                    <tr v-if="jobs.length === 0 && !loading">
                        <td>Aucune donnée disponible dans le tableau</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </script>

    <div class="padd-top-80 padd-bot-80">
        <div class="container">
            <div id="client">
                <!--                <comp-login v-if="!isLogged && !Loading" @login-success="loggedIn"></comp-login>-->
                <router-view></router-view>
                <!--                <comp-client-profil v-if="!Loading && isLogged" :client="Client"></comp-client-profil>-->
            </div>
        </div>

    </div>

<?php
get_footer();
