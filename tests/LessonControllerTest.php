<?php

namespace App\Tests;

use App\Tests\Authorization\Auth;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Panther\PantherTestCase;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Tests\AbstractTest;

class LessonControllerTest extends AbstractTest
{
    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$container->get(SerializerInterface::class);
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    private function adminUser()
    {
        $auth = new Auth();
        $auth->setSerializer($this->serializer);
        $data = [
            'username' => 'admin@test.local',
            'password' => '12345678'
        ];
        $requestData = $this->serializer->serialize($data, 'json');
        return $auth->testAuth($requestData);
    }

    public function testResponsePages(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $lessons = $lessonRepository->FindAll();
        
        foreach ($lessons as $lesson) {
            $client->request('GET', '/lesson/' . $lesson->getId());
            $this->assertResponseOk();

            $client->request('GET', '/lesson/' . $lesson->getId() . '/edit');
            $this->assertResponseOk();

            $client->request('POST', '/lesson/' . $lesson->getId() . '/edit');
            $this->assertResponseOk();
        }
    }

    public function testHiddenPages(): void
    {
        $client = self::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $lessons = $lessonRepository->FindAll();

        foreach ($lessons as $lesson) {
            $client->request('GET', '/lesson/' . $lesson->getId());
            $this->assertResponseOk();

            $client->request('GET', '/lesson/' . $lesson->getId() . '/edit');
            $this->assertResponseCode(403);

            $client->request('POST', '/lesson/' . $lesson->getId() . '/edit');
            $this->assertResponseCode(403);
        }
    }

    public function testCreateLesson(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.add-lesson')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => 1000 - 7,
        ]);

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);
        $countLessonsBefore = count($course->getIncludes());

        $client->submit($form);
        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $crawler = $client->followRedirect();
        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);
        $countLessonsAfter = count($course->getIncludes());
        self::assertEquals($countLessonsBefore + 1, $countLessonsAfter);
    }

    public function testCreateLessonWithBlanks(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.add-lesson')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => '',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => 1000 - 7,
        ]);

        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value should not be blank.', $error->text());


        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => '',
        ]);

        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value should not be blank.', $error->text());
    }

    public function testCreateLessonWithInvalids(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.add-lesson')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqq',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => 1000 - 7,
        ]);
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value is too long. It should have 255 characters or less.', $error->text());

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => -1,
        ]);
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value should be between "1" and "10000".', $error->text());
    }


    public function testDeleteLesson(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-edit')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $button = $crawler->selectButton('Сохранить');
        $form = $button->form();
        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);
        $lessonsCountBefore = count($course->getIncludes());

        $client->submitForm('lesson-delete');

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);
        $lessonsCountAfter = count($course->getIncludes());
        self::assertEquals($lessonsCountBefore - 1, $lessonsCountAfter);
    }

    public function testEditLesson(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-edit')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form();
        $lessonBefore = self::getEntityManager()->getRepository(Lesson::class)
            ->FindOneBy(['title' => $form['lesson[title]']->getValue()]);

        $form['lesson[title]'] = 'Измененный урок';
        $form['lesson[contents]'] = 'Это измененный урок';
        $form['lesson[number]'] = 1000 - 7;
        $client->submit($form);


        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $lessonAfter = self::getEntityManager()->getRepository(Lesson::class)
            ->FindOneBy(['id' => $lessonBefore->getId()]);

        self::assertEquals('Измененный урок', $lessonAfter->getTitle());
        self::assertEquals('Это измененный урок', $lessonAfter->getContents());
        self::assertEquals(1000 - 7, $lessonAfter->getNumber());
    }

    public function testDeleteOrphanedLessons(): void
    {
        $crawler = $this->adminUser();
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['title' => $crawler->filter('.course-title')->first()->text()]);


        $client->submitForm('course-delete');
        self::assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $orphanedLessons = self::getEntityManager()->getRepository(Lesson::class)
            ->FindBy(['course' => $course]);
        self::assertEmpty($orphanedLessons);
    }

}
