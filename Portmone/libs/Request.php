<?php

namespace payment\PaymentSystem\Portmone\libs;

use payment\PaymentSystem\Portmone\exceptions\PortmoneException;

class Request
{
    public $responseCode;
    public $responseBody;
    
    public function post($url, $data)
    {
        // select request way
        if (extension_loaded('curl') && function_exists('curl_version')) {
            $this->curlRequest($url, $data);
        } elseif (extension_loaded('openssl') && ini_get('allow_url_fopen')) {
            $this->phpPostRequest($url, $data);
        } else {
            throw new PortmoneException('Environmental requirements fail', PortmoneException::CONFIGURATION_ERROR);
        }
        // check http code and response content (It should be XML)
        if (200 == $this->responseCode) {
            if ($data = PortmoneHelper::parseXml($this->responseBody)) {
                return $data;
            } else {
                throw new PortmoneException('HTTP request fail', PortmoneException::REQUEST_ERROR);
            }
        } else {
            throw new PortmoneException(
                'HTTP request fail, response code: ' . $this->responseCode,
                PortmoneException::REQUEST_ERROR
            );
        }
    }

    protected function curlRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $this->responseBody = $response = curl_exec($ch);
        $this->responseCode = $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (200 !== intval($httpCode)) {
            return false;
        }
        return $response;
    }

    protected function phpPostRequest($url, $data)
    {
        $result = file_get_contents(
            $url,
            false,
            stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data),
                    'ignore_errors' => true // to suppress a warnings
                ],
            ])
        );
        $this->responseBody = $result;
        $this->responseCode = (int)substr($http_response_header[0], 9, 3); // e.g. 403 from "HTTP/1.1 403 Forbidden"
        if (200 !== intval($this->responseCode)) {
            return false;
        }
        return $result;
    }
}