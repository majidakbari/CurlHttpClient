<?php
namespace makbari\httpClient\client;

use makbari\httpClient\exception\ClientException;
use makbari\httpClient\interfaces\iHttpClient;
use mhndev\phpStd\Str;


/**
 * Class HttpCurlClient
 * @package makbari\httpClient\client
 */
class HttpCurlClient implements iHttpClient
{

    /**
     * @param string $method
     * @param string $uri
     * @param string $body
     * @param array $headers
     * @param array $queryParams
     * @return array
     */
    public function sendRequest(
        string $method = 'GET',
        string $uri,
        string $body = '',
        array $headers = [],
        array $queryParams = []
    )
    {

        if (!empty($queryParams)){
            $uri = $this->setQueryParams($uri, $queryParams);
        }
        $ch = curl_init($uri);
        $ch = $this->setCurlMethod($method, $ch);
        $ch = $this->setRequestBodyAndHeaders($ch, [
            'body' => $body, 'headers' => $headers
        ]);
        return $this->request($ch);

    }

    /**
     * @param string $method
     * @param $curl
     * @return $curl
     */
    private function setCurlMethod(string $method, $curl)
    {
        switch ($method) {
            case 'GET':
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);

                break;
            case 'PATCH':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        return $curl;
    }

    /**
     * @param $curl
     * @param array $options
     * @return $curl
     */
    private function setRequestBodyAndHeaders($curl, array $options = [])
    {
        if (array_key_exists('body', $options) && !empty($options['body'])){

            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['body']);
        }
        if (is_array($options['headers']) && !empty($options['headers']) ){
            $headers = [];
            foreach ($options['headers'] as $key => $value){
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        return $curl;
    }

    /**
     * @param $curl
     * @return  array
     */
    private function request($curl)
    {
        $server_output = curl_exec($curl);
        $this->checkIfResponseIsSuccess($curl);
        curl_close($curl);

        return json_decode($server_output, true);
    }

    /**
     * @param array $queries
     * @param string $uri
     * @return string
     */
    private function setQueryParams($uri, array $queries = [])
    {
        if (!empty($queries)){
            foreach ($queries as $key => $value){
                if (is_string($value)){
                    if (str::contains($uri, '?')){
                        $uri .= '&'. $key . '='. $value;
                    }
                    else{
                        $uri .= '?'. $key . '='. $value;
                    }
                }
                if (is_array($value)){
                   foreach ($value as $v){
                       if (str::contains($uri, '?')){
                           $uri .= '&'. $key . '[]='. $v;
                       }
                       else{
                           $uri .= '?'. $key . '[]='. $v;
                       }
                   }
                }
            }
        }

        return $uri;
    }


    private function checkIfResponseIsSuccess($curl)
    {
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode){
            if ($httpCode >= 300){
                throw new ClientException();
            }
        }

    }

}
