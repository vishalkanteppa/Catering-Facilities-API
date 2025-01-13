<?php
namespace App\Models;
use App\Plugins\Di\Injectable;

class Facility extends Injectable
{
    public function createFacility($name, $locationId, $tagIds = [])
    {
        $query = "INSERT INTO facilities (name, location_id, creation_date) VALUES (:name, :location_id, NOW());";
        $bind = ['name' => $name, 'location_id' => $locationId];
        $result = $this->db->executeQuery($query, $bind);
        if ($result) {
            // obtain id of the created facility
            $facilityId = (int) $this->db->executeQuery("SELECT LAST_INSERT_ID()")->fetchColumn();
        } else {
            echo "Failed to create facility.";
            return 0;
        }
        $this->updateFacilityTags($facilityId, $tagIds);
    }

    public function getFacilityByName($name)
    {
        $query = "SELECT f.name, l.city, t.name AS tag_name
                  FROM facilities f
                  JOIN locations l ON f.location_id = l.id
                  LEFT JOIN facility_tags ft ON f.id = ft.facility_id
                  LEFT JOIN tags t ON ft.tag_id = t.id
                  WHERE f.name = :name";
        $bind = ['name' => $name];
        $result = $this->db->executeQuery($query, $bind);
        return $result ? $result->fetchAll(\PDO::FETCH_ASSOC) : null;
    }

    public function getAllFacilities()
    {
        $query = "SELECT f.name AS facility_name, l.city, t.name AS tag_name
                  FROM facilities f
                  JOIN locations l ON f.location_id = l.id
                  LEFT JOIN facility_tags ft ON f.id = ft.facility_id
                  LEFT JOIN tags t ON ft.tag_id = t.id";
        $result = $this->db->executeQuery($query);
        return $result ? $result->fetchAll(\PDO::FETCH_ASSOC) : null;
    }


    public function updateFacility($oldName, $newName)
    {
        $query = "UPDATE facilities SET name = :newName WHERE name = :oldName";
        $bind = ['oldName' => $oldName, 'newName' => $newName];
        $this->db->executeQuery($query, $bind);
    }

    public function deleteFacility($id)
    {
        $query = "DELETE FROM facilities WHERE id = :id";
        $bind = ['id' => $id];
        $this->db->executeQuery($query, $bind);

        // delete from facility_tags table
        $query = "DELETE FROM facility_tags WHERE facility_id = :facility_id";
        $bind = ['facility_id' => $id];
        $this->db->executeQuery($query, $bind);
    }

    private function updateFacilityTags($facilityId, $tagIds)
    {
        // delete existing tags to avoid duplicates
        $deleteQuery = "DELETE FROM facility_tags WHERE facility_id = :facility_id";
        $this->db->executeQuery($deleteQuery, ['facility_id' => $facilityId]);
        foreach ($tagIds as $tagId) {
            $insertQuery = "INSERT INTO facility_tags (facility_id, tag_id) VALUES (:facility_id, :tag_id)";
            $bind = ['facility_id' => $facilityId, 'tag_id' => $tagId];
            $this->db->executeQuery($insertQuery, $bind);
        }
    }

    public function searchFacilities(array $filters): array
    {
        $query = "SELECT DISTINCT f.id, f.name AS facility_name, l.city AS location_city
              FROM facilities f
              JOIN locations l ON f.location_id = l.id
              LEFT JOIN facility_tags ft ON f.id = ft.facility_id
              LEFT JOIN tags t ON ft.tag_id = t.id
              WHERE 1 = 1";

        $bind = [];

        if (!empty($filters['facility_name'])) {
            $query .= " AND f.name LIKE :facility_name";
            $bind['facility_name'] = '%' . $filters['facility_name'] . '%';
        }

        if (!empty($filters['tag_name'])) {
            $query .= " AND t.name LIKE :tag_name";
            $bind['tag_name'] = '%' . $filters['tag_name'] . '%';
        }

        if (!empty($filters['location_city'])) {
            $query .= " AND l.city LIKE :location_city";
            $bind['location_city'] = '%' . $filters['location_city'] . '%';
        }

        $query .= " ORDER BY f.name";

        $result = $this->db->executeQuery($query, $bind);
        return $result ? $result->fetchAll(\PDO::FETCH_ASSOC) : [];
    }

}

