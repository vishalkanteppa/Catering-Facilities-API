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

    public function validateCreateFacilityInput($input)
    {
        // ensuring none of the inputs are empty
        if (empty($input['name']) || empty($input['locationData']) || empty($input['tagNames'])) {
            return ['status' => false, 'message' => "Missing required fields"];
        }

        $locationData = $input['locationData'];
        if (empty($locationData['city']) || empty($locationData['address']) || empty($locationData['zip_code']) || empty($locationData['country_code']) || empty($locationData['phone_number'])) {
            return ['status' => false, 'message' => "Incomplete location data"];
        }

        if (empty($input['tagNames'])) {
            return ['status' => false, 'message' => "Tags cannot be empty"];
        }
        return ['status' => true];
    }

    public function validateUpdateFacilityInput($input)
    {
        if (empty($input['oldName']) || empty($input['newName']) || empty($input['tagNames'])) {
            return ['status' => false, 'message' => "Missing required fields"];
        }

        // check if tagNames is an associative array with valid tags
        if (!is_array($input['tagNames']) || empty($input['tagNames'])) {
            return ['status' => false, 'message' => "Tag names must be in an associative array format"];
        }

        // check that tagNames are key-value pairs
        foreach ($input['tagNames'] as $key => $value) {
            if (empty($key) || empty($value)) {
                return ['status' => false, 'message' => "Tag names must contain non empty keys and values"];
            }
        }
        return ['status' => true];
    }

    /**
     * creates a new facility with the given name, location and tags
     *
     * @param string $name Name of facility
     * @param array $locationData Location details
     * @param array $tagNames List of tag names associated with the facility
     * @return array Response with status, message and HTTP code
     */
    public function createFacility($name, $locationData, $tagNames)
    {
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

        $facilityId = $this->facilityModel->createFacility($name, $locationId, $tagIDs);

        $this->facilityModel->updateFacilityTags($facilityId, $tagIDs);

        return ['status' => true, 'message' => "Facility successfully created!", 'code' => 201];
    }

    /**
     * Updates an existing facilitys name and tags
     *
     * @param string $oldName Current facility name
     * @param string $newName New facility name
     * @param array $tagNames Array with key-value pairs mapping old tag name to new tag name
     * @return array Response with status, message, and HTTP code
     */
    public function updateFacility($oldName, $newName, $tagNames)
    {
        // ensure facility exists before updating
        $exists = $this->facilityModel->getFacilityByName($oldName);
        if (!$exists) {
            return ['status' => false, 'message' => "Facility name does not exist", 'code' => 404];
        }

        // update tags individually
        foreach ($tagNames as $oldTagName => $newTagName) {
            $oldTagName = trim($oldTagName);
            $newTagName = trim($newTagName);
            if ($newTagName !== "") {
                $tagID = $this->tagModel->updateTag($oldTagName, $newTagName);
                if (!$tagID) {
                    return ['status' => false, 'message' => $oldTagName . " does not exist", 'code' => 404];
                }
            }
        }

        $id = $this->getFacilityIdByName($oldName);

        $this->facilityModel->updateFacility($id, $oldName, $newName);
        return ['status' => true, 'message' => "Facility successfully updated!", 'code' => 200];
    }

    /**
     * Retrieves a facility by its name
     *
     * @param string $name Name of the facility
     * @return array Response containing facility details or error message
     */
    public function getFacilityByName($name)
    {
        $result = $this->facilityModel->getFacilityByName($name);
        if ($result) {
            return ['status' => true, 'message' => $result, 'code' => 200];
        }

        return ['status' => false, 'message' => "No facilities found", 'code' => 404];
    }

    /**
     * Retrieves all facilities from the database.
     * Create empty filters to obtain all facilities from db
     * Re-using logic from searchFacilitiesByFilter
     * 
     * @return array Response with a list of facilities or an error message.
     */
    public function getAllFacilities()
    {
        $filters = [
            "facility_name" => null,
            "tag_name" => null,
            "location_city" => null
        ];
        return $this->searchFacilitiesByFilter($filters);
    }

    public function deleteFacility($facilityId)
    {
        if (!$facilityId) {
            return ['status' => false, 'message' => "Facility does not exist", 'code' => 404];
        }

        $this->facilityModel->deleteFacility($facilityId);
        return ['status' => true, 'message' => 'Facility successfully deleted!', 'code' => 200];
    }

    /**
     * Searches facilities based on some criteria and returns results
     *
     * @param array $filters Search criteria
     * @return array Response with filtered data
     */
    public function searchFacilitiesByFilter($filters = [])
    {
        $results = $this->facilityModel->searchFacilities($filters);
        if (!empty($results)) {
            $data = $this->parseFilteredData($results);
            return ['status' => true, 'message' => $data, 'code' => 200];
        }

        return ['status' => false, 'message' => 'No facilities found', 'code' => 404];
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

    public function getFacilityIdByName($name)
    {
        $query = "SELECT id FROM facilities WHERE name = :name";
        $bind = ['name' => $name];
        $result = $this->db->executeQuery($query, $bind);

        $facility = $result->fetch(\PDO::FETCH_ASSOC);
        return $facility ? $facility['id'] : null;
    }

    /**
     * Groups facilities by ID and collects respective tags
     *
     * @param array $results Facility data with id, facility_name, location_city and tag
     * @return array Processed facilities with tags in a list
     */
    public function parseFilteredData($results)
    {
        $facilities = [];

        foreach ($results as $row) {
            $id = $row['id'];

            if (!isset($facilities[$id])) {
                $facilities[$id] = [
                    'facility_name' => $row['facility_name'],
                    'location_city' => $row['location_city'],
                    'tags' => []
                ];
            }

            if (!empty($row['tag']) && !in_array($row['tag'], $facilities[$id]['tags'])) {
                $facilities[$id]['tags'][] = $row['tag'];
            }
        }

        $data = array_values($facilities);
        return $data;
    }

}

