#!/bin/bash

login_group="$1"
login_group="${page// /$'_'}"

login_group_plural="$2"
login_group_singular="$3"


login_zone_type="$4"

echo "Creating folder ${login_group}..."

mkdir ../application/views/$login_group

if [ $login_zone_type == "ecms" ];then

echo "ecms"

else

echo "custom"

fi
