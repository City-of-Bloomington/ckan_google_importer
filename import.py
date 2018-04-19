import errno
import os
import pycurl
import yaml

download = pycurl.Curl();
upload   = pycurl.Curl();
config   = open('./config.yml', 'r')
docs     = yaml.load_all(config)

download.setopt(pycurl.FOLLOWLOCATION, True)

for doc in docs:
    tmp_dir  = doc['tmp_dir' ]
    log_file = doc['log_file']
    ckan_url = doc['ckan']['url'    ] + '/api/3/action/resource_update'
    api_key  = doc['ckan']['api_key']

    upload.setopt(pycurl.URL, ckan_url)
    upload.setopt(pycurl.HTTPHEADER, ['Authorization: ' + api_key])

    try:
        os.makedirs(tmp_dir, 0775)
    except OSError as e:
        if e.errno != errno.EEXIST: raise

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
