<?php

use JP\Framework\Traits\DemandeTrait;

final class AdminManager
{
    public function __construct()
    {
        add_action('restrict_manage_users', [$this, 'add_activated__filter']);
        add_action('pre_user_query', [$this, 'pre_user_query']);

        // User column
        add_filter('manage_users_columns', [&$this, 'user_head_table']);
        add_filter('manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3);

        // Jobs or annonce columns
        add_filter('manage_jp-jobs_posts_columns', function ($columns) {
            return array_merge($columns, ['company' => __('Entreprise', 'textdomain')]);
        });

        add_action('manage_jp-jobs_posts_custom_column', function ($column_key, $post_id) {
            if ($column_key == 'company') {
                $companies = new WP_User_Query([
                    'role' => 'company',
                    'number' => 100
                ]);

                $employer_id = (int)get_post_meta($post_id, 'employer_id', true);
                $post_company_id = (int)get_metadata('user', $employer_id, 'company_id', true);
                $option = '<option value=""></option>';
                $select = "<form method='post'>";
                $select .= '<select name="company" style="float:none;">%s</select>';

                if (!empty($companies->get_results())) {
                    foreach ($companies->get_results() as $company) {
                        $checked = ($post_company_id === $company->ID) ? "selected='selected'" : '';
                        $option .= sprintf('<option value="%d" %s>%s</option>', $company->ID, $checked, $company->display_name);
                    }
                }
                $select = sprintf($select, $option);
                $select .= '<input type="hidden" name="controller" value="update_emploie_company" />';
                $select .= '<input type="hidden" name="post_id" value="' . $post_id . '" />';
                $select .= '<input type="hidden" name="employer_id" value="' . $employer_id . '" />';
                $select .= '<input type="hidden" name="company_id" value="' . $post_company_id . '" />';
                $select .= '<input type="submit" value="Save" class="button button-primary">';
                $select .= '</form>';
                echo $select;
            }
        }, 10, 2);

        add_action('admin_init', [&$this, 'init']);
        add_action('admin_enqueue_scripts', [&$this, 'admin_enqueue']);
    }

    public function init()
    {
        // Cette action permet d'activer ou desactiver une utilisateur
        $controller = jTools::getValue('controller');

        // Cette condition permet de valider ou non un compte
        if ($controller === 'user_activation') {
            $name = jTools::getValue('name');
            $value = ($name === 'deactivated') ? 0 : 1;
            $user_id = (int)jTools::getValue('user');
            // Mettre a jours la valeur
            update_metadata('user', $user_id, 'validated', $value);
            // Verifier si c'est une entreprise
            $user = new WP_User($user_id);
            $template = in_array('company', $user->roles) ? 'company' : 'candidate';
            if ('company' === $template) {
                $employer_id = get_metadata('user', $user->ID, 'employer_id', true);
                $user_id = intval($employer_id);
                $template = 'company';
            }

            // Send mail to user
            if (1 === $value) {
                do_action('send_mail_activated_account', $user_id, $template);
            }
            wp_redirect(admin_url('users.php'));
        }

        // Cette condition permet d'ajouter une entreprise pour une annonce
        if ($controller === 'update_emploie_company') {
            $company_id = jTools::getValue('company', 0);
            $post_id = jTools::getValue('post_id', 0);
            if ($company_id) {
                // Add employer id
                $employer_query = new WP_User_Query([
                    'role' => 'employer',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'company_id',
                            'value' => $company_id,
                            'compare' => '='
                        )
                    )
                ]);
                if ($employer_query->get_results()) {
                    $results = $employer_query->get_results();
                    $employer = $results[0]; // Get first result
                    if ($employer instanceof WP_User) {
                        // Add company id
                        update_post_meta($post_id, 'company_id', $company_id);
                        update_post_meta($post_id, 'employer_id', $employer->ID);
                    }
                }
            }
        }

