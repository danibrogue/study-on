<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;

class BillingTransactions
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getTransactions($filters, $token)
    {
        $qm = new QueryManager(
            '/api/v1/transactions/',
            'GET',
            [
                'Content-Type: application/json',
                'token: ' . $token
            ],
            $filters
        );
        $result = $qm->exec();
        return json_decode($result, true, 512, JSON_THROW_ON_ERROR);

    }
}