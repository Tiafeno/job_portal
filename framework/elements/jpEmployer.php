<?php
namespace JP\Framework\Elements;
if (!defined('ABSPATH')) {
    exit;
}


class jpEmployer extends \WP_User
{
    public $company = null;
    public $first_name;
    public $last_name;
    public $email;
    public $register_date;

    public $validated;
    public $blocked;

    public function __construct($id = 0, $name = '', $site_id = '') {
        parent::__construct($id, $name, $site_id);
        $company = $this->get_company_object();
        if ($company instanceof \WP_Error) {
            $this->company = null;
        } else {
            $this->company = $company;
        }
        $this->first_name = $this->user_firstname;
        $this->last_name = $this->user_lastname;
        $this->email = $this->user_email;
        $this->register_date = $this->user_registered;

        // blocked check
        $this->blocked = $this->isBlocked();
        $this->validated = $this->validated();
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool {
        $blocked = get_user_meta($this->ID, 'blocked', true);
        return !$blocked ? false : (bool)$blocked;
    }


    /**
     * @return bool
     * return true if user is valid, otherwise false
     */
    public function validated(): bool
    {
        $validated = get_user_meta($this->ID, 'validated', true);
        return !$validated ? false : (bool)$validated;
    }


    /**
     * @return bool
     */
    public function has_company() {
        $has_company = get_user_meta($this->ID, 'company_id', true);
        $has_company = intval($has_company);
       return is_int($has_company);
    }

    /**
     * @return false|\WP_Error|\WP_User
     */
    public function get_company() {
        if (!$this->has_company()) return new \WP_Error(0, "Not enterprise register");
        $company_id = get_user_meta($this->ID, 'company_id', true);
        $company_id = intval($company_id);
        return get_user_by('ID', $company_id);
    }

    /**
     * @return jpCompany|\WP_Error
     */
    public function get_company_object() {
        $company = $this->get_company();
        if (!$company) return new \WP_Error('', "Aucun entreprise");
        return new jpCompany($company->ID);
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
