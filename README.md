# Catering Facilities API

This API provides a simple interface to perform CRUD operations on catering data and allows access to a MySQL database via PHPMyAdmin.

## Prerequisites
Ensure that [Docker](https://www.docker.com/) is installed on your system.

## Setup and Running the Project

1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Run the following command to build and start the containers in detached mode:
```bash
    docker-compose up --build -d
```
This will build the images and start the containers in the background.

## Acessing the API
Once the containers are up, you can access the API through the following:

- API Endpoint: Access the API via `http://localhost:8080/api/{route_name}`
- PHPMyAdmin: Access PHPMyAdmin for MySQL database via `http://localhost:8081 `
    - The login credentials for PHPMyAdmin are:
        - Username: `root`
        - Password: Leave the password field empty (no characters)



## Removing Containers and Volumes
To stop and remove the containers, run the following command with the `-v` flag set to remove the volumes as well.

```bash
    docker-compose down -v
```
This will stop the containers and delete the associated volumes.

