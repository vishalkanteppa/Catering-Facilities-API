<?php

namespace App\Controllers;

use App\Models\Facility;
use App\Models\Location;
use App\Models\Tag;
use App\Plugins\Http\Response as Status;

class FacilitiesController extends BaseController
{
    protected $facilityModel;
    protected $locationModel;

    public function __construct()
    {
        $this->facilityModel = new Facility();
        $this->locationModel = new Location();
        $this->tagModel = new Tag();
    }

    public function createFacility()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // obtain parameters from the input
        $name = $input['name'];

        // ensure facility names do not get repeated
        $selectQuery = "SELECT * FROM facilities WHERE name = :name";
        $result = $this->db->executeQuery($selectQuery, ['name' => $name]);
        if ($result && $result->rowCount() > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => "Facility name already exists",
            ]);
            return;
        }

        $locationData = $input['locationData'];
        $tagNames = $input['tagNames'];

        $locationId = $this->locationModel->createLocation(
            $locationData['city'],
            $locationData['address'],
            $locationData['zip_code'],
            $locationData['country_code'],
            $locationData['phone_number']
        );

        $tagIDs = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName !== "") {
                $tagID = $this->tagModel->createTag($tagName);
                if ($tagID) {
                    array_push($tagIDs, $tagID);
                }
            }
        }
        $this->facilityModel->createFacility($name, $locationId, $tagIDs);
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => "Facility successfully created!",
        ]);
    }

    public function updateFacility()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $oldName = $input['oldName'];
        $exists = $this->facilityModel->getFacilityByName($oldName);
        if (!$exists) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => "Facility name: " . $oldName . " does not exist",
            ]);
            return;
        }

        $newName = $input['newName'];
        $exists = $this->facilityModel->getFacilityByName($newName);
        if ($exists) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => "Cannot change facility name to: " . $newName . " as it already exists",
            ]);
            return;
        }

        $tagNames = $input['tagNames'];

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
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Facility and tags successfully updated!",
        ]);
    }

    //obtain all instances of a facility with a name
    public function getFacility($fname)
    {
        $result = $this->facilityModel->getFacilityByName($fname);
        if ($result) {
            foreach ($result as $facility) {
                echo json_encode($facility);
                echo "<br>"; // separate each facility
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No results found',
            ]);
            return;
        }
        http_response_code(200);
    }

    public function getAllFacilities()
    {
        $result = $this->facilityModel->getAllFacilities();
        if ($result) {
            foreach ($result as $facility) {
                echo json_encode($facility);
                echo "<br>";
            }
        } else {
            
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No facilities found',
            ]);
            return;
        }
        http_response_code(200);
    }


    public function deleteFacility($name)
    {
        $selectQuery = "SELECT id FROM facilities WHERE name = :name";
        $bind = ['name' => $name];
        $result = $this->db->executeQuery($selectQuery, $bind);
        if (!$result || $result->rowCount() <= 0) {
            
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'This facility does not exist',
            ]);
            return;
        }
        $facility = $result->fetch(\PDO::FETCH_ASSOC);
        $facilityId = $facility['id'];

        $this->facilityModel->deleteFacility($facilityId);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Facility successfully deleted!',
        ]);
    }

    public function searchFacilitiesByFilter()
    {
        // obtain parameters from URL
        $facilityName = $_GET['facility_name'] ?? null;
        $tagName = $_GET['tag_name'] ?? null;
        $locationCity = $_GET['location_city'] ?? null;

        $filters = [
            'facility_name' => $facilityName,
            'tag_name' => $tagName,
            'location_city' => $locationCity,
        ];

        $results = $this->facilityModel->searchFacilities($filters);

        if (!empty($results)) {
            http_response_code(200);
            foreach ($results as $row) {
                echo json_encode($row);
                echo '<br>';
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No facilities found matching the search criteria.',
            ]);
        }
    }

}

