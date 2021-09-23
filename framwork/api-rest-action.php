<?php
class JOB_REST {
    public function __construct() {
        add_action('woocommerce_thankyou', [&$this, 'thank_you']);
        add_action('woocommerce_order_status_completed', array(&$this, 'thank_you'));
    }

    public function thank_you(int $order_id) {
        $order = wc_get_order($order_id);
    }
}

new JOB_REST();


class apiHelper {
    public function __construct() {}
    public static function getAccountPricingByID($id, $array) {
        foreach ( $array as $element ) {
            if ( $id == $element->_id ) {
                return $element;
            }
        }

        return false;
    }
}