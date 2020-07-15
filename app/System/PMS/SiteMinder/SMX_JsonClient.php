<?php

namespace App\System\PMS\SiteMinder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\SeekException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;

/**
 * Description of SMX_JsonClient
 *
 * @author mmammar
 */
class SMX_JsonClient {

    /**
     * @var Client
     */
    private $guzzle;
    private $baseUrl = 'https://smx-pilot-subscriberx.dev.siteminderlabs.com/inventory/v1/';
    private $authorizationBearer = 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1ODY4OTEzMTYsImp0aSI6IjVmMGMxODA1LTg3YzctNDM0OS05NGRhLTM1ZWRjZTQ5OWQ1MCIsImF1ZCI6InN1YnNjcmliZXJ4L2FwaSIsInN1YiI6ImNoYXJnZV9hdXRvbWF0aW9uIn0.Kj3taHVF3pTThzYaiqWtDjuVmS5ZzpM09FFXzNQKp1R5EnZ-8UTATyDnZvVOd1Ul4fjimu14tMhFW0K6iQ7uyJavBlwvqHv1kaXE5o3-z3daH_QAs6QROEUQMCQG6eePgjGnaT9plC6l0vLOmotqqhlh0rUpXqcYqZgX8oseZC2YyjaMCKxCYksdRStxiyUGnhj8G9-Kw6-yp8c_zZIzcsfw1SY6NCNj5fH6Ya1tincfEDRqkHeXAf7rdfosfsk0Mq-ayC0BMhAhm2EDYRzO5ScRbshIJBXBzusE511_6dfNqpQCQXmFqFEnL71Ee9WfdAZ3F_6IRDlVwkt8l78lYJ98SSbeX0ISCuZwZiqx0z1BmpiPftfjFXPCLUqXMzik3mCUj6JnOLNxQibiyaE_yIxEVVBLS7ODBg6C2Rs48_ZQWEkoVGEp-WWut5soYB-eF1-pkRfeaoV_grPicR94krhkX6HDuE_kvfq_cFHEJjYsRFXwWIroJzFHd-Pj7L_OOCNHvU10ZUjt5U7Lyo3Qxz-dYRO3shWYZI6bAjPBp7uaxZLRwdqJ71OFUNAhwDUDU1GLUX1ME43hw0VCs0bYLmmsUA1Po5HSwbWn8g4La7J_4iyX9uOlsDzIUzakhp3Cw213tjO0Jcl7nUCeKvZ1DvYuvoC9MyixkYk8tG-Xphk';
    
    public function __construct(Client $guzzle) {
        
        $this->guzzle = $guzzle;
    }

    public function makeJsonRequest(string $api) {
        
        $url = $this->baseUrl . $api;
        
        $options = [];
        $options['headers']['Authorization'] = $this->authorizationBearer;
        $options['headers']['Content-type'] = 'application/json';

        $response = $this->guzzle->get($url, $options);
        $content = $response->getBody();
        
//        header('Content-type: application/json');
//        echo (string) $content->getContents();
//        die();
        
        return (string) $content->getContents();
        
    }
    
}
