# Google Spreadsheets to CKAN importer #

Python script for updating CKAN dataset resources from Google Spreadsheets.  This script will download sheets as CSV files and update the CKAN resource with the CSV file.

## Install
```bash
sudo apt-get install curl libssl-dev libcurl4 libcurl4-openssl-dev
sudo pip install -r requirements.txt
```

## Usage
```bash
python import.py /path/to/config.yml
```

## Copying and License ##

This material is copyright 2016-2017 City of Bloomington, Indiana
It is open and licensed under the GNU General Public License (GPL) v3.0 whose full text may be found at: https://www.gnu.org/licenses/gpl.txt
