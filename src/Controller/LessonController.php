<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lesson")
 */
class LessonController extends AbstractController
{
    /**
     * @Route("/{id}", name="app_lesson_show", methods={"GET"})
     */
    public function show(Lesson $lesson): Response
    {
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_lesson_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $courseId = $lesson->getCourse()->getId();

        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_lesson_show', ['id' => $lesson->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_lesson_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $courseId = $lesson->getCourse()->getId();

        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson);
        }

        return $this->redirectToRoute('app_course_show', ['id' => $courseId], Response::HTTP_SEE_OTHER);
    }
}
