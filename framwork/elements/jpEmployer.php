<?php
namespace JP\Framwork\Elements;
if (!defined('ABSPATH')) {
    exit;
}


class jpEmployer extends \WP_User
{
    public $company_id = 0;
    public function __construct($id = 0, $name = '', $site_id = '')
    {
        parent::__construct($id, $name, $site_id);
    }

}

class EmployerHelper {
    public function __construct() {

    }

    public static function getInstance() {
        return new self();
    }
}