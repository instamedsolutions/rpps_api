symfony server:ca:install
composer install
sudo chmod -R 777 ./var
rm -rf var/db.sqlite
sudo touch var/test_db.sqlite
sudo chmod 777 var/test.sqlite
php bin/console doctrine:schema:update --force --env=test
php bin/console doctrine:fixtures:load --env=test
php bin/console cache:clear --env=test
