<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class CredentialsDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $username;


    /**
     * @Serializer\Type("string")
     */
    public $password;
}