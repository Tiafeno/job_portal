<?php


namespace JP\Framework\Elements;
use function _\indexOf;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core class used to implement the WP_User object.
 *
 * @since 2.0.0
 *
 * @property string $nickname
 * @property string $description
 * @property string $user_description
 * @property string $first_name
 * @property string $user_firstname
 * @property string $last_name
 * @property string $user_lastname
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property string $user_status
 * @property int $user_level
 * @property string $display_name
 * @property string $spam
 * @property string $deleted
 * @property string $locale
 * @property string $rich_editing
 * @property string $syntax_highlighting
 */
final class jpCompany extends \WP_User
{
    // Ces champs doivent être mis à jour manuellement
    // Utiliser l'action 'profile_update' https://developer.wordpress.org/reference/hooks/profile_update/
    public $company_name;
    public $address;
    public $region; // int - Reference with taxonomy 'Region'
    public $country; // int - Reference with taxonomy 'country'
    public $city;
    public $nif;
    public $stat;
    public $activated = 1;

    public function __construct($id = 0, $name = '', $site_id = '') {
        parent::__construct($id, $name, $site_id);
    }

    public function profile_update($args = []) {
        foreach ($args as $arg_key => $value) {
            if (property_exists('\\JP\\Framework\\Elements\\jpCompany', $arg_key)) {
                update_user_meta($this->ID, $arg_key, $value);
            }
        }
    }

    public static function is_company($id_object) {
        $user = get_user_by('ID', $id_object);
        if (!$user) return false;
        if ($user instanceof \WP_User) {
            return indexOf($user->roles, 'company') >= 0;
        }
        return false;
    }

    public function is_active() {
        $is_active = get_user_meta($this->ID, 'is_active', false); // int|bool
        return boolval($is_active);
    }
}