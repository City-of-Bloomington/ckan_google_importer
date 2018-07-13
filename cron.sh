#!/bin/bash
APPLICATION_NAME=ckan_google_importer
APPLICATION_HOME=/srv/sites/$APPLICATION_NAME
SITE_HOME=/srv/data/$APPLICATION_NAME
LOG=/var/log/cron/$APPLICATION_NAME

python3 $APPLICATION_HOME/import.py $SITE_HOME/config.yml &>$LOG
