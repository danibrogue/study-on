<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Курс по линейной алгебре
        $linearAlgebraCourse = new Course();
        $linearAlgebraCourse->setCode('LRAL');
        $linearAlgebraCourse->setTitle('Линейная алгебра');
        $linearAlgebraCourse->setInfo(
            "В данном курсе вы познакомитесь с основами линейной алгебры,".
            "узнаете, как она соотносится с нашей жизнью и как ее применить,".
            "а так же научитесь объяснять сложные вещи простыми словами с ее помощью."
        );

        // Урок 1
        $lesson = new Lesson();
        $lesson->setTitle('Общая информация о курсе');
        $lesson->setContents(
            "В этом вводном уроке мы расскажем вам о том, что вас ждет, ".
            "и дадим рекомендации по прохождению курса."
        );
        $lesson->setNumber(1);
        $linearAlgebraCourse->addInclude($lesson);

        // Урок 2
        $lesson = new Lesson();
        $lesson->setTitle('Концепция линейного пространства');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, что такое линейное пространство, ".
            "что такое вектора и как они взаимодействуют друг с другом."
        );
        $lesson->setNumber(2);
        $linearAlgebraCourse->addInclude($lesson);

        // Урок 3
        $lesson = new Lesson();
        $lesson->setTitle('Линейные функции');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, что такое линейная функция, ".
            "как ее можно представить и по каким законам она функционирует."
        );
        $lesson->setNumber(3);
        $linearAlgebraCourse->addInclude($lesson);

        $manager->persist($linearAlgebraCourse);

        // Курс по структурам данных
        $discreteStructuresCourse = new Course();
        $discreteStructuresCourse->setCode('DRSR');
        $discreteStructuresCourse->setTitle('Дискретные структуры');
        $discreteStructuresCourse->setInfo(
            "В данном курсе вы познакомитесь с основами дискретных структур,".
            "узнаете, зачем они нужны, а так же как они соотносятся ".
            "как с реальными объектами, так и с программными."
        );

        // Урок 1
        $lesson = new Lesson();
        $lesson->setTitle('Общая информация о курсе');
        $lesson->setContents(
            "В этом вводном уроке мы расскажем вам о том, что вас ждет, ".
            "и дадим рекомендации по прохождению курса."
        );
        $lesson->setNumber(1);
        $discreteStructuresCourse->addInclude($lesson);

        // Урок 2
        $lesson = new Lesson();
        $lesson->setTitle('Стандартные числовые множества');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, что такое стандартные числовые множества, ".
            "как они формируются и для чего нужны."
        );
        $lesson->setNumber(2);
        $discreteStructuresCourse->addInclude($lesson);

        // Урок 3
        $lesson = new Lesson();
        $lesson->setTitle('Суммирование и произведение');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, как в рамках курса выражаются, ".
            "операции суммирования и произведения и для чего они нужны."
        );
        $lesson->setNumber(3);
        $discreteStructuresCourse->addInclude($lesson);

        $manager->persist($discreteStructuresCourse);

        // Курс по структурам данных
        $dataStructuresCourse = new Course();
        $dataStructuresCourse->setCode('DTSR');
        $dataStructuresCourse->setTitle('Структуры данных');
        $dataStructuresCourse->setInfo(
            "В данном курсе вы познакомитесь с основами структур данных,".
            "узнаете, зачем они нужны, а так же как они используются ".
            "при создании программных продуктов"
        );

        // Урок 1
        $lesson = new Lesson();
        $lesson->setTitle('Общая информация о курсе');
        $lesson->setContents(
            "В этом вводном уроке мы расскажем вам о том, что вас ждет, ".
            "и дадим рекомендации по прохождению курса."
        );
        $lesson->setNumber(1);
        $dataStructuresCourse->addInclude($lesson);

        // Урок 2
        $lesson = new Lesson();
        $lesson->setTitle('Массивы');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, что такое массив, ".
            "что он из себя представляет и какие имеет преимущества и недостатки."
        );
        $lesson->setNumber(2);
        $dataStructuresCourse->addInclude($lesson);

        // Урок 3
        $lesson = new Lesson();
        $lesson->setTitle('Связные списки');
        $lesson->setContents(
            "В этом уроке мы расскажем вам о том, что такое связный список, ".
            "что он из себя представляет и какие имеет преимущества и недостатки."
        );
        $lesson->setNumber(3);
        $dataStructuresCourse->addInclude($lesson);

        $manager->persist($dataStructuresCourse);

        $manager->flush();
    }
}
