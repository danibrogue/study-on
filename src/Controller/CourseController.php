<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Form\CourseType;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingCourses;
use App\Service\BillingTransactions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class CourseController extends AbstractController
{
    private const OPERATION_TYPE = [
        'deposit' => 1,
        'payment' => 2
    ];

    private function createKeyArray($arr, $key)
    {
        foreach ($arr as $var) {
            $arrOut[$var[$key]] = $var;
        }
        return $arrOut;
    }
    /**
     * @Route("/", name="app_course_index", methods={"GET"})
     */
    public function index(
        CourseRepository $courseRepository,
        BillingCourses $billingCourses,
        BillingTransactions $billingTransactions
    ): Response {
        if (!$this->getUser()) {
            $courses = $courseRepository->findAll();
            return $this->render('course/index.html.twig', [
                'data' => $courses
            ]);
        }
        $billingCourses = $billingCourses->findCourses();
        foreach ($billingCourses as $course) {
            $data = $courseRepository->findOneBy(['code' => $course['code']]);
            if ($data !== null) {
                $courseData[] = array_merge([
                    'id' => $data->getId(),
                    'title' => $data->getTitle(),
                    'info' => $data->getInfo()
                ], $course);
            }
        }
        $transactions = $billingTransactions->getTransactions(
            [
                'filters' => [
                    'type' => self::OPERATION_TYPE['payment'],
                    'skip_expired' => true
                ]
            ],
            $this->getUser()->getApiToken()
        );
        $keyCourses = $this->createKeyArray($courseData, 'code');
        $keyTransactions = $this->createKeyArray($transactions, 'course_code');
        foreach (array_keys($keyCourses) as $key) {
            if (isset($keyTransactions[$key])) {
                $billingData[] = array_merge(
                    $keyCourses[$key],
                    ['created_at' => $keyTransactions[$key]['created_at']],
                    ['expires_at' => $keyTransactions[$key]['expires_at'] ?? null],
                    ['purchased' => true]
                );
            } else {
                $billingData[] = array_merge(
                    $keyCourses[$key],
                    ['purchased' => false]
                );
            }
        }
        return $this->render('course/index.html.twig', [
            'data' => $billingData
        ]);
    }

    /**
     * @Route("/courses/new", name="app_course_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function new(Request $request, CourseRepository $courseRepository): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/courses/{id}", name="app_course_show", methods={"GET"})
     */
    public function show(
        Course $course,
        BillingTransactions $billingTransactions,
        BillingCourses $billingCourses
    ): Response {
        if (!$this->getUser()) {
            throw new AccessDeniedHttpException();
        }
        $billingCourses = $billingCourses->findCourses();
        $keyCourses = $this->createKeyArray($billingCourses, 'code');
        if ($keyCourses[$course->getCode()]['type'] !== 'free') {
            $transactions = $billingTransactions->getTransactions(
                [
                    'filters' => [
                        'type' => self::OPERATION_TYPE['payment'],
                        'course_code' => $course->getCode(),
                        'skip_expired' => true
                    ]
                ],
                $this->getUser()->getApiToken()
            );

            if (!$transactions) {
                throw new AccessDeniedHttpException();
            }
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * @Route("/courses/{id}/edit", name="app_course_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function edit(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/courses/{id}", name="app_course_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/courses/{id}/lessons/add", name="app_course_add_lesson", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function addLesson(Request $request, LessonRepository $lessonRepository, Course $course): Response
    {
        $lesson = new Lesson();
        $lesson->setCourse($course);
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }
}
