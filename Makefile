## === Development === commands - meant to be used to run the project locally while developping on it

build: ## Builds the app
	docker-compose build

composer-install: ## Runs composer install in the container to install backend dependencies
	docker-compose run --rm apache composer install

update-schema: ## Updates the SQL schema
	docker-compose run --rm apache php bin/console doctrine:schema:update --force

load-fixtures: ## Loads fixtures into the database
	docker-compose run --rm apache php bin/console doctrine:fixtures:load --no-interaction

sync-metadata-storage: ## Ensures that the metadata storage is at the latest version.
	docker-compose run --rm apache php bin/console doctrine:migrations:sync-metadata-storage

migrate: ## Sync the migrations
	docker-compose run --rm apache php bin/console doctrine:migrations:migrate --no-interaction

migrate-sync: ## Runs the migrations
	docker-compose run --rm apache php bin/console doctrine:migrations:version --add --all

import-allergens: # Import allergets' data
	docker-compose run --rm apache php bin/console app:allergen:import

import-ccam: # Import CCAM' data
	docker-compose run --rm apache php bin/console app:ccam:import

import-diseases: # Import diseases' data
	docker-compose run --rm apache php bin/console app:disease:import

import-drugs: # Import drugs' data
	docker-compose run --rm apache php bin/console app:drugs:import

import-rpps: # Import rpps' data
	docker-compose run --rm apache php bin/console app:rpps:import

import-test-data: # Import test data
	docker-compose run --rm apache php bin/console app:test:create

import-data: import-allergens import-ccam import-diseases import-drugs import-rpps # Import all data but test data

install-app: build composer-install setup-db ## Installs the application without importing the data

setup-db: update-schema load-fixtures sync-metadata-storage migrate-sync ## Installs the application without importing the data

install: install-app import-data ## Installs the application & imports the data

shell: ## Gets a shell in the apache container
	docker-compose run --rm apache bash

run: ## Runs all application's containers
	docker-compose up

routes-dev: ## Lists all routes of the application
	docker-compose run --rm apache php bin/console debug:router

stop: ## Stops all containers
	docker-compose down -v

help: ## Displays the current help
	@$(call say_yellow,"Usage:")
	@$(call say,"  make [command]")
	@$(call say,"")
	@$(call say_yellow,"Available commands:")
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%s\033[0m___%s\n", $$1, $$2}' | column -ts___
