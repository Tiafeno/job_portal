<?php

wp_enqueue_script('comp-archive-jobs', get_stylesheet_directory_uri() . '/assets/js/comp-archive-jobs.js',
    ['axios', 'wpapi', 'jquery', 'bluebird', 'lodash', 'paginationjs'], null, true);
wp_localize_script('comp-archive-jobs', 'archiveApiSettings',[
    'root' => esc_url_raw( rest_url() ),
    'nonce' => wp_create_nonce( 'wp_rest' ),
    'isLogged' => is_user_logged_in(),
    'userId' => get_current_user_id(),
]);

get_header();
?>
<style>
    .count-item {
        background: #26ae61;
        color: #ffffff;
        width: 30px;
        height: 30px;
        display: inline-block;
        border-radius: 50%;
        margin-left: 4px;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        line-height: 28px;
    }
    .can-skils li {
        padding: 2px 0 !important;
    }
</style>
<!-- Template-->
<script  id="filter-salary-template" type="text/x-template">
    <div class="widget-boxed padd-bot-0" v-if="items.length > 0">
        <div class="widget-boxed-header">
            <h4>Salaire offert</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="side-list no-border">
                <ul>
                    <li v-for="item in items">
                        <span class="custom-checkbox">
                            <input type="checkbox" class="salary-filter" :id="item.id" :name="'salaries'" :value="item.id" v-on:change="selectedFilter">
                            <label :for="item.id"></label>
                        </span> {{ item.filter_name }} <span class="pull-right"> <span class="count-item">{{ item.count }} </span></span>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</script>
