# FATEC Internship Management API

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge&logo=appveyor)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![DockerBadge](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Symfony](https://img.shields.io/badge/symfony-%23000000.svg?style=for-the-badge&logo=symfony&logoColor=white)

<img src="./images/fatec_logo.png" alt="logo" style="width:250px;height:72px;">

Our API is designed to provide easy access to up-to-date information about student internships, including internship
applications, approvals, placements, and evaluations. With FATEC Internship Management API, developers can easily build
web or mobile applications that enable students, faculty, and administrators to manage internships more efficiently.


This guide will walk you through setting up a Symfony project with Docker for development.

Prerequisites

    Docker and Docker Compose installed on your machine.

Setup

- Clone the project repository:

        git clone git@github.com:lotaviods/link-fatec-api.git


- Copy the .env.dist file to .env:

        cp .env-example .env

- Build and start the Docker containers:

        docker-compose up --build

- Install the project dependencies:

        docker exec -it estagio_fatec_api  /bin/bash

        composer install

- Run the database:

        docker exec -it estagio_fatec_api  /bin/bash

        php bin/console doctrine:database:create

        php bin/console doctrine:schema:update --complete --force

The server will automatically reload any changes you make to the code.
Stopping the Containers

- To stop the Docker containers, run the following command:

        docker-compose down

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Welcome to FATEC Internship Management API - a powerful API that provides access to a wide range of data related to the
management of student internships at the São Paulo State Technological College (FATEC).

