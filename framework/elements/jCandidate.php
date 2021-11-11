<?php

namespace JP\Framework\Elements;

use Doctrine\Common\Collections\ArrayCollection;
use JsonException;

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
    public $registered_date;
    public $reference;
    public $profil;
    public $region; // Region e.g [2, 8 ...]
    public $status; // Je cherche...
    public $validated; // Check for this account is active or not
    public $has_cv = false;
    public $blocked = false;
    public $drive_licences = []; // A, B, C & A`
    public $languages = [];  //  e.g [22, 1 ...]
    public $categories = [];  // e.g [85, 7 ...]
    public $last_name;
    public $first_name;
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

        $this->name = $this->display_name;
        $this->email = $this->user_email;
        $this->first_name = $this->user_firstname;
        $this->last_name = $this->user_lastname;
        $this->registered_date = $this->user_registered;
        $this->profil = get_metadata('user', $this->ID, 'profil', true);

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
            if (empty($languages)) {
                throw new \Exception("Languages is not array");
            }
            $languages = json_decode($languages);
            if (is_array($languages)) {
                $langCollection = new ArrayCollection($languages);
                $this->languages = $langCollection->map(function($lang) {
                    $lTerm = get_term(intval($lang), 'language');
                    if ($lTerm instanceof \WP_Term)  return $lTerm;
                    return null;
                })->filter(function($term) { return !is_null($term); })->toArray();
            }
            
        } catch (\Exception $e) {
            $this->languages = [];
        }

        // Categories ou metier recherché
        $categories = get_metadata('user', $this->ID, 'categories', true);
        try {
            if (empty($categories)) {
                throw new \Exception("Categories is not array");
            }
            $categories = json_decode($categories);
            if (is_array($categories)) {
                $catCollection = new ArrayCollection($categories);
                $this->categories = $catCollection->map(function($cat) {
                    $catTerm = get_term(intval($cat), 'category');
                    if ($catTerm instanceof \WP_Term)  return $catTerm;
                    return null;
                })->filter(function($term) { return !is_null($term); })->toArray();
            }
        } catch (\Exception $e) {
            $this->categories = [];
        }

        // have cv
        $this->has_cv = $this->hasCV();

        // blocked check
        $this->blocked = $this->isBlocked();

        // Experiences
        /*  _id: '', office: '', enterprise: '', city: '', country: '', b: '', e: '', desc: '', locked: [bool] */
        $experiences = get_metadata('user', $this->ID, 'experiences', true);
        if (!empty($experiences)) {
            try {
                $experiences_encode = json_decode($experiences);
                if (is_array($experiences_encode) && !empty($experiences_encode)) {
                    $collectionExp = new ArrayCollection($experiences_encode);
                    $this->experiences = $collectionExp->map(function($exp) {
                        $exp->office = htmlentities2($exp->office);
                        $exp->desc = htmlentities2($exp->desc);
                        $exp->enterprise = htmlentities2($exp->enterprise);
                        return $exp;
                    })->toArray();
                }
            } catch (JsonException $e) {
                $this->experiences = [];
            }
        }


        // Educations
        /* _id: '', establishment: '', diploma: '', city: '', country: '', desc: '', b: '', e: '', locked: [bool] */
        $educations = get_metadata('user', $this->ID, 'educations', true);
        if (!empty($educations)) {
            try {
                $edu_encode = json_decode($educations);
                if (is_array($edu_encode) && !empty($edu_encode)) {
                    $collectionEdu = new ArrayCollection($edu_encode);
                    $this->educations = $collectionEdu->map(function($edu) {
                        $edu->establishment = htmlentities2($edu->establishment);
                        return $edu;
                    })->toArray();
                }
            } catch (JsonException $e) {
                $this->educations = [];
            }
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

    /**
     * @param array $args
     */
    public function profile_update($args = [])
    {
        foreach ($args as $arg_key => $value) {
            if (property_exists('\\JP\\Framework\\Elements\\jCandidate', $arg_key)) {
                update_user_meta($this->ID, $arg_key, $value);
            }
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        if ($key !== 'experiences' || $key !== 'educations')
            $this->{$key} = $value;
        update_user_meta($this->ID, $key, $value);
    }

    /**
     * @return bool
     */
    public function hasCV(): bool
    {
        $has_cv = get_user_meta($this->ID, 'has_cv', true);
        return (bool)$has_cv;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->validated();
    }

    /**
     * @return bool
     */
    public function validated(): bool
    {
        $validated = get_user_meta($this->ID, 'validated', true);
        return !$validated ? false : (bool)$validated;
    }

    /**
     * @return bool
     */
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

    /**
     * @param string $context
     * @return array
     */
    public function getObject($context = 'view') {
        if ($context === 'edit') {
            return get_object_vars($this);
        }
        $clone = $this;
        unset($clone->allcaps, $this->data, $this->avatar);
        return get_object_vars($clone);
    }

}

