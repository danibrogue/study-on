<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;

class BillingCourses
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function findCourses()
    {
        $qm = new QueryManager(
            '/api/v1/courses/',
            'GET',
            [
                'Content-Type: application/json',
            ]
        );
        return json_decode($qm->exec(), true, 512, JSON_THROW_ON_ERROR);
    }
}