# Salesforce assignment

**Prerequisites**

- Install composer 2.0.
- Install docker (latest version), docker-compose (latest version).
- Linux OS required. This project runs with Laravel Sail, so it is best to run in a Linux OS machine. I have tried with WSL2, but it is having some issues with this platform when installing new packages.

---------------
**Project setup**

1. Copy .env.example to .env

2. At the root of the project, run the following command to install the project's dependencies
````
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
````

3. Initialize application containers using Laravel Sail:

- Configure a Bash alias which allows you to execute Sail's commands more easily, run the following command:

````  
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
````

- Then Sail is now ready to use with:

````
sail up -d // Initialize the containers (detached mode)

sail down -v // Stop the containers, remove volumes
````

4. Once all containers are up and running (this might take several minutes to install necessary dependencies of the project)

5. Generate application key

````
sail php artisan key:generate
````

6. Install npm packages

````
sail npm install
````

7. Once all npm packages are installed, start the dev environment

````
sail npm run dev
````

8. Now open a new browser and navigate to the following URL:

``http://localhost/contacts``

Here, you will see the Contacts list. Currently, the list supports pagination and the table cells can be edited inline.

**API Guidelines**

The base URL of all APIs is: http://localhost/api/v1 .

1. Contact API: 

- GET /contacts : Fetch all contacts from Salesforce. The API supports pagination.


- PATCH /contacts/{id} : Update a contact by salesforce's contact id

  Sample payload:

  ````
  { "FirstName": "Tuan edited" }
  ````
