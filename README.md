# Welcome to thee Medical Database & API

## Description

This project contains all scripts to fetch and store data as well as a fully functioning API to fetch this data.

The project is based on **PHP 8.1**,  **[API-Platform 2.5](https://api-platform.com/docs/v2.5/distribution/)** and **[Symfony 5.4](https://symfony.com/)**

Live API is available at https://data.instamed.fr

5 type of data are currently available :

##### RPPS Data
- The RPPS (Répertoire Partagé des Professionnels de Santé) contains all the data of French health professionals

##### Drugs data
- The drugs data contains all the data of allowed drugs on the French Market

##### Diseases data
- The diseases data contains all the data from the OMS CIM-10 database

##### Allergens data
- The allergens data contains all the alergens that are known

##### CCAM data
- The CCAM data contains all the medical acts and their reimbursment rate by the social security

##### NGAP data
- The NGAP data contains a database of medical acts

## Installation

### Docker Setup

**Prerequisites:**

- Have docker installed
- Have docker-compose installed

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
