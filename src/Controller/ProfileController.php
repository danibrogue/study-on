<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Exception\BillingUnavailableException;
use App\Service\BillingClient;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private $serializer;
    private $billingClient;

    public function __construct(
        BillingClient $billingClient,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->billingClient = $billingClient;
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function index(
        Request $request
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_course_index');
        }
        try {

            $userDTO = $this->billingClient->getCurrentUser($this->getUser());
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }

//        $userDTO = $this->serializer->deserialize($response, UserDTO::class, 'json');
        return $this->render('profile/index.html.twig', [
            'userDTO' => $userDTO
        ]);
    }
}
