<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class UserDTO
{

    /**
     * @Serializer\Type("string")
     */
    private $username;


    /**
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @Serializer\Type("array")
     */
    private $roles;

    /**
     * @Serializer\Type("float")
     */
    private $balance;

    /**
     * @Serializer\Type("string")
     */
    private $token;

    /**
     * @Serializer\Type("string")
     */
    private $refresh_token;


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }


    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }


    public function getBalance(): ?float
    {
        return $this->balance;
    }


    public function setBalance(?float $balance): void
    {
        $this->roles = $balance;
    }


    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getrefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function setrefreshToken(?string $refresh_token): void
    {
        $this->refresh_token = $refresh_token;
    }
}
