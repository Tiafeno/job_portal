<?php


namespace JP\Framework\Elements;


use JP\Framework\Traits\DemandeTrait;
use JP\Framework\Traits\DemandeTypeTrait;
use JP\framework\traits\ProfilAccessTrait;

class jDemande
{
    public $ID;
    public $user; // WP_User
    public $type_demande; // object
    public $reference;
    public $status; //0: en attente, 1: valider, 2: refuser
    public $data_request;
    public $url;
    public $purchase_informations;

    public function __construct(int $id_demande)
    {
        $demande = DemandeTrait::getDemande($id_demande);
        if (is_null($demande)) return;
        $this->ID = $demande->ID;
        $this->user = new \WP_User(intval($demande->user_id));
        unset($this->user->allcaps, $this->user->caps);
        $this->reference = $demande->reference;
        $this->type_demande = DemandeTypeTrait::getDemandeType(intval($demande->type_demande_id));
        $this->status = DemandeTrait::getStatus($id_demande);
        $this->data_request = unserialize($demande->data_request);

        if ($this->status === 1 && $this->type_demande->name === "DMD_CANDIDAT") { // validate
            if (isset($this->data_request->candidate_id)) {
                $candidate_id = (int) $this->data_request->candidate_id;
                $this->purchase_informations = ProfilAccessTrait::index($candidate_id, $demande->user_id);
            }

        }
    }

    public function getCustomerName() {
        if (!$this->user instanceof \WP_User) return null;
        return $this->user->display_name;
    }

    public function getCustomerEmail() {
        if (!$this->user instanceof \WP_User) return null;
        return $this->user->user_email;
    }

    public function getData(string $property) {
        if (property_exists($this->data_request, $property)) {
            return $this->data_request->{$property};
        }
        return null;
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
        return get_object_vars($clone);
    }
}