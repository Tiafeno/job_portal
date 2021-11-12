<?php


namespace JP\Framework\Traits;


use JP\Framework\Elements\jDemande;

trait DemandeTrait
{
    public static $tableName;
    public static function getTableName() {
        global $wpdb;
        return self::$tableName = $wpdb->prefix . 'demande';
    }

    /**
     * @param int $status
     * @param int $id
     * @return bool|int - The number of rows updated, or false on error.
     */
    public static function updateStatus(int $status = 0, int $id) {
        global $wpdb;
        $updateDemande = $wpdb->update(self::getTableName(), ['status' => $status], ['ID' => $id]);
        $wpdb->flush();
        return $updateDemande;
    }

    /**
     * @param int $id
     * @return int
     * 0: pending, 1: accept, 2: reject
     */
    public static function getStatus(int $id) {
        global $wpdb;
        $table = self::getTableName();
        $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE ID = %d", $id));
        $wpdb->flush();
        return intval($status);
    }

    /**
     * @param int $id
     * @return object|null
     */
    public static function traiter(int $id): ?object {
        global $wpdb;
        $table = self::getTableName();
        $demande = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $id));
        $wpdb->flush();
        if ($demande) {
            //0 : en attente, 1: valider, 2: refuser
            $status = (int)$demande->status;
            $type_demande_name = DemandeTypeTrait::getTypeName((int) $demande->type_demande_id);
            if (1 === $status && 'DMD_CANDIDAT' === $type_demande_name) {
                // Add profil access
                $request_data = unserialize($demande->data_request); // object
                $candidate_id = intval($request_data->candidate_id);
                $employer_id = intval($demande->user_id);

                // generate pursache key
                $add_profil = ProfilAccessTrait::add(wp_generate_uuid4(), $employer_id, $candidate_id);

                do_action('send_mail_demande_accepted', $id); //todo create mail body
            }
            if (0 === $status && 'DMD_CANDIDAT' === $type_demande_name) {
                // reject demande
            }
        }
        return null;
    }

    public static function getDemande(int $id): ?object {
        global $wpdb;
        $table = self::getTableName();
        $demande = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $id));
        $wpdb->flush();
        return $demande;
    }

    public static function getDemandes() {
        global $wpdb;
        $responses = [];
        $table = self::getTableName();
        $demandes = $wpdb->get_results($wpdb->prepare("SELECT ID FROM $table"));
        $wpdb->flush();
        foreach ($demandes as $demande) {
            $responses[] = new jDemande($demande->ID);
        }
        return $responses;
    }
}
