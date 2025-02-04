<?php

namespace App\Controllers;

use App\Services\FacilityServices;

class FacilitiesController extends BaseController
{
    public function __construct()
    {
        $this->facilityService = new FacilityServices();
    }

    /**
     * Sends a JSON HTTP response
     *
     * @param int $statusCode HTTP status code
     * @param bool $success Whether the request was successful or not
     * @param string|array $message Response message or data
     * @return void
     */
    public function sendHttpResponse($statusCode, $success, $message)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ], JSON_PRETTY_PRINT);
    }

    public function createFacility()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // validate the input
        $validation = $this->facilityService->validateCreateFacilityInput($input);
        if (!$validation['status']) {
            $this->sendHttpResponse(400, false, $validation['message']);
            return;
        }

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

        $validation = $this->facilityService->validateUpdateFacilityInput($input);
        if (!$validation['status']) {
            $this->sendHttpResponse(400, false, $validation['message']);
            return;
        }

        $oldName = $input['oldName'];
        $newName = $input['newName'];
        $tagNames = $input['tagNames'];

        $result = $this->facilityService->updateFacility($oldName, $newName, $tagNames);

        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    /**
     * Retrieves all facilities with the provided name
     * Sends an error response if no name is provided
     *
     * @param string $fname Name of facility
     * @return void
     */
    public function getFacility($fname)
    {
        // performing input validation in same function since its a small check
        if (empty($fname)) {
            $this->sendHttpResponse(400, false, "Facility name is required");
            return;
        }

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
        if (empty($name)) {
            $this->sendHttpResponse(400, false, "Facility name is required");
            return;
        }

        // obtain facility id to delete
        // id is especially needed because we delete from facility_tags table as well. hence name is not used to delete
        $facilityId = $this->facilityService->getFacilityIdByName($name);

        $result = $this->facilityService->deleteFacility($facilityId);
        $this->sendHttpResponse($result['code'], $result['status'], $result['message']);
    }

    /**
     * Searches for facilities based on provided filters
     * Extracts filters from URL parameters and returns matching results
     *
     * @return void
     */
    public function searchFacilitiesByFilter()
    {
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

