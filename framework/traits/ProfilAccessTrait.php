<?php


namespace JP\framework\traits;


trait ProfilAccessTrait
{
    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . 'profil_employer_access';
    }

    /**
     * @param string|null $purchase_key
     * @param int $employer_id
     * @param int $candidate_id
     * @return false|int
     */
    public static function add(?string $purchase_key = null, int $employer_id = 0, int $candidate_id = 0) {
        global $wpdb;
        $tbl = self::getTableName();
        $insert_request = $wpdb->insert($tbl, [
            'employer_id' => $employer_id,
            'candidate_id'  => $candidate_id,
            'purchased' => 1, // Acheter
            'purchase_key' =>  $purchase_key
        ]);
        $wpdb->flush();
        return $insert_request;
    }
}