include Makefile.import
include Makefile.database
include Makefile.dev
include Makefile.test

run: ## Runs all application's containers
	docker-compose up

destroy: ## Stops all containers
	docker-compose down -v

stop: ## Stops all containers
	docker-compose stop

help: ## Displays the current help
	@$(call say_yellow,"Usage:")
	@$(call say,"  make [command]")
	@$(call say,"")
	@$(call say_yellow,"Available commands:")
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%s\033[0m___%s\n", $$1, $$2}' | column -ts___
