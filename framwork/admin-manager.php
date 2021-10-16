<?php

final class AdminManager
{
    public function __construct()
    {
        add_action('restrict_manage_users', [$this, 'add_activated__filter']);
        add_action('pre_user_query', [$this, 'pre_user_query']);

        add_filter('manage_users_columns', [&$this, 'user_head_table']);
        add_filter('manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3);

        add_filter('manage_jp-jobs_posts_columns', function($columns) {
            return array_merge($columns, ['company' => __('Entreprise', 'textdomain')]);
        });

        add_action('manage_jp-jobs_posts_custom_column', function($column_key, $post_id) {
            // todo show company for this annonce
            if ($column_key == 'company') {
                $duration = get_post_meta($post_id, 'duration', true);
                $select = '<select name="company" style="float:none;">%s</select>';
                $option = '<option value="%d" %s>%s</option>';

                echo (!empty($duration)) ? sprintf(__('%s minutes', 'textdomain'), $duration) : __('Unknown', 'textdomain');
            }
        }, 10, 2);

        add_action('admin_init', [&$this, 'init']);
        add_action('admin_enqueue_scripts', [&$this, 'admin_enqueue']);
    }


    public function add_activated__filter($which)
    {
        $value = jpHelpers::getValue('activation', null);
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
        $action = jpHelpers::getValue('activation', null);
        if ($action != '0' && $action != '1') return $query;
        $query->query_where = "WHERE {$wpdb->users}.ID IN (
            SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
                WHERE {$wpdb->usermeta}.meta_key = 'is_active' AND {$wpdb->usermeta}.meta_value = '{$action}')";
        return $query;
    }

    public function user_head_table($column)
    {
        $column['is_active'] = 'Activation';
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
            case 'is_active' :
                if (!in_array($current_user_role, ['candidate', 'company'])) {
                    return $no_required;
                }
                $is_active = get_metadata('user', $user->ID, 'is_active', true);
                $action_name = $is_active ? "deactivated" : "activated";
                $btn_class = $is_active ? '' : "button-primary";
                $user_activation_url = admin_url("users.php?controller=user_activation&amp;user=$user->ID&amp;name=$action_name");
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

    public function init()
    {
        /**
         * Cette action permet d'activer ou desactiver une utilisateur
         */
        $controller = jpHelpers::getValue('controller');
        if ($controller === 'user_activation') {
            $name = jpHelpers::getValue('name');
            $value = ($name === 'deactivated') ? 0 : 1;
            $user_id = (int)jpHelpers::getValue('user');
            // Mettre a jours la valeur
            update_metadata('user', $user_id, 'is_active', $value);

            // Send mail to user
            if (1 === $value) do_action('send_mail_activated_account', $user_id);

            // Redirection
            wp_redirect(admin_url('users.php'));
        }
    }

    public function admin_enqueue($hook) {
        //if ( 'users.php' != $hook ) return;
        wp_enqueue_style( 'admin-jobjiaby', get_stylesheet_directory_uri() . '/assets/css/admin.css', [], true);
    }
}

new AdminManager();