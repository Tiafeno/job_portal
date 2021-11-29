<?php


namespace JP\framework\traits;


use JP\Framework\Elements\jCandidate;
use JP\Framework\Elements\jpEmployer;

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

    public static function index(int $candidate_id, int $employer_id = 0) {
        global $wpdb;
        $employer_id = ($employer_id === 0) ? get_current_user_id() : $employer_id;
        $tbl = self::getTableName();
        $response = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $tbl WHERE candidate_id = %d AND employer_id = %d",
            $candidate_id, $employer_id));
        $wpdb->flush();

        if (is_null($response) || !$response) {
            return null;
        }

        $access = new \stdClass();
        $access->employer = new jpEmployer((int) $response->employer_id);
        $access->candidate = new jCandidate((int) $response->candidate_id);
        $access->purchased = $response->purchased;
        $access->create_at = $response->date_add;
        $access->purchase_key = $response->purchase_key;

        return $access;
    }
}