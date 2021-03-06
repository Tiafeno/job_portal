<?php
namespace JP\Framwork\Elements;
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
class jpCandidate extends \WP_User
{

    public $reference;
    public $region; // Region
    public $status; // Je cherche...
    public $drive_licences; // A, B, C & A`
    public $languages = [];
    public $phone = '';
    public $mastered_technology = [];
    public $educations = [];
    public $experiences = [];
    public $center_interest;
    public $newsletter;
    public $branch_activity;
    //public $activated = 1;

    public function __construct($id = 0, $name = '', $site_id = ''){
        parent::__construct($id, $name, $site_id);
    }

    public function profile_update($args = []) {
        foreach ($args as $arg_key => $value) {
            if (property_exists('\\JP\\Framwork\\Elements\\jpCandidate', $arg_key)) {
                update_user_meta($this->ID, $arg_key, $value);
            }
        }
    }

    public function hasCV() {
        $has_cv = get_user_meta($this->ID, 'has_cv', true);
        return (bool) $has_cv;
    }

    public function isPublic() {
        return $this->is_active();
    }

    public function is_active() {
        $is_active = get_user_meta($this->ID, 'is_active', true);
        return (bool) $is_active;
    }

    public function getExperiences() {
        $exp_encode = get_user_meta($this->ID, 'experiences', true);
        $experiences = $exp_encode ? json_decode($exp_encode) : [];
        return is_array($experiences) ? $experiences : [];
    }

    public function removeExperience(string $id) {
        $experience = $this->getExperiences();
    }

    public function getEducations() {
        $edu_encode  = get_user_meta($this->ID, 'educations', true);
        $educations = $edu_encode ? json_decode($edu_encode) : [];
        return $educations;
    }

}

