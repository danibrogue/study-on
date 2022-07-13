<?php

namespace App\DTO;

use App\DTO\UserDTO;
use App\Security\AppUser;

class DTOConvertor
{
    public function fromDTO(userDTO $userDTO): AppUser
    {
        $user = new AppUser();
        $user->setApiToken($userDTO->getToken());
        $decodedToken = $this->JWTDecode($userDTO->getToken());
        $user->setRoles($decodedToken['roles']);
        $user->setEmail($decodedToken['email']);

        return $user;
    }

    private function JWTDecode($token): array
    {
        $partedToken = explode('.', $token);
        $payload = json_decode(base64_decode($partedToken[1]), true);
        return [
            'email' => $payload['email'],
            'roles' => $payload['roles']
        ];
    }
}