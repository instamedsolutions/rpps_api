# Welcome to the Medical Database & API

## Description

This project contains all scripts to fetch and store data as well as a fully functioning API to fetch this data.

The project is based on **PHP 8.1**,  **[API-Platform 2.5](https://api-platform.com/docs/v2.5/distribution/)** and **[Symfony 5.4](https://symfony.com/)**

Live API is available at [https://data.instamed.fr](https://data.instamed.fr).

Six types of data are currently available:

### RPPS Data
- The RPPS (Répertoire Partagé des Professionnels de Santé) contains all the data of French health professionals.

### Drugs Data
- Contains all the data of allowed drugs on the French market.

### Diseases Data
- Contains all data from the OMS CIM-10 database.

### Allergens Data
- Contains all known allergens.

### CCAM Data
- Contains all the medical acts and their reimbursement rates by the social security.

### NGAP Data
- Contains a database of medical acts.

## Installation

### Docker Setup

**Prerequisites:**

- Have docker installed
- Have docker-compose installed

**Installation:**

1. Clone the repository:

   ```bash
   git clone git@github.com:instamedsolutions/rpps_api.git
   ```
   
2. Navigate to the project directory:

   ```bash
   cd rpps_api
   ```
   
3. Create an empty SQLite file that will be used by Docker:

   ```bash
   touch test_db.sqlite
   ```

4. Start the Instamed project to ensure the instamed network is up. 

5. Start the Docker services for this project:

   ```bash
   docker-compose up -d
   ```

6. Access the shell in the rpps-database container and install Composer dependencies:

   ```bash
   make shell
   composer install
   ```

**Setup with test data:**

Duration: ~10/15 minutes

```
$ make setup-dev
```

## Development
To run the docker environment you can start the docker server with the following command :
```shell
docker-compose up
````

Then here are some useful commands
````shell
# Starts a bash session in the container
make shell

# Install a composer package
make composer-require package='name/of/your/package'

# Runs the doctrine migrations
make migrate:

# Creates a new doctrine migration
make create-migration:
````


## Tests
All code is tested using [phpunit](https://phpunit.de/)
All test files are in the *tests/* folder in 3 sub folders :
* **Unit** : Contains all unit tests of the project
* **Integration** : Contains all the integration tests of the project
* **Functional** : Contains all the functional tests of the project

To run the tests, run the command
````shell
make phpunit
````
