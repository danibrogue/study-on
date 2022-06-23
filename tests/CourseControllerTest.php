<?php

namespace App\Tests;

use App\Form\CourseType;
use Symfony\Component\Panther\PantherTestCase;
use App\Tests\AbstractTest;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseControllerTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function urlSuccessProvider()
    {
        yield ['/'];
        yield ['courses/new'];
    }

    /**
     * @dataProvider urlSuccessProvider
     */
    public function testUrlSuccess($url): void
    {
        $client = self::getClient();
        $client->request('GET', $url);
        $this->assertResponseOk();
    }

    public function urlNotFoundProvider()
    {
        yield ['/wrongUrl'];
        yield ['courses/0'];
    }

    /**
     * @dataProvider urlNotFoundProvider
     */
    public function testUrlNotFound($url): void
    {
        $client = self::getClient();
        $client->request('GET', $url);
        $this->assertResponseNotFound();
    }

    public function urlInternalServerProvider()
    {
        yield ['courses/wrong'];
    }

    /**
     * @dataProvider urlInternalServerProvider
     */
    public function testUrlInternalServer($url): void
    {
        $client = self::getClient();
        $client->request('GET', $url);
        $this->assertResponseCode(500);
    }

    public function testResponsePages(): void
    {
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $courses = $courseRepository->FindAll();
        foreach ($courses as $course) {
            $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();

            $client->request('GET', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();

            $client->request('GET', '/courses/' . $course->getId() . '/lessons/add');
            $this->assertResponseOk();

            $client->request('POST', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();

            $client->request('POST', '/courses/' . $course->getId() . '/lessons/add');
            $this->assertResponseOk();
        }

        $client->request('GET', '/courses/new');
        $this->assertResponseOk();

        $client->request('POST', '/courses/new');
        $this->assertResponseOk();
    }

    public function testCourseLessonsCount(): void
    {
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $courses = $courseRepository->FindAll();
        self::assertNotEmpty($courses);

        foreach ($courses as $course) {
            $crawler = $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();

            $actualLessonsCount = count($course->getIncludes());
            self::assertCount($actualLessonsCount, $crawler->filter('.list_node'));
        }
    }

    public function testCoursesCount(): void
    {
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $courses = $courseRepository->FindAll();
        self::assertNotEmpty($courses);

        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $actualCoursesCount = count($courses);
        self::assertCount($actualCoursesCount, $crawler->filter('.course_list_node'));
    }

    public function testCreateCourse(): void
    {
        $coursesCountBefore = self::getEntityManager()->getRepository(Course::class)->count([]);
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.create_course')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
           'course[code]' => 'TEST',
           'course[title]' => 'Тестовый курс',
           'course[info]' => 'Это тестовый курс',
        ]);
        $client->submit($form);
        self::assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();

        $coursesCountAfter = self::getEntityManager()->getRepository(Course::class)->count([]);
        self::assertEquals($coursesCountBefore + 1, $coursesCountAfter);
    }

    public function testCreateCourseWithBlank(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.create_course')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => '',
            'course[title]' => 'Тестовый курс',
            'course[info]' => 'Это тестовый курс',
        ]);

        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value should not be blank.', $error->text());

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => 'TEST',
            'course[title]' => '',
            'course[info]' => 'Это тестовый курс',
        ]);

        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value should not be blank.', $error->text());
    }

    public function testCreateCourseWithInvalidLength(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.create_course')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();


        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqq',
            'course[title]' => 'Тестовый курс',
            'course[info]' => 'Это тестовый курс',
        ]);
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value is too long. It should have 255 characters or less.', $error->text());

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => 'TEST',
            'course[title]' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqq',
            'course[info]' => 'Это тестовый курс',
        ]);
        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value is too long. It should have 255 characters or less.', $error->text());

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => 'TEST',
            'course[title]' => 'Тестовый курс',
            'course[info]' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq
            qqqqqqqqqq',
        ]);

        $crawler = $client->submit($form);
        $error = $crawler->filter('li')->first();
        self::assertEquals('This value is too long. It should have 1000 characters or less.', $error->text());
    }

    public function testCreateNonUnique(): void
    {
        $countCoursesBefore = self::getEntityManager()->getRepository(Course::class)->count([]);
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.create_course')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        //проверка на создание курса, который уже существует
        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[code]' => 'LRAL',
            'course[title]' => 'Линейная алгебра',
            'course[info]' => "В данном курсе вы познакомитесь с основами линейной алгебры,".
                "узнаете, как она соотносится с нашей жизнью и как ее применить,".
                "а так же научитесь объяснять сложные вещи простыми словами с ее помощью.",
        ]);
        $client->submit($form);

        $countCoursesAfter = self::getEntityManager()->getRepository(Course::class)->count([]);
        self::assertEquals($countCoursesBefore, $countCoursesAfter);
    }

    public function testDeleteCourse(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $countCoursesBefore = self::getEntityManager()->getRepository(Course::class)->count([]);

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('course-delete');
        $countCoursesAfter = self::getEntityManager()->getRepository(Course::class)->count([]);

        self::assertEquals($countCoursesBefore - 1, $countCoursesAfter);
    }

    public function testEditCourse(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseOk();

        $link = $crawler->filter('.course-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.course-edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form();
        $courseBefore = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['code' => $form['course[code]']->getValue()]);

        $form['course[code]'] = 'EDITED';
        $form['course[title]'] = 'Измененный курс';
        $form['course[info]'] = 'Этот курс изменен';
        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $courseAfter = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['id' => $courseBefore->getId()]);

        self::assertEquals('EDITED', $courseAfter->getCode());
        self::assertEquals('Измененный курс', $courseAfter->getTitle());
        self::assertEquals('Этот курс изменен', $courseAfter->getInfo());
    }
}
