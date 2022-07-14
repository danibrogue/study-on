<?php

namespace App\Tests\Authorization;

use App\Service\BillingClient;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Panther\PantherTestCase;

class Auth extends AbstractTest
{
    private $serializer;

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getBillingClient(): void
    {
        self::getClient()->disableReboot();
        self::getClient()->getContainer()->set(
            BillingClient::class,
            new BillingClientMock($this->serializer)
        );
    }

    public function testAuth(string $data)
    {
        $request = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $this->getBillingClient();
        $client = self::getClient();

        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
        $form->setValues([
            'email' => $request['username'],
            'password' => $request['password']
        ]);
        $crawler = $client->submit($form);
        $errors = $crawler->filter('#errors');
        self::assertCount(0, $errors);
        self::assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        self::assertEquals('/', $client->getRequest()->getPathInfo());
        return $crawler;
    }
}
