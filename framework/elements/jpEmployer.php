<?php
namespace JP\Framework\Elements;
if (!defined('ABSPATH')) {
    exit;
}


class jpEmployer extends \WP_User
{
    public function __construct($id = 0, $name = '', $site_id = '') {
        parent::__construct($id, $name, $site_id);
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

    public function get_company_object() {
        $company = $this->get_company();
        if (!$company) return new \WP_Error('', "Aucun entreprise");
        return new jpCompany($company->ID);
    }
}
