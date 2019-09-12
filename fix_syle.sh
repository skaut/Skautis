#!/bin/bash

if [ ! -f ./vendor/bin/php-cs-fixer ]; then 
    composer update --dev; 
fi


#Fixni pouze nove a upravene soubory
for file in $(git diff --name-only); do
    ./vendor/bin/php-cs-fixer fix "$file" --rules=psr4 --allow-risky=yes;
done
