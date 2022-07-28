<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;

class QueryManager
{
    private array $options;
    private string $route;

    public function __construct(
        string $url,
        string $method,
        array $header,
        $parameters = null
    ) {
        $this->options = [CURLOPT_RETURNTRANSFER => true];
        $this->route = $_ENV['BILLING_URL'] . $url;
        $this->options[CURLOPT_HTTPHEADER] = $header;

        if ($method === 'POST') {
            $this->options[CURLOPT_POST] = true;
        }
        $this->setParams($parameters, $method);
    }

    private function setParams($params, $method): void
    {
        if ($params === null) {
            return;
        }
        if ($method === 'POST') {
            $this->options[CURLOPT_POSTFIELDS] = $params;
        } else {
            $this->route .= '?' . http_build_query($params);
            $this->options[CURLOPT_FOLLOWLOCATION] = true;
        }
    }

    public function exec()
    {
        $ch = curl_init($this->route);
        curl_setopt_array($ch, $this->options);
        $jsonResponse = curl_exec($ch);
        if ($jsonResponse === false) {
            throw new BillingUnavailableException('Сервис временно недоступен');
        }
        curl_close($ch);
        return $jsonResponse;
    }
}
