<?php

namespace App\Services\Locator;

use App\Repositories\Eloquent\RepLocatorRepository;
use GuzzleHttp\Client;

class RepLocatorService
{
    public function __construct(RepLocatorRepository $repLocatorRepo)
    {
        $this->repLocatorRepo = $repLocatorRepo;
    }

    public function searchZipCodes($zip, $radius)
    {
        // Gather Zip Codes in Radius //

        // https://www.zipcodeapi.com/API registered with key at jreed@controlpad.com
        // Test Zips to limit number of calls to zipcodeapi during testing
        $apiKey = env('ZIPCODE_API_KEY');
        $url = 'https://www.zipcodeapi.com/rest/' . $apiKey . '/radius.json/'
                    . $zip . '/' . $radius . '/mile';
        $zipCodes = [];

        try {
            $client = new Client(['timeout'  => 10.0, ]);
            $data = $client->get($url);
            $zipCodes = json_decode($data->getBody(), true)['zip_codes'];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            if ($statusCode === 429) {
                logger()->error('Status code 429: Zipcodeapi usage exceeded.');
            }
        }
        // Convert zip codes for easy sorting of users //
        $zipCollection = [];
        foreach ($zipCodes as $zip) {
            $zipCollection[$zip['zip_code']] = $zip['distance'];
        }
        return $zipCollection;
    }

    public function searchUsersByZipCode($zip, $radius = 50)
    {
        $settings = app('globalSettings');
        $limit = $settings->getGlobal('rep_locator_max_results', 'value');
        $this->zipCodes = $this->searchZipCodes($zip, $radius);
        $users = $this->repLocatorRepo->searchUsersByZipCode(array_keys($this->zipCodes))->toArray();
        $users = $this->mergeSortUsers($users);
        $users = array_slice($users, 0, $limit);
        foreach ($users as $user) {
            $user->domain = str_replace("%s", $user->public_id, env('REP_URL'));
        }
        return $users;
    }

    public function searchUsersByName($name)
    {
        $settings = app('globalSettings');
        $limit = $settings->getGlobal('rep_locator_max_results', 'value');
        $users = $this->repLocatorRepo->searchUsersByName($name, $limit);
        foreach ($users as $user) {
            $user->domain = str_replace("%s", $user->public_id, env('REP_URL'));
        }
        return $users;
    }

    public function getRandomUsers()
    {
        $settings = app('globalSettings');
        $limit = $settings->getGlobal('rep_locator_random_users', 'value');
        $users = $this->repLocatorRepo->getRandomUsers($limit);
        foreach ($users as $user) {
            $user->domain = str_replace("%s", $user->public_id, env('REP_URL'));
        }
        return $users;
    }


// Helper functions

    public function mergeSortUsers($users)
    {
        if (count($users)<=1) {
            return $users;
        }
        $mid = count($users) / 2;
        $left = array_slice($users, 0, $mid);
        $right = array_slice($users, $mid);
        $left = $this->mergeSortUsers($left);
        $right = $this->mergeSortUsers($right);
        return $this->merge($left, $right);
    }

    public function merge($left, $right)
    {
        $res = array();
        while (count($left) > 0 && count($right) > 0) {
            if ($this->zipCodes[substr($left[0]->zip, 0, 5)] > $this->zipCodes[substr($right[0]->zip, 0, 5)]) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[0];
                $left = array_slice($left, 1);
            }
        }
        while (count($left) > 0) {
            $res[] = $left[0];
            $left = array_slice($left, 1);
        }
        while (count($right) > 0) {
            $res[] = $right[0];
            $right = array_slice($right, 1);
        }
        foreach ($res as $user) {
            $user->distance = $this->zipCodes[substr($user->zip, 0, 5)];
            $user->domain = str_replace("%s", $user->public_id, env('REP_URL'));
        }
        return $res;
    }
}
