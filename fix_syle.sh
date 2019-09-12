#!/bin/bash

if [ ! -f ./vendor/bin/php-cs-fixer ]; then 
    composer update --dev; 
fi

./vendor/bin/phpstan analyze  -c phpstan.neon --level 7 --no-progress  src

#Fixni pouze nove a upravene soubory
for file in $(git diff --name-only); do
    ./vendor/bin/php-cs-fixer fix "$file" --rules=psr4 --allow-risky=yes;
done
