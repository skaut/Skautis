#!/bin/bash

if [ ! -f ./vendor/bin/php-cs-fixer ]; then 
    composer update --dev; 
fi


fixers="object_operator,operators_spaces,remove_leading_slash_use,spaces_before_semicolon,standardize_not_equal,ternary_spaces,unused_use,whitespacy_lines,concat_with_spaces,short_array_syntax"


#Fixni pouze nove a upravene soubory
for file in $(git diff --name-only); do
    ./vendor/bin/php-cs-fixer fix "$file" --level=psr2 --fixers="$fixers";
done
