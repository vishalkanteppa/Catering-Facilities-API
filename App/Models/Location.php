<?php
namespace App\Models;
use App\Plugins\Di\Injectable;

class Location extends Injectable
{
    public function createLocation($city, $address, $zipCode, $countryCode, $phoneNumber)
    {
        $query = "INSERT INTO locations (city, address, zip_code, country_code, phone_number) 
                  VALUES (:city, :address, :zip_code, :country_code, :phone_number);";
        $bind = [
            'city' => $city,
            'address' => $address,
            'zip_code' => $zipCode,
            'country_code' => $countryCode,
            'phone_number' => $phoneNumber
        ];
        $result = $this->db->executeQuery($query, $bind);
        if ($result) {
            $locationId = (int) $this->db->executeQuery("SELECT LAST_INSERT_ID()")->fetchColumn();
            return $locationId;
        } else {
            echo "Failed to insert location.";
            return 0;
        }
    }

    public function getLocationById($id)
    {
        $query = "SELECT * FROM locations WHERE id = :id";
        $bind = ['id' => $id];
        $result = $this->db->executeQuery($query, $bind);
        if (!$result) {
            return null;
        }
        return $result->fetch(\PDO::FETCH_ASSOC);
    }
}