<script  id="filter-category-template" type="text/x-template">
    <div class="widget-boxed padd-bot-0" v-if="items.length > 0">
        <div class="widget-boxed-header">
            <h4>Secteur d'activité</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="side-list no-border">
                <ul>
                    <li v-for="item in items">
                        <span class="custom-radio">
                            <input type="radio" class="category-filter" :id="item.id" :checked="valueSelected === item.id" :name="'cat'" :value="item.id" v-on:change="selectedFilter">
                            <label :for="item.id"></label>
                        </span><i class="count-item">{{ item.count }} </i> {{ item.name }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</script>
<script  id="filter-region-template" type="text/x-template">
    <div class="widget-boxed padd-bot-0" v-if="items.length > 0">
        <div class="widget-boxed-header">
            <h4>Region</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="side-list no-border">
                <ul>
                    <li v-for="item in items">
                <span class="custom-radio">
                    <input type="radio" class="region-filter" :id="item.id" :checked="valueSelected === item.id" :name="'region'" :value="item.id" v-on:change="selectedFilter">
                    <label :for="item.id"></label>
                </span> <i class="count-item">{{ item.count }} </i> {{ item.name }}
                    </li>

                </ul>
            </div>
        </div>
    </div>
</script>
<script  id="filter-search-template" type="text/x-template">
        <div class="widget-boxed padd-bot-0">
            <div class="widget-boxed-body">
                <div class="search_widget_job">
                    <div class="field_w_search">
                        <input type="text" class="form-control" @keyup="searchKey($event)"  placeholder="Rechercher des mot-clés">
                    </div>
<!--                    <div class="field_w_search">-->
<!--                        <input type="text" class="form-control" placeholder="All Locations">-->
<!--                    </div>-->
                </div>
            </div>
        </div>
    </script>
<script type="text/x-template" id="apply-job">
    <div>
        <button @click="apply()" type="button" class="btn-job theme-btn job-apply">{{buttonText}}</button>
        <p class="text-muted font-12 padd-l-5 padd-r-5 " v-if="message.success !== null"
           v-bind:class="{'alert-info': message.success,  'alert-danger': !message.success}">
            {{ message.data }}
        </p>
    </div>
</script>
<script type="text/x-template" id="job-vertical-lists">
    <div class="job-verticle-list">
        <div class="vertical-job-card">
            <div class="vertical-job-header">
                <div class="vrt-job-cmp-logo">
                    <a :href="item.link"><img :src="item.company.avatar_urls[96]" class="img-responsive" alt=""></a>
                </div>
                <h4><a href="job-detail.html"></a>{{ item.company.name }}</h4>
                <span class="com-tagline">{{item.title.rendered}}</span> <span class="pull-right vacancy-no">ID. <span class="v-count">{{item.id}}</span></span>
            </div>
            <div class="vertical-job-body">
                <div class="row">
                    <div class="col-md-9 col-sm-12 col-xs-12">
                        <ul class="can-skils">
                            <li><strong>Type de contrat: </strong>{{ item.get_type_name}}</li>
                            <li><strong>Expérience: </strong>{{item.meta.experience == 0 ? '0-3 Mois' : item.meta.experience + ' ans'}}</li>
                            <li><strong>Secteur d'activité: </strong>{{item.get_cat_name}}</li>
                        </ul>
                    </div>
                    <div class="col-md-3 col-sm-12 col-xs-12">
                        <div class="vrt-job-act">
                            <comp-apply :jobid="item.id"></comp-apply>

                            <a :href="item.link" title="" class="btn-job light-gray-btn">Voir offre</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
<script type="text/x-template" id="pagination-jobs-template">
    <div class="utf_flexbox_area padd-0" id="pagination-archive"></div>
</script>
<script id="job-archive-template" type="text/x-template">
    <section class="padd-top-80 padd-bot-80" id="archive-jobs">
        <div class="container padd-top-40">
            <div class="row">
                <div class="col-md-5 col-sm-5">
                    <div class="left floated">
                        <button type="button" @click="resetFilter" class="btn light-gray">Reset filter</button>
                    </div>
                    <filter-search v-on:changed="applyFilter"></filter-search>
                    <filter-salary
                            v-bind:salaries="taxonomies.Salaries"
                            v-if="typeof taxonomies.Salaries === 'object'"
                            v-on:changed="applyFilter">
                    </filter-salary>
                    <filter-region
                            v-bind:regions="taxonomies.Regions"
                            v-if="typeof taxonomies.Regions === 'object'"
                            v-on:changed="applyFilter">
                    </filter-region>
                    <filter-category
                            v-bind:categories="taxonomies.Categories"
                            v-if="typeof taxonomies.Categories === 'object'"
                            v-on:changed="applyFilter">
                    </filter-category>
                </div>

                <!-- Start Job List -->
                <div class="col-md-7 col-sm-7">
                    <div class="row mrg-bot-20">
                        <div class="col-md-4 col-sm-12 col-xs-12 browse_job_tlt">
                            <h4 class="job_vacancie" v-if="paging !== null">{{ paging.total }} Emplois & postes vacants</h4>
                        </div>
                        <div class="col-md-8 col-sm-12 col-xs-12">
                            <div class="fl-right short_by_filter_list">
                                <div class="search-wide short_by_til">
<!--                                    <h5>Short By</h5>-->
                                </div>
                                <div class="search-wide full">
                                    <select name="per_page" @change="Route($event.currentTarget.value, 'per_page')" class="wide form-control">
                                        <option :value="n" v-for="n in inputPerPages">{{ n }} Per Page</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lds-roller" v-if="loadArchive"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                    <!-- Single Verticle job -->
                    <job-vertical-lists v-if="!loadArchive" v-for="(item, index) in archives" :item="item" :key="item.id" ></job-vertical-lists>
                    <div class="alert alert-warning" role="alert" v-if="archives.length === 0 && !loadArchive">
                        Aucune annonce trouver
                    </div>
                    <div class="clearfix"></div>
                    <com-pagination v-if="paging !== null" v-bind:paging="paging" @change-route-page="Route" v-bind:pagesize="per_page"></com-pagination>

                </div>
            </div>
            <!-- End Row -->
        </div>
    </section>
</script>
<!-- .end template-->
    <div id="archive-jobs">
        <div class="lds-roller" v-if="loading"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        <comp-archive-jobs v-if="!loading" v-bind:taxonomies="Taxonomies"></comp-archive-jobs>
    </div>
<?php
get_footer();