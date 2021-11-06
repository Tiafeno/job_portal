<?php

namespace JP\Framework\Elements;

use Doctrine\Common\Collections\ArrayCollection;

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
class jCandidate extends \WP_User
{

    public $gender;
    public $reference;
    public $region; // Region
    public $status; // Je cherche...
    public $validated; // Check for this account is active or not
    public $has_cv = false;
    public $blocked = false;
    public $drive_licences = []; // A, B, C & A`
    public $languages = [];
    public $phone = '';
    public $profil_url = null;
    public $mastered_technology = [];
    public $educations = [];
    public $experiences = [];
    public $avatar = null;
    public $center_interest;
    public $newsletter;
    public $branch_activity;

    //public $activated = 1;

    public function __construct($id = 0, $name = '', $site_id = '')
    {
        parent::__construct($id, $name, $site_id);

        $this->validated = $this->validated();
        $this->phone = get_user_meta($this->ID, 'phone', true);
        // reference
        $this->reference = "CV{$this->ID}";

        // Candidate status
        $configs = \jTools::getInstance()->getSchemas();
        $config_status = $configs->candidat_status;
        $status = get_user_meta($this->ID, 'cv_status', true);
        if (is_array($config_status)) {
            $collection = new ArrayCollection($config_status);
            $this->status = $collection->filter(function ($el) use ($status) {
                return $status == $el->_id;
            })->first();
        }

        // Candidate Region
        $region_id = get_metadata('user', $this->ID, 'region', true);
        $region_term = get_term($region_id, 'region', OBJECT);
        if ($region_term instanceof \WP_Term) {
            $this->region = $region_term;
        }

        // profil url
        $this->profil_url = get_site_url(null, "/candidate/#/candidate/{$this->ID}");

        // genre
        $gender = get_metadata('user', $this->ID, 'gender', true);
        if ($gender) {
            $this->gender = ($gender === 'M.') ? 'Femme' : 'Monsieur';
        }

        // Languages
        $languages = get_metadata('user', $this->ID, 'languages', true);
        try {
            $languages = json_decode($languages);
            if (is_array($languages)) {
                $langCollection = new ArrayCollection($languages);
                $this->languages = $langCollection->map(function($lang) {
                    $lTerm = get_term(intval($lang), 'language');
                    if ($lTerm instanceof \WP_Term)  return $lTerm;
                    return null;
                })->filter(function($term) { return !is_null($term); })->toArray();
            }
            
        } catch (\JsonException $e) {
            $this->languages = "";
        }

        // have cv
        $this->has_cv = $this->hasCV();

        // blocked check
        $this->blocked = $this->isBlocked();

        // Experiences
        $experiences = get_metadata('user', $this->ID, 'experiences', true);
        try {
            $experiences_encode = json_decode($experiences);
            $this->experiences = $experiences_encode;
        } catch (\JsonException $e) {
            $this->experiences = "";
        }

        // Educations
        $educations = get_metadata('user', $this->ID, 'educations', true);
        try {
            $edu_encode = json_decode($educations);
            $this->educations = $edu_encode;
        } catch (\JsonException $e) {
            $this->educations = "";
        }

        // avatar
        $avatar_id = get_metadata('user', $this->ID, 'avatar_id', true);
        if ($avatar_id) {
            $this->avatar = [
                'upload_dir' => wp_upload_dir(),
                'metadata' => wp_get_attachment_metadata(intval($avatar_id)),
            ];
        }

    }

    public function profile_update($args = [])
    {
        foreach ($args as $arg_key => $value) {
            if (property_exists('\\JP\\Framework\\Elements\\jCandidate', $arg_key)) {
                update_user_meta($this->ID, $arg_key, $value);
            }
        }
    }

    public function hasCV(): bool
    {
        $has_cv = get_user_meta($this->ID, 'has_cv', true);
        return (bool)$has_cv;
    }

    public function isPublic(): bool
    {
        return $this->validated();
    }

    public function validated(): bool
    {
        $validated = get_user_meta($this->ID, 'validated', true);
        return !$validated ? false : (bool)$validated;
    }

    public function isBlocked(): bool {
        $blocked = get_user_meta($this->ID, 'blocked', true);
        return !$blocked ? false : (bool)$blocked;
    }

    /**
     * @return array|mixed
     */
    public function getExperiences(): array
    {
        $exp_encode = get_user_meta($this->ID, 'experiences', true);
        $experiences = $exp_encode ? json_decode($exp_encode) : [];
        return is_array($experiences) ? $experiences : [];
    }

    /**
     * @return array|mixed
     */
    public function getEducations(): array
    {
        $edu_encode = get_user_meta($this->ID, 'educations', true);
        $educations = $edu_encode ? json_decode($edu_encode) : [];
        return $educations;
    }

    public function getObject($context = 'view') {
        if ($context === 'edit') {
            return get_object_vars($this);
        }
        $clone = $this;
        unset($clone->allcaps, $this->data, $this->avatar);
        return get_object_vars($clone);
    }

}