        if ($controller === 'DEMANDE') {
            global $wpdb;
            $admin_id = get_current_user_id();
            $admin = new WP_User($admin_id);

            // Only an administrator can confirm the demande
            if (in_array('administrator', $admin->roles)) {
                $id_demande = (int)jTools::getValue('demande_id', 0);
                $dmd_table = DemandeTrait::getTableName();
                $request_demande = "SELECT * FROM $dmd_table WHERE ID = %d";
                $result = $wpdb->get_row($wpdb->prepare($request_demande, intval($id_demande)));
                $wpdb->flush();
                if ($result) {
                    //0 : en attente, 1: valider, 2: refuser
                    $status = (int)jTools::getValue('status', 0); // Default en attente (pending)
                    $updateDemande = DemandeTrait::updateStatus($status, $id_demande);
                    $traiter = DemandeTrait::traiter($id_demande);
                }
            }

        }

    }

    public function add_activated__filter($which)
    {
        $value = jTools::getValue('activation', null);
        // create sprintf templates for <select> and <option>s
        $st = '<select name="activation" style="float:none;">%s</select>';
        $ot = '<option value="%d" %s>%s</option>';
        // generate <option> and <select> code
        $options = implode('', array_map(function ($i) use ($ot, $value) {
            $name = ($i == 0) ? 'Deactivate' : 'Activate';
            return sprintf($ot, $i, $value == $i ? "checked" : "", $name);
        }, [0, 1]));
        $select = sprintf($st, $options);
        echo $select;
        // output <select> and submit button
        submit_button(__('Trouver'), null, $which, false);
    }

    public function pre_user_query($query)
    {
        //global $pagenow;
        global $wpdb;
        $action = jTools::getValue('activation', null);
        if ($action != '0' && $action != '1') return $query;
        $query->query_where = "WHERE {$wpdb->users}.ID IN (
            SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
                WHERE {$wpdb->usermeta}.meta_key = 'validated' AND {$wpdb->usermeta}.meta_value = '{$action}')";
        return $query;
    }

    public function user_head_table($column)
    {
        $column['validated'] = 'Activation';
        $column['employer'] = 'Emp. / Ent.';
        $column['verify_email'] = 'Email v.';
        return $column;
    }

    public function manage_user_table($val, $column_name, $user_id)
    {
        $user = new WP_User($user_id);
        $current_user_role = reset($user->roles);
        $no_required = "-";
        switch ($column_name) {
            case 'validated' :
                if (!in_array($current_user_role, ['candidate', 'company'])) {
                    return $no_required;
                }
                $validated = get_metadata('user', $user->ID, 'validated', true);
                $action_name = $validated ? "deactivated" : "activated";
                $btn_class = $validated ? '' : "button-primary";
                $user_activation_url = admin_url("users.php?controller=user_activation&user=$user->ID&name=$action_name");
                return "<a class='activation button $btn_class' href='" . $user_activation_url . "'>" . ucfirst($action_name) . "</a>";
            case 'employer':
                if (!in_array($current_user_role, ['company', 'employer'])) {
                    return $no_required;
                }
                // For employer
                if ('employer' === $current_user_role) {
                    $uId = get_metadata('user', $user_id, 'company_id', true);
                }
                // For company
                if ('company' === $current_user_role) {
                    $uId = get_metadata('user', $user_id, 'employer_id', true);
                }
                if (!$uId) return $no_required;
                $user_relation = new WP_User(intval($uId));
                $edit_link = get_edit_user_link($user_relation->ID);
                return "<a href='$edit_link' target='_blank' class='button button-primary' >$user_relation->display_name</a>";
            case 'verify_email':
                if (!in_array($current_user_role, ['candidate', 'employer'])) {
                    return $no_required;
                }
                $is_verify = get_metadata('user', $user->ID, 'email_verify', true);
                $value = (!$is_verify || 0 === intval($is_verify)) ? "Non" : "Oui";
                return sprintf("<span class='user-email-status %s'>%s</span>",
                    $value === "Non" ? "user-no-verify" : "user-verify", $value);
            default:
        }
        return $val;
    }

    public function admin_enqueue($hook)
    {
        //if ( 'users.php' != $hook ) return;
        wp_enqueue_style('admin-jobjiaby', get_stylesheet_directory_uri() . '/assets/css/admin.css', [], true);
    }
}

new AdminManager();