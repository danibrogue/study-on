<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Security\AppUser;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use App\Exception\BillingUnavailableException;
use App\Service\QueryManager;
use App\DTO\DTOConvertor;

class BillingClient
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function userAuth($credentials): ?AppUser
    {
        $qm = new QueryManager(
            '/api/v1/auth',
            'POST',
            [
                'Content-Type: application/json',
            ],
            $credentials
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (!(isset($arrayResponse['token']))) {
            return null;
        }
        if (!(isset($arrayResponse['code']))) {
            $userDTO = $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
            return (new DTOConvertor())->fromDTO($userDTO);
        }
        if ($arrayResponse['code'] === Response::HTTP_UNAUTHORIZED) {
            throw new UserNotFoundException('Неверные учетные данные');
        }
    }

    public function userRegister($credentialsObject): AppUser
    {
        $credentials = $this->serializer->serialize($credentialsObject, 'json');
        $qm = new QueryManager(
            '/api/v1/register',
            'POST',
            [
                'Content-Type: application/json',
            ],
            $credentials
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (isset($arrayResponse['error'])) {
            throw new BillingUnavailableException($arrayResponse['error']);
        }
        $userDTO = $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
        return (new DTOConvertor())->fromDTO($userDTO);
    }

    public function getCurrentUser(AppUser $user)
    {
        $qm = new QueryManager(
            '/api/v1/current',
            'GET',
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $user->getApiToken()
            ]
        );
        $jsonResponse = $qm->exec();
        $arrayResponse = json_decode($jsonResponse, true);
        if (isset($arrayResponse['error'])) {
            throw new BillingUnavailableException($arrayResponse['error']);
        }
        return $this->serializer->deserialize($jsonResponse, UserDTO::class, 'json');
    }
}
