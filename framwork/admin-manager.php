<?php

final class AdminManager
{
    public function __construct()
    {
        add_action('restrict_manage_users', [$this, 'add_activated__filter']);
        add_filter('pre_get_users', [$this, 'activated_user__filter']);

        add_filter('manage_users_columns', [&$this, 'user_head_table']);
        add_filter('manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3);
        add_action('admin_init', [&$this, 'init']);
    }


    public function add_activated__filter($which)
    {
        // create sprintf templates for <select> and <option>s
        $st = '<select name="activation" style="float:none;">%s</select>';
        $ot = '<option value="%d" >%s</option>';
        // generate <option> and <select> code
        $options = implode('', array_map(function ($i) use ($ot) {
            $name = ($i == 0) ? 'Deactivate' : 'Activate';
            return sprintf($ot, $i, $name);
        }, [0, 1]));
        $select = sprintf($st, $options);
        echo $select;
        // output <select> and submit button
        submit_button(__('Filter'), null, $which, false);
    }


    public function activated_user__filter($query)
    {
        global $pagenow;
        if (is_admin() && 'users.php' == $pagenow) {
            if ($section = $_GET['activation']) {
                $meta_query = [['key' => 'is_active', 'value' => $section, 'compare' => 'LIKE']];
                $query->set('meta_key', 'is_active');
                $query->set('meta_query', $meta_query);
            }
        }
    }


    public function user_head_table($column)
    {
        $column['is_active'] = 'Active';
        $column['employer'] = 'Relation';
        return $column;
    }

    public function manage_user_table($val, $column_name, $user_id)
    {
        $user = new WP_User($user_id);
        $no_required = "-";
        switch ($column_name) {
            case 'is_active' :
                if (!in_array($user->roles[0], ['candidate', 'company'])) {
                    return $no_required;
                }
                $is_active = get_metadata('user', $user->ID, 'is_active', true);
                $action_name = $is_active ? "deactivated" : "activated";
                $btn_class = $is_active ? '' : "button-primary";
                return "<a class='activation button $btn_class' href='" . admin_url("users.php?controller=user_activation&amp;user=$user->ID&amp;name=$action_name") . "'>" . ucfirst($action_name) . "</a>";
            case 'employer':
                $current_user_role = reset($user->roles);
                if (!in_array($current_user_role, ['company', 'employer'])) {
                    return $no_required;
                }
                // For employer
                if ('employer' === $current_user_role) {
                    $uId = get_metadata('user', $user_id, 'company_id', true);
                }
                if ('company' === $current_user_role) {
                    $uId = get_metadata('user', $user_id, 'employer_id', true);
                }
                if (!$uId) return $no_required;
                $user_relation = new WP_User(intval($uId));
                $edit_link = get_edit_user_link($user_relation->ID);
                return "<a href='$edit_link' target='_blank' class='button button-primary' >$user_relation->display_name</a>";

                break;
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
            wp_redirect(admin_url('users.php'));
        }
    }
}

new AdminManager();