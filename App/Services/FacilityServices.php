<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Location;
use App\Models\Tag;
use App\Plugins\Di\Injectable;

class FacilityServices extends Injectable
{
    protected $facilityModel;
    protected $locationModel;
    protected $tagModel;

    public function __construct()
    {
        $this->facilityModel = new Facility();
        $this->locationModel = new Location();
        $this->tagModel = new Tag();
    }

    public function createFacility($name, $locationData, $tagNames)
    {
        // ensure facility name does not get repeated
        $exists = $this->facilityModel->getFacilityByName($name);
        if ($exists) {
            return ['status' => false, 'message' => "Facility name already exists", 'code' => 409];
        }

        // create location and obtain locationId
        $locationId = $this->locationModel->createLocation(
            $locationData['city'],
            $locationData['address'],
            $locationData['zip_code'],
            $locationData['country_code'],
            $locationData['phone_number']
        );

        // create all tags and obtain tagIds
        $tagIDs = $this->createTags($tagNames);

        $this->facilityModel->createFacility($name, $locationId, $tagIDs);
        return ['status' => true, 'message' => "Facility successfully created!", 'code' => 201];
    }

    public function updateFacility($oldName, $newName, $tagNames)
    {
        // ensure facility exists before updating
        $exists = $this->facilityModel->getFacilityByName($oldName);
        if (!$exists) {
            return ['status' => false, 'message' => "Facility name does not exist", 'code' => 404];
        }

        // ensure new facility name does not exist
        $exists = $this->facilityModel->getFacilityByName($newName);
        if ($exists) {
            return ['status' => false, 'message' => "Cannot change facility name as it already exists", 'code' => 409];
        }

        // update tags individually
        foreach ($tagNames as $oldTagName => $newTagName) {
            $oldTagName = trim($oldTagName);
            $newTagName = trim($newTagName);
            if ($newTagName !== "") {
                $tagID = $this->tagModel->updateTag($oldTagName, $newTagName);
                if (!$tagID) {
                    echo "Tag name: " . $oldTagName . " does not exist";
                }
            }
        }

        $this->facilityModel->updateFacility($oldName, $newName);
        return ['status' => true, 'message' => "Facility successfully updated!", 'code' => 200];
    }

    public function getFacilityByName($name)
    {
        $result = $this->facilityModel->getFacilityByName($name);
        if ($result) {
            $data = [
                'name' => $result[0]['name'],
                'city' => $result[0]['city'],
                'tags' => []
            ];

            // appending all tags associated with a facility
            foreach ($result as $facility) {
                $data['tags'][] = $facility['tag_name'];
            }
            return ['status' => true, 'message' => $data, 'code' => 200];
        }

        return ['status' => false, 'message' => "No facilities found", 'code' => 404];
    }

    public function getAllFacilities()
    {
        $result = $this->facilityModel->getAllFacilities();
        if ($result) {
            $data = [];
            // adding all facility data to an array
            foreach ($result as $facility) {
                $facility_name = $facility['facility_name'];
                $data[$facility_name] = [
                    'city' => $facility['city'],
                    'tags' => []
                ];
            }

            // appending all tags associated with a particular facility
            foreach ($result as $facility) {
                $data[$facility['facility_name']]['tags'][] = $facility['tag_name'];
            }
            return ['status' => true, 'message' => $data, 'code' => 200];
        }

        return ['status' => false, 'message' => "No facilities found", 'code' => 404];
    }

    public function deleteFacility($name)
    {
        // ensure facility exists before deleting
        $exists = $this->facilityModel->getFacilityByName($name);
        if (!$exists) {
            return ['status' => false, 'message' => "Facility name: " . $name . " does not exist", 'code' => 404];
        }

        // obtain facility id to delete
        // id is especially needed because we delete from facility_tags table as well. hence name is not used to delete
        $selectQuery = "SELECT id FROM facilities WHERE name = :name";
        $bind = ['name' => $name];
        $result = $this->db->executeQuery($selectQuery, $bind);
        $facility = $result->fetch(\PDO::FETCH_ASSOC);
        $facilityId = $facility['id'];

        $this->facilityModel->deleteFacility($facilityId);
        return ['status' => true, 'message' => 'Facility successfully deleted!', 'code' => 200];
    }

    public function searchFacilitiesByFilter($filters)
    {
        $results = $this->facilityModel->searchFacilities($filters);
        if (!empty($results)) {
            $data = [];
            foreach ($results as $row) {
                $data[] = $row;
            }
            return ['status' => true, 'message' => $data, 'code' => 200];
        }

        return ['status' => false, 'message' => 'No facilities found matching the criteria', 'code' => 404];
    }

    // helper function to create tags
    private function createTags($tagNames)
    {
        $tagIDs = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName !== "") {
                $tagIDs[] = $this->tagModel->createTag($tagName);
            }
        }
        return $tagIDs;
    }
}