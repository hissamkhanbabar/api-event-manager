<?php

namespace HbgEventImporter;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Location extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'location';

    public function beforeSave()
    {
        $this->post_title = !is_string($this->post_title) ? $this->post_title : DataCleaner::string($this->post_title);
        $this->street_address = !is_string($this->street_address) ? $this->street_address : DataCleaner::string($this->street_address);
        $this->postal_code = !is_string($this->postal_code) ? $this->postal_code : DataCleaner::string($this->postal_code);
        $this->city = !is_string($this->city) ? $this->city : DataCleaner::string($this->city);
        $this->municipality = !is_string($this->municipality) ? $this->municipality : DataCleaner::string($this->municipality);
        $this->country = !is_string($this->country) ? $this->country : DataCleaner::string($this->country);
        $this->latitude = !is_string($this->latitude) ? $this->latitude : DataCleaner::string($this->latitude);
        $this->longitude = !is_string($this->longitude) ? $this->longitude : DataCleaner::string($this->longitude);
        $this->_event_manager_uid = !is_string($this->_event_manager_uid) ? $this->_event_manager_uid : DataCleaner::string($this->_event_manager_uid);
    }

    public function afterSave()
    {
        $res = Helper\Address::gmapsGetAddressComponents($this->street_address . ' ' . $this->postal_code . ' ' . $this->city . ' ' . $this->country);

        if (!isset($res->geometry->location)) {
            return;
        }

        update_post_meta($this->ID, 'map', array(
            'address' => $res->formatted_address,
            'lat' => $res->geometry->location->lat,
            'lng' => $res->geometry->location->lng
        ));

        update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
    }
}
