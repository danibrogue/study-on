<?php

namespace App\Tests\Mock;

use App\DTO\DTOConvertor;
use App\Exception\BillingUnavailableException;
use App\Service\BillingClient;
use App\DTO\UserDTO;
use App\Security\AppUser;
use JMS\Serializer\SerializerInterface;

class BillingClientMock extends BillingClient
{
    private $serializer;
    private $existing;

    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->serializer = $serializer;
        $this->existing = [
            [
                'username' => 'user@test.local',
                'password' => '12345678'
            ],
            [
                'username' => 'admin@test.local',
                'password' => '12345678'
            ]
        ];
    }

    private function generateToken($roles, $username): string
    {
        $data = json_encode([
            'email' => $username,
            'roles' => $roles,
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp()
        ], JSON_THROW_ON_ERROR);
        $query = base64_encode($data);
        return 'header. ' . $query . '.signature';
    }

    private function generateRequest($credentials, $roles)
    {
        return json_encode([
            'token' => $this->generateToken($roles, $credentials['username']),
            'username' => $credentials['username'],
            'roles' => $roles
        ], JSON_THROW_ON_ERROR);
    }

    public function userAuth($credentials): ?AppUser
    {
        $data = json_decode($credentials, true);
        if (!in_array($data, $this->existing, true)) {
            return null;
        }
        if ($data === $this->existing[0]) {
            $request = $this->generateRequest($data, ['ROLE_USER']);
        } else {
            $request = $this->generateRequest($data, ['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        }
        $userDTO = $this->serializer->deserialize($request, UserDTO::class, 'json');
        return (new DTOConvertor())->fromDTO($userDTO);
    }

    public function userRegister($credentialsObject): AppUser
    {
        $credentials = [
            'username' => $credentialsObject->username,
            'password' => $credentialsObject->password
        ];
        if (in_array($credentials, $this->existing, false)) {
            throw new BillingUnavailableException('Данный пользователь уже существует');
        }
        $response = json_encode([
            'token' => $this->generateToken(['ROLE_USER'], $credentials['username'])
        ], JSON_THROW_ON_ERROR);
        $userDTO = $this->serializer->deserialize($response, UserDTO::class, 'json');
        return (new DTOConvertor())->fromDTO($userDTO);
    }
}
