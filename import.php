<?php
/**
 * @copyright 2019 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
include './SkidderService.php';

$opts = getopt('', ['help', 'config:']);
if (isset($opts['help'])) {
    echo <<<EOT
DESCRIPTION
    This script downloads Google Spreadsheets as CSV files, then uploads
    them to resources in a CKAN site.

OPTIONS
    Generic Program Information
        --help   Output a usage message and exit
        --config Path to the configuration file

EOT;
exit();
}

if (     isset($opts['config'])
    &&  !empty($opts['config'])
    && is_file($opts['config'])) {

    $config = json_decode(file_get_contents($opts['config']));
}

if (!isset($config) || !$config) {
    echo "import: you must specify the path to a valid json config file\n";
    exit(1);
}

if (!is_dir($config->tmp_dir)) {
    echo "import: you must configure a tmp directory to store downloaded files\n";
    exit(1);
}

$tmp_dir  = $config->tmp_dir;
$ckan_url = $config->ckan->url . '/api/3/action/resource_update';
$api_key  = $config->ckan->api_key;

$download = curl_init();
$upload   = curl_init();
curl_setopt($download, CURLOPT_FOLLOWLOCATION, true);
curl_setopt_array($upload, [
    CURLOPT_URL            => $ckan_url,
    CURLOPT_POST           => true,
    CURLOPT_HEADER         => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ["Authorization: $api_key"]
]);

foreach ($config->spreadsheets as $s) {
    $name           = $s->name;
    $resource_id    = $s->resource_id;
    $spreadsheet_id = $s->spreadsheet_id;
    $sheet_id       = $s->sheet_id;
    $google_url     = "https://docs.google.com/spreadsheets/d/$spreadsheet_id/export?format=csv&gid=$sheet_id";
    $csv_file       = "$tmp_dir/$name.csv";

    $fp = fopen($csv_file, 'w');
    curl_setopt_array($download, [CURLOPT_URL  => $google_url,
                                  CURLOPT_FILE => $fp        ]);
    $success = curl_exec($download);
    if (!$success) {
        SkidderService::log($config->skidder->url, $config->skidder->application_id, [
            'script'  => $google_url,
            'type'    => 'Curl error',
            'message' => curl_error($download)
        ]);
    }
    fclose($fp);

    curl_setopt($upload, CURLOPT_POSTFIELDS, [
        'id'     => $s->resource_id,
        'upload' => new \CURLFile($csv_file, 'text/csv', 'data.csv')
    ]);
    $response = curl_exec($upload);
    if (!$response) {
        SkidderService::log($config->skidder->url, $config->skidder->application_id, [
            'script'  => $ckan_url,
            'type'    => 'Curl error',
            'message' => "$csv_file\n{$s->resource_id}\n".curl_error($upload)
        ]);
    }
    else {
        $json = json_decode($response);
        if (!$json) {
            SkidderService::log($config->skidder->url, $config->skidder->application_id, [
                'script'  => $ckan_url,
                'type'    => 'CKAN invalid response',
                'message' => "$csv_file\n{$s->resource_id}\n$response"
            ]);
        }

        if (!$json->success) {
            SkidderService::log($config->skidder->url, $config->skidder->application_id, [
                'script'  => $ckan_url,
                'type'    => 'CKAN error: '.$json->error->__type,
                'message' => "$csv_file\n{$s->resource_id}\n".$json->error->message
            ]);
        }
    }
}
curl_close($download);
curl_close($upload);
