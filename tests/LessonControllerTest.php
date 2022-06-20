<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Tests\AbstractTest;

class LessonControllerTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
    
    public function testResponsePages(): void
    {
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

    public function testCreateLesson(): void
    {

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

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        self::assertFalse($client->getResponse()->isRedirect('/courses/' . $course->getId()));

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => '',
            'lesson[number]' => 1000 - 7,
        ]);

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        self::assertFalse($client->getResponse()->isRedirect('/courses/' . $course->getId()));

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => '',
        ]);

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        self::assertFalse($client->getResponse()->isRedirect('/courses/' . $course->getId()));
    }

    public function testCreateLessonWithInvalids(): void
    {
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

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        self::assertFalse($client->getResponse()->isRedirect('/courses/' . $course->getId()));

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[title]' => 'Тестовый урок',
            'lesson[contents]' => 'Это тестовый урок',
            'lesson[number]' => -1,
        ]);

        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        self::assertFalse($client->getResponse()->isRedirect('/courses/' . $course->getId()));
    }


    public function testDeleteLesson(): void
    {
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

        $client->submitForm('lesson-delete');

        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $actualLessonsCount = count($course->getIncludes());
        self::assertCount($actualLessonsCount, $crawler->filter('.list_node'));
    }

    public function testEditLesson(): void
    {
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
        $course = self::getEntityManager()->getRepository(Course::class)
            ->FindOneBy(['id' => $form['lesson[course]']->getValue()]);

        $form['lesson[title]'] = 'Измененный урок';
        $form['lesson[contents]'] = 'Это измененный урок';
        $form['lesson[number]'] = 1000 - 7;
        $client->submit($form);

        $lesson = self::getEntityManager()->getRepository(Lesson::class)
            ->FindOneBy(['title' => $form['lesson[title]']->getValue()]);

        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $lessonTitle = $crawler->filter('.lesson-title')->text();
        self::assertEquals('Измененный урок', $lessonTitle);

        $lessonContents = $crawler->filter(".lesson-contents")->text();
        self::assertEquals('Это измененный урок', $lessonContents);
    }

    public function testDeleteOrphanedLessons(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $orphanedLessons = $crawler->filter('.lesson-link')->link();

        $client->submitForm('course-delete');
        self::assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        foreach ($orphanedLessons as $orphanedLesson) {
            $client->request('GET', $orphanedLesson);
            $this->assertResponseNotFound();
        }
    }
}
