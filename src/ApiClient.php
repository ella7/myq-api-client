<?php

namespace MyQ;

use GuzzleHttp\Client;

/**
 * API Client to interact with MyQ controlled devices
 * 
 * @author Ryan Packer
 */
class ApiClient
{
    const BASE_URI              = 'https://www.myliftmaster.com';
    const AUTH_PATH             = '';
    const DEVICES_PATH          = '/api/MyQDevices/GetAllDevices';
    const SINGLE_DEVICE_PATH    = '/api/MyQDevices/LoadSingleDevice';
    const TRIGGER_PATH          = '/Device/TriggerStateChange';
    const USERNAME_KEY          = 'Email';
    const PASSWORD_KEY          = 'Password';
    const AUTH_COOKIE_NAME      = '.AspNet.ApplicationCookie';
    const DOOR_STATE_OPEN       = 1;
    const DOOR_STATE_CLOSED     = 0;

    private $config;
    
    private $authenticated;
    
    private $client;
    
    public function __construct($config = [])
    {
        // if not an array throw an ERROR
        if(!is_array($config)){
            throw new \Exception('Parameter $config in MyQ\ApiClient __construct must be an array. '.gettype($config).' provided');
        }
        // merge with defaults
        
        $this->config = $config;
        
        $this->client = new Client([
            'base_uri' => self::BASE_URI,
            'cookies'  => true
        ]);
    }
    
    public function authenticate($force = false)
    {
        // don't reauthenticate unless you need to
        if(!$this->authenticated || $force == true){
            $this->authenticated = false;
            $response = $this->client->request('POST', '', [
                'headers'  => self::headersForAuthenticateRequest(),
                'form_params' => [
                    self::USERNAME_KEY => $this->config['username'],
                    self::PASSWORD_KEY => $this->config['password']
                ]
            ]);
            foreach($this->client->getConfig('cookies') as $cookie){
                if($cookie->getName() == self::AUTH_COOKIE_NAME){
                    $this->authenticated = true;
                    return $this;
                }
            }
            throw new \Exception('Authentication failed. Unknown cause');
        }
        return $this;
    }
    
    public function getAllDevices()
    {
        $this->authenticate(); 
        $response = $this->client->request('GET', self::DEVICES_PATH, [
            'headers'  => self::headersForApiRequests(),
            'query'   => [
                'brandName'         => 'Liftmaster'
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }
    
    public function loadSingleDevice($device_id)
    {
        $this->authenticate(); 
        $response = $this->client->request('POST', self::SINGLE_DEVICE_PATH, [
            'headers'  => self::headersForApiRequests(),
            'query'   => [
                'myQDeviceId'       => $device_id,
                'brandName'         => 'Liftmaster'
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }
    
    public function openGarageDoor($device_id)
    {
        // validate the $device_id
        
        // open the garage door
        $this->triggerStateChange($device_id, 'desireddoorstate', self::DOOR_STATE_OPEN);
        
        // check to see if the door is opening
    }
    
    public function closeGarageDoor($device_id)
    {
        // validate the $device_id
        
        // open the garage door
        $this->triggerStateChange($device_id, 'desireddoorstate', self::DOOR_STATE_CLOSED);
        
        // check to see if the door is opening
    }
    
    private function triggerStateChange($device_id, $attribute_name, $attribute_value)
    {
        $this->authenticate();   
        $response = $this->client->request('POST', self::TRIGGER_PATH, [
            'headers'       => self::headersForApiRequests(),
            'form_params'   => [
                'myQDeviceId'       => $device_id,
                'attributename'     => $attribute_name,
                'attributevalue'    => $attribute_value    
            ]
        ]);
    }
    
    private function headersForAuthenticateRequest()
    {
        return [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'en-US,en;q=0.8',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Origin' => 'https://www.myliftmaster.com',
            'Referer' => 'https://www.myliftmaster.com/',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36'
        ];
    }
    
    private function headersForApiRequests()
    {
        return [
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding' => 'gzip, deflate, sdch, br',
            'Accept-Language' => 'en-US,en;q=0.8',
            'Connection' => 'keep-alive',
            'Referer' => 'https://www.myliftmaster.com/Dashboard',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-TS-AJAX-Request' => 'true'
        ];
    }
    
    
}