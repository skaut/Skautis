#!/bin/bash

if [ ! -f ./vendor/bin/php-cs-fixer ]; then 
    composer update --dev; 
fi


fixers="psr1,psr2,object_operator,operators_spaces,remove_leading_slash_use,spaces_before_semicolon,standardize_not_equal,ternary_spaces,unused_use,whitespacy_lines,concat_with_spaces,short_array_syntax"


#Fixni pouze soubory urcene ke commitu
for file in $(git diff --name-only --cached); do
    ./vendor/bin/php-cs-fixer fix "$file" --fixers="$fixers";
done
