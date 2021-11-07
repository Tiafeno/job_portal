<?php


namespace JP\Framework\Elements;


use JP\Framework\Traits\DemandeTrait;
use JP\Framework\Traits\DemandeTypeTrait;

final class JDemande
{

    public $user; // WP_User
    public $type_demande; // object
    public $status; //0: en attente, 1: valider, 2: refuser
    public $data;
    public function __construct(int $id_demande)
    {
        $demande = DemandeTrait::getDemande($id_demande);
        if (is_null($demande)) return;
        $this->user = new \WP_User(intval($demande->user_id));
        $this->type_demande = DemandeTypeTrait::getDemandeType(intval($demande->type_demande_id));
        $this->status = DemandeTrait::getStatus($id_demande);
        $this->data = unserialize($demande->data_request);
    }

    public function getData(string $property) {
        if (property_exists($this->data, $property)) {
            return $this->data->{$property};
        }
        return null;
    }
}