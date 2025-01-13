<?php

namespace App\Controllers;

use App\Models\Facility;
use App\Models\Location;
use App\Models\Tag;
use App\Services\FacilityServices;
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
        $this->facilityService = new FacilityServices();
    }

    // function used to generate responses
    public function sendHttpResponse($statusCode, $success, $message) {
        http_response_code($statusCode);
        print_r( json_encode([
            'success' => $success,
            'message' => $message,
        ]));
    }

    public function createFacility()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // obtain parameters from the input
        $name = $input['name'];
        $locationData = $input['locationData'];
        $tagNames = $input['tagNames'];

        $result = $this->facilityService->createFacility($name, $locationData, $tagNames);
        
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    public function updateFacility()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $oldName = $input['oldName'];
        $newName = $input['newName'];
        $tagNames = $input['tagNames'];

        $result = $this->facilityService->updateFacility($oldName, $newName, $tagNames);
        
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    //obtain all instances of a facility with a name
    public function getFacility($fname)
    {
        $result = $this->facilityService->getFacilityByName($fname);
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    public function getAllFacilities()
    {
        $result = $this->facilityService->getAllFacilities();
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    public function deleteFacility($name)
    {
        $result = $this->facilityService->deleteFacility($name);
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
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

        $result = $this->facilityService->searchFacilitiesByFilter($filters);
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }
}

