<?php
/*
 * Template Name: Client Page
 * description: Page pour les clients, candidat ou employer
 */

wp_enqueue_script(
    'comp-client',
    get_stylesheet_directory_uri() . '/assets/js/component-client.js',
    ['vue-router', 'axios', 'wpapi', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'sortable', 'comp-login', 'vue-select'],
    null,
    true
);
wp_localize_script('comp-client', 'clientApiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'current_user_id' => get_current_user_id()
]);

get_header();
?>

<script id="experience-template" type="text/x-template">
    <div class="edu-history info"> <i></i>
        <div class="detail-info" @click="$emit('edit', $event, item._id)">
            <h3>{{item.office}}</h3>
            <i>{{item.b}} - {{item.e}}</i>
            <p>{{item.desc}}</p>
        </div>
    </div>
</script>

<script id="education-template" type="text/x-template">
    <div class="edu-history info"> <i></i>
        <div class="detail-info" @click="$emit('edit', $event, item._id)">
            <h3>{{item.establishment}}</h3>
            <i>{{item.b}} - {{item.e}}</i>
            <span>{{item.diploma}}</span>
            <p>{{item.desc}}</p>
        </div>
    </div>
</script>

<script type="text/x-template" id="client-cv">
    <div id="cv">
        <div>
            <h2 class="font-bold">REFERENCE
                <a target="_blank" title="Voir le CV" class="text-muted" href="https://www.itjobmada.com/candidate/cv563/">#CV563</a>
            </h2>
        </div>
        <div>
        <v-select :options="[{label: 'Canada', code: 'ca'}]"></v-select>
        </div>
        <div class="col-md-12 col-sm-12" id="educations">
            <div class="detail-wrapper">
                <div class="detail-wrapper-header">
                    <h4>Educations</h4>
                </div>
                <div class="detail-wrapper-body" id="education-list">
                    <comp-education v-for="education in getEducations()" v-if="!Loading" 
                    @edit="editEducation"
                    :key="education._id" 
                    :item="education"
                    :year_range="yearRange">
                    </comp-education>
                </div>
                <div class="padd-l-15 padd-bot-15">
                    <button type="button" @click="addEducation()" class="btn-job theme-btn">+ Ajouter</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12" id="experiences">
            <div class="detail-wrapper">
                <div class="detail-wrapper-header">
                    <h4>Work Experience</h4>
                </div>
                <div class="detail-wrapper-body" id="experience-list">
                    <comp-experience v-for="experience in getExperiences()" v-if="!Loading" 
                    @edit="editExperience"
                    :key="experience._id" 
                    :item="experience" 
                    :year_range="yearRange">
                    </comp-experience>
                </div>
                <div class="padd-l-15 padd-bot-15">
                    <button type="button" @click="addExperience()" class="btn-job theme-btn">+ Ajouter</button>
                </div>
            </div>
        </div>

        <!-- exxperience modal Code -->
        <div class="modal fade" id="experience" style="background-color:  rgba(0, 0, 0, 0.85)" tabindex="-1" role="dialog"
        aria-labelledby="expModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" id="expModal">
                    <div class="modal-header">
                        <h5 class="modal-title">Experience</h5>
                    </div>
                    <div class="modal-body">
                        <form @submit="validateExpForm" method="post" action="" novalidate>
                            <div class="row">

                                <div class="col-md-12">
                                    <label class="col-form-label">Office <span style="color: red">*</span></label>
                                    <div class="form-group">
                                        <input placeholder="" autocomplete="off" name="office" v-model="formExpEdit.office"
                                            class="form-control" required="">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="col-form-label">Enterprise <span style="color: red">*</span></label>
                                    <div class="form-group">
                                        <input placeholder="" autocomplete="on" v-model="formExpEdit.enterprise" name="enterprise" class="form-control "
                                            required="">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="col-form-label">Ville <span style="color: red">*</span></label>
                                    <div class="form-group">
                                        <input placeholder="Ex: Majunga" autocomplete="off" v-model="formExpEdit.city" name="city"
                                            class="form-control"
                                            required="">
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <label class="col-form-label">Pays <span style="color: red">*</span></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input placeholder="Ex: Madagascar" v-model="formExpEdit.country" autocomplete="off" name="country"
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
                                                <select name="b" v-model="formExpEdit.b" class="form-control" required="">
                                                    <option v-for="year in yearRange" :value="year">{{year}}</option>
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
                                                <select class="form-control" v-model="formExpEdit.e" name="e">
                                                    <option :value="''">Poste actuel</option>
                                                    <option v-for="year in yearRange" :value="year">{{year}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-sm-12">
                                    <label class="col-form-label ">Description <span style="color: red">*</span></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <textarea placeholder="" autocomplete="off" v-model="formExpEdit.desc" name="desc"
                                                class="form-control"
                                                required=""></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="text-center">
                                        <button type="button" @click="deleteExperience($event, formExpEdit._id)" 
                                        v-if="formExpSelected != null" class="btn btn-m ">Supprimer</button>

                                        <button type="submit" class="btn btn-m theme-btn ">Enregistrer</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- End experience modal -->

    <!-- education modal Code -->
<div class="modal fade" id="education" style="background-color:  rgba(0, 0, 0, 0.85)" tabindex="-1" role="dialog"
     aria-labelledby="eduModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="eduModal">
            <div class="modal-header">
                <h5 class="modal-title">Education</h5>
            </div>
            <div class="modal-body">
                <form @submit="validateEduForm" method="post" action="" novalidate>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label ">Establishment <span style="color: red">*</span></label>
                            <div class="form-group">
                                <input placeholder="Ex: Université de Majunga" autocomplete="off" name="establishment" 
                                v-model="formEduEdit.establishment"
                                    class="form-control" required="">

                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label ">Diplôme <span style="color: red">*</span></label>
                            <div class="form-group">
                                <input placeholder="Ex: Master II" autocomplete="on" name="diploma" v-model="formEduEdit.diploma" class="form-control "
                                    required="">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label ">Ville <span style="color: red">*</span></label>
                            <div class="form-group">
                                <input placeholder="Ex: Majunga" autocomplete="off" name="city" v-model="formEduEdit.city"
                                    class="form-control"
                                    required="">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <label class="col-form-label ">Pays <span style="color: red">*</span></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input placeholder="Ex: Madagascar" autocomplete="off" v-model="formEduEdit.country" name="country"
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
                                        <select name="b" class="form-control" v-model="formEduEdit.b" required="">
                                            <option :value="''">Année</option>
                                            <option v-for="year in yearRange" :value="year">{{year}}</option>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="col-sm-12">
                                    <p >Année de fin (ou prévision)</p>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <select class="form-control" v-model="formEduEdit.e"
                                                name="e">
                                            <option :value="''">Année</option>
                                            <option v-for="year in yearRange" :value="year">{{year}}</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="text-center">
                            <button type="button" @click="deleteEducation($event, formEduEdit._id)" 
                                        v-if="formEduSelected != null" class="btn btn-m ">Supprimer</button>
                                <button type="submit" class="btn btn-m theme-btn full-width">Enregistrer</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End education modal -->
</div>


</script>

<script type="text/x-template" id="client-layout">
    <!-- ================ Profile Settings ======================= -->
    <section class="padd-top-80 padd-bot-80">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div id="leftcol_item">
                        <div class="user_dashboard_pic">
                            <span class="user-photo-action">Alden Smith</span>
                        </div>
                    </div>
                    <div class="dashboard_nav_item">
                        <ul>
                            <li class="active">
                                <router-link to="/"><i class="login-icon ti-dashboard"></i> Dashboard</router-link>
                                <router-link :to="{ path: '/cv' }"><i class="login-icon ti-dashboard"></i> Mon CV</router-link>
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


<div class="page-title">
    <div class="container">
        <div class="page-caption">
            <h2><?= get_the_title() ?></h2>
        </div>
    </div>
</div>
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
