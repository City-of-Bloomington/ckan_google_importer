# @copyright 2018 City of Bloomington, Indiana
# @license http://www.gnu.org/licenses/gpl.txt GNU/GPL, see LICENSE.txt
#
# This file is part of ckan_google_importer.
#
# ckan_google_importer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# ckan_google_importer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with ckan_google_importer.  If not, see <https://www.gnu.org/licenses/>.
import argparse
import errno
import os
import pycurl
import sys
import yaml

parser = argparse.ArgumentParser()
parser.add_argument("configfile", help="path to configuration YML file")
args = parser.parse_args()

if len(sys.argv)==1:
    parser.print_help(sys.stderr)
    sys.exit(1)

with open(args.configfile) as stream:
    try:
        doc = yaml.safe_load(stream)
    except yaml.YAMLError as exc:
        print(exc)
        sys.exit(1)

tmp_dir  = doc['tmp_dir' ]
log_file = doc['log_file']
ckan_url = doc['ckan']['url'    ] + '/api/3/action/resource_update'
api_key  = doc['ckan']['api_key']

if not os.path.isdir(tmp_dir):
    print(tmp_dir + ' does not exist')
    sys.exit(1)

download = pycurl.Curl();
upload   = pycurl.Curl();
download.setopt(pycurl.FOLLOWLOCATION, True)
upload.setopt(pycurl.URL, ckan_url)
upload.setopt(pycurl.HTTPHEADER, ['Authorization: ' + api_key])

for s in doc['spreadsheets']:
    resource_id    = s['resource_id'   ]
    spreadsheet_id = s['spreadsheet_id']
    sheet_id       = s['sheet_id'      ]
    google_url     = 'https://docs.google.com/spreadsheets/d/' + spreadsheet_id + '/export?format=csv&gid=' + sheet_id
    csv_file       = tmp_dir + '/' + resource_id + '.csv'

    with open(csv_file, 'wb') as f:
        download.setopt(pycurl.URL,       google_url)
        download.setopt(pycurl.WRITEDATA, f)
        download.perform()

        upload.setopt(pycurl.HTTPPOST, [
            ('id',     resource_id),
            ('upload', (
                pycurl.FORM_FILE,        csv_file,
                pycurl.FORM_CONTENTTYPE, 'text/csv'
            ))
        ])
        upload.perform()

download.close()
upload.close()
