[![SymfonyInsight](https://insight.symfony.com/projects/3ee1ad82-ce45-4266-a3d1-c29aa39b7c1a/mini.svg)](https://insight.symfony.com/projects/3ee1ad82-ce45-4266-a3d1-c29aa39b7c1a) [![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# P6 OC DAPS - SNOWTRICKS

For Project Number 6, the objective was to develop a community-based web application focused on Snowboarding. Registered users have the ability to create Snowboarding tricks and add media content, including images and videos.

Since it is a community project, any registered user can edit or delete a Snowboarding trick.

A set of requirements and wireframes were provided, and it was mandatory to adhere to them during the development process.

#### All UML diagrams of the project are available in the [diagrams](https://github.com/MH-DevApp/OC_Projet_6/tree/develop/diagrams) folder.

## Specs

* PHP >= 8.1
* Symfony 5.4
* Bootstrap 5.2.3
* Font Awesome 6.4.0

### Success criteria
The website must be responsive & secured. Code quality assessments done via [SymfonyInsight](https://insight.symfony.com/projects/3ee1ad82-ce45-4266-a3d1-c29aa39b7c1a).

## Install, build and run

First clone or download the source code and extract it.

### Local webserver
___
#### Requirements
- You need to have composer on your computer
- Your server needs PHP >= 8.1
- MySQL or MariaDB
- Apache or Nginx
- Mailer service

The following PHP extensions need to be installed and enabled :
- pdo_mysql
- mysqli
- intl

#### Install

1. To install dependencies with Composer:

    ```bash
    > composer install
    ```

2. Creation of a `.env.dev.local` file with the following information:

   **Note: `*user*` and `*password*` should be replaced with your own credentials for your database.**

   example :

    ```dotenv
   DATABASE_URL="mysql://*user*:*password*@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"
   MAILER_DSN=smtp://localhost:1025
    ```


3. To run the script for load all fixtures:

    ```bash
    > composer run load
    ```

7. To launch a PHP development server:

   **Note: Please free up port 3000 or modify it in the following command.**

    ```bash
    > php -S localhost:3000 -t public/
    ```
   
   or

   ```bash
   > symfony serve -port=3000
   ```

The website is available at the url: https://localhost:3000

### With Docker
___
#### Requirements
To install this project, you will need to have [Docker](https://www.docker.com/) installed on your Computer.

#### Install

Once your Docker configuration is up and ready, you can follow the instructions below:

1. To create a volume for the database:

    ```bash
    > docker volume create oc_dev
    ```

2. Creation of a `.env.dev.local` file with the following information:

   example :

    ```dotenv
   DATABASE_URL="mysql://root:password@db/oc_p6_dev?serverVersion=8&charset=utf8mb4"
   MAILER_DSN=smtp://mailer:1025
    ```

3. To build a Docker image:

   **Note: Please free up port 3000.**

    ```bash
    > docker-compose -f ../docker-compose.dev.yml up -d --build --remove-orphans
    ```

4. To run the script for load all fixtures:

    ```bash
    > docker exec -it php composer run load
    ```

5. To destroy/remove a Docker image, you can use the following command:

    ```bash
    > docker-compose -f ../docker-compose.dev.yml down -v --remove-orphans
    ```
The generated Docker container uses PHP8.2, MySQL 8.0, phpMyAdmin and mailcatcher.

The website is available at the url: https://localhost:3000

#### DBMS

You can access the DBMS (phpMyAdmin) to view and configure your database. Please go to the url: http://localhost:8080.

- Username: `root` ;
- Password: `password`.

This assumes that you have set up a Docker container running phpMyAdmin and configured it to run on port 8080. Make sure that the Docker container is running and accessible before attempting to access phpMyAdmin.

#### Mailer
You can access the mailer interface to view mails caught : http://localhost:5001.

### USERS CREDENTIALS
[List of Users](https://github.com/MH-DevApp/OC_Projet_6/blob/develop/src/DataFixtures/data/data-user.json)

Default account:
   - Username : `user`
   - Password for all users : `123456`

