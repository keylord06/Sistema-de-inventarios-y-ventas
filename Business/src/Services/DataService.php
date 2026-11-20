<?php
namespace App\Services;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

final class DataService {
   private ClientInterface $client;
   public function __construct(object $config){

             $this->client = new  Client([

                  'base_uri' =>rtrim($config->api_data, '/'). '/',
                  'timeout' => $config->timeout,
                  'connect_timeout' => $config->timeout,
                  'http_errors' => false,
                  'headers' => [
                       'Accept' => 'application/json'
                  ]
             ]);
   }
    public function get(string $path, array $query =[]): ResponseInterface{

      return $this->client->request('GET', ltrim($path,'/'),
            ['query' => $query]);
    }

    public function filter(string $path, array $query = []): ResponseInterface
{
    return $this->client->request(
        'GET',
        ltrim($path, '/'),
        [
            'query' => $query
        ]
    );
}

  public function post(string $path, string $body): ResponseInterface{

      return $this->client->request('POST', ltrim($path,'/'),
            ['body' => $body,
            'headers' =>[ 'Content-Type' =>'application/json'
            ]
            ]);
    }



     public function put(string $path, string $body): ResponseInterface {

      return $this->client->request('PUT', ltrim($path,'/'),
            ['body' => $body,
            'headers' =>[
                 'Content-Type' =>'application/json'
            ]
            ]);
    }

   public function delete(String $path): ResponseInterface{

     return $this->client->request('DELETE',ltrim($path, '/'));
   }

}
