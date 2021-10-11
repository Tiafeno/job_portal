<?php

final class AdminManager {
    public function __construct() {
        add_filter( 'manage_users_columns', [&$this, 'user_head_table'] );
        add_filter( 'manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3 );
        add_action('admin_init', [&$this, 'init']);
    }

    public function user_head_table($column) {
        $column['is_active'] = 'Active';
        return $column;
    }

    public function manage_user_table($val, $column_name, $user_id) {
        switch ($column_name) {
            case 'is_active' :
                $user = WP_User::get_data_by('ID', $user_id);
                $is_active = get_metadata('user', $user->ID, 'is_active', true);
                $action_name = $is_active ? "deactivated" : "activated";
                return "<a class='activation button' href='" . admin_url( "users.php?action=user_activation&amp;user=$user->ID&amp;ref=$action_name") . "'>" . ucfirst($action_name) . "</a>";
            default:
        }
        return $val;
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
            // Mettre a jours la valeur
            update_metadata('user', $user_id, 'is_active', $value);
            wp_redirect(admin_url('user.php'));
        }
    }
}

new AdminManager();