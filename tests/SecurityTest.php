<?php

namespace App\Tests;

use App\Tests\Authorization\Auth;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Panther\PantherTestCase;

class SecurityTest extends AbstractTest
{
    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$container->get(SerializerInterface::class);
    }

    public function testSuccessfulAuth(): void
    {
        $authTest = new Auth();
        $authTest->setSerializer($this->serializer);
        $data = [
            'username' => 'user@test.local',
            'password' => '12345678'
        ];
        $credentials = $this->serializer->serialize($data, 'json');
        $crawler = $authTest->testAuth($credentials);
    }

    public function testErrorAuth(): void
    {
        $client = AbstractTest::getClient();
        $url = '/';

        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $authorization = $crawler->selectLink('Авторизация')->link();
        $crawler = $client->click($authorization);
        $this->assertResponseOk();


        self::assertEquals('/login', $client->getRequest()->getPathInfo());

        $data = [
            'username' => 'admin@test.local',
            'password' => '12345678'
        ];
        $requestData = $this->serializer->serialize($data, 'json');

        $auth = new Auth();
        $auth->setSerializer($this->serializer);

        $requestData = json_decode($requestData, true, 512, JSON_THROW_ON_ERROR);

        $form = $crawler->selectButton('Войти')->form();
        $form['email'] = $requestData['username'];
        $form['password'] = $requestData['password'];
        $client->submit($form);

        self::assertFalse($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();

        $error = $crawler->filter('#errors');
        self::assertEquals('Invalid credentials.', $error->text());
    }

    public function testLogout(): void
    {
        $authTest = new Auth();
        $authTest->setSerializer($this->serializer);
        $data = [
            'username' => 'user@test.local',
            'password' => '12345678'
        ];
        $credentials = $this->serializer->serialize($data, 'json');
        $crawler = $authTest->testAuth($credentials);

        $client = self::getClient();
        $logout = $crawler->selectLink('Выход')->link();
        $crawler = $client->click($logout);
        self::assertEquals('/logout', $client->getRequest()->getPathInfo());

        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        self::assertEquals('/', $client->getRequest()->getPathInfo());
    }

    public function testSuccessfulRegister(): void
    {
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        $auth->getBillingClient();

        $client = static::getClient();
        $crawler = $client->request('GET', '/register');
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
        $form['registration[username]'] = 'guest@test.local';
        $form['registration[password][password]'] = '12345678';
        $form['registration[password][password_repeat]'] = '12345678';
        $form['registration[agreeTerms]'] = true;
        $crawler = $client->submit($form);

        $errors = $crawler->filter('span.form-error-message');
        self::assertCount(0, $errors);


        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        self::assertEquals('/', $client->getRequest()->getPathInfo());
    }

    public function testInvalidLengthRegister(): void
    {
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        $auth->getBillingClient();

        $client = static::getClient();
        $crawler = $client->request('GET', '/register');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Зарегистрироваться')->form();
        $form['registration[username]'] = 'guest@test.local';
        $form['registration[password][password]'] = '1';
        $form['registration[password][password_repeat]'] = '1';
        $form['registration[agreeTerms]'] = true;
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('Пароль должен быть длиннее 6 символов', $error->text());
    }

    public function testNonMatchRegister(): void
    {
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        $auth->getBillingClient();

        $client = static::getClient();
        $crawler = $client->request('GET', '/register');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Зарегистрироваться')->form();
        $form['registration[username]'] = 'guest@test.local';
        $form['registration[password][password]'] = '12345678';
        $form['registration[password][password_repeat]'] = '1234567';
        $form['registration[agreeTerms]'] = true;
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('Пароли не совпадают', $error->text());
    }
}
