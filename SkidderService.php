<?php
/**
 * @copyright 2019 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

class SkidderService
{
    public static function log(string $skidder_url, int $application_id, array $entry)
    {

        $skidder = curl_init($skidder_url.'/index');
        $post    = [
            'application_id' => $application_id,
            'script'         => $entry['script' ],
            'type'           => $entry['type'   ],
            'message'        => $entry['message']
        ];
        curl_setopt_array($skidder, [
            CURLOPT_POST           => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $post
        ]);
        curl_exec($skidder);
    }
}
