<?php

final class AdminManager {
    public function __construct() {
        add_filter( 'manage_users_columns', [&$this, 'user_head_table'] );
        add_filter( 'manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3 );
        add_filter( 'user_row_actions', 'activation_user_action', 10, 2);
        add_action('admin_init', [&$this, 'init']);
    }

    public function user_head_table($column) {
        $column['is_active'] = 'Active';
        return $column;
    }

    public function manage_user_table($val, $column_name, $user_id) {
        switch ($column_name) {
            case 'is_active' :
                $is_active = get_metadata('user', $user_id, 'is_active', true);
                return $is_active ? "OUI" : "NON";
            default:
        }
        return $val;
    }

    public function activation_user_action($actions, WP_User $user) {
        $user_role = reset($user->roles);
        if (!in_array($user_role, ['candidate', 'company'])) return $actions;
        $is_active = get_metadata('user', $user->ID, 'is_active', true);
        $action_name = $is_active ? "deactivated" : "activated";
        $actions['activation'] = "<a class='activation' href='" . admin_url( "users.php?&action=user_activation&amp;user=$user->ID&amp;ref=$action_name") . "'>" . ucfirst($action_name) . "</a>";
        return $actions;
    }

    public function init() {
        /**
         * Cette action permet d'activer ou desactiver une utilisateur
         */
        $action = jpHelpers::getValue('action');
        if ($action === 'user_activation') {
            $ref = jpHelpers::getValue('ref');
            $value = $ref === 'deactivated' ? 0 : 1;
            $user_id = (int) jpHelpers::getValue('user');
            $reponse = update_metadata('user', $user_id, 'is_active', $value);
        }
    }
}

new AdminManager();