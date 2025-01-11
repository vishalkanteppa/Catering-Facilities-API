<?php
namespace App\Db;
use App\Controllers\BaseController;

class Setup extends BaseController
{
    public function runSetup()
    {
        // Assuming $this->db is the database instance from the DI container
        $query = "
            CREATE TABLE IF NOT EXISTS facilities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL
            );
            CREATE TABLE IF NOT EXISTS locations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                city VARCHAR(255) NOT NULL,
                address VARCHAR(255) NOT NULL,
                zip_code VARCHAR(10),
                country_code VARCHAR(3),
                phone_number VARCHAR(20)
            );
            CREATE TABLE IF NOT EXISTS facility_tags (
                facility_id INT,
                tag_id INT,
                FOREIGN KEY (facility_id) REFERENCES facilities(id),
                FOREIGN KEY (tag_id) REFERENCES tags(id),
                PRIMARY KEY (facility_id, tag_id)
            );
            CREATE TABLE IF NOT EXISTS facility_location (
                facility_id INT,
                location_id INT,
                FOREIGN KEY (facility_id) REFERENCES facilities(id),
                FOREIGN KEY (location_id) REFERENCES locations(id),
                PRIMARY KEY (facility_id, location_id)
            );
        ";

        // Execute the query to set up the database
        $this->db->executeQuery($query, []);
    }
}
