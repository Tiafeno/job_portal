<?php

final class AdminManager {
    public function __construct() {
        add_filter( 'manage_users_columns', [&$this, 'user_head_table'] );
        add_filter( 'manage_users_custom_column', [&$this, 'manage_user_table'], 10, 3 );
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
}

new AdminManager();