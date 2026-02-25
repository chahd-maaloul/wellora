<?php

namespace App\Controller;

use App\Entity\Exercises;
use App\Form\ExerciseType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/coach/exercises')]
class ExerciseController extends AbstractController
{  
    #[Route('/new', name: 'exercises_new', methods: ['GET', 'POST'])]
    public function new(ManagerRegistry $m, Request $req): Response
    {  
        $em = $m->getManager();  
        $exercise = new Exercises();  
         $exercise->setUser($this->getUser());
        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // IMPORTANT: VichUploader nécessite que la date updatedAt soit définie manuellement
            if ($exercise->getVideoFile()) {
                $exercise->setUpdatedAt(new \DateTimeImmutable());
            }
            
            $em->persist($exercise);
            $em->flush();
            
            $this->addFlash('success', 'Exercise created successfully!');
            return $this->redirectToRoute('exercises_show');  // <-- Redirige vers la liste
        }
         else {
        // ADD THIS TO DEBUG:
        dump($form->getErrors(true, true)); 
    }
        
        return $this->render('coach/exercise/new-exercises.html.twig', [
            'page_title' => 'Create New Exercise',
            'form' => $form->createView(),        
        ]);  
    }

    
    
    #[Route('/show', name: 'exercises_show', methods: ['GET'])]
    public function show(ManagerRegistry $m): Response
    {
        $em = $m->getManager();
        $exerciseRepository = $em->getRepository(Exercises::class);
        $exercises = $exerciseRepository->findBy(['User' => $this->getUser()]);

        // Convertir les objets en tableau pour Alpine.js
        $exercisesArray = [];
        foreach ($exercises as $exercise) {
            $exercisesArray[] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'description' => $exercise->getDescription(),
                'category' => $exercise->getCategory(),
                'difficulty_level' => $exercise->getDifficultyLevel(),
                'defaultUnit' => $exercise->getDefaultUnit(),
                'videoUrl' => $exercise->getVideoUrl(),
                'videoFileName' => $exercise->getVideoFileName(),  // <-- IMPORTANT : ajouté
                'isActive' => $exercise->isActive(),
                'createdAt' => $exercise->getCreatedAt() ? $exercise->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'duration' => $exercise->getDuration(),
                'calories' => $exercise->getCalories(),
                'sets' => $exercise->getSets(),
                'reps' => $exercise->getReps(),
            ];
        }
            
        return $this->render('coach/exercise/show-exercises.html.twig', [
            'page_title' => 'Exercise Library',
            'exercises' => $exercisesArray,
        ]);  
    }
     
    // Dans votre contrôleur
// Dans votre contrôleur
#[Route('/delete/{id}', name: 'delete_exercise', methods: ['POST', 'DELETE'])]
public function delete(ManagerRegistry $m, Request $request, $id): Response
{
    $em = $m->getManager();
    $exercise = $em->getRepository(Exercises::class)->find($id);

    if (!$exercise || $exercise->getUser() !== $this->getUser()) {
        if ($request->isXmlHttpRequest()) {
            return $this->json(['error' => 'Exercise not found'], 404);
        }
        throw $this->createNotFoundException('Exercise not found');
    }

    // Vérification CSRF - Changé de 'delete' . $id à 'global'
    if ($request->isMethod('POST')) {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('global', $submittedToken)) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['error' => 'Invalid CSRF token'], 400);
            }
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }

    $em->remove($exercise);
    $em->flush();

    if ($request->isXmlHttpRequest()) {
        return $this->json(['success' => true]);
    }

    $this->addFlash('success', 'Exercise deleted successfully!');
    return $this->redirectToRoute('exercises_show');
}
    
    #[Route('/update/{id}', name: 'exercises_edit', methods: ['GET', 'POST'])]
    public function update(Request $request, ManagerRegistry $m, $id): Response
    {
        $em = $m->getManager();
        $exercise = $em->getRepository(Exercises::class)->find($id);

        if (!$exercise || $exercise->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Exercise not found');
        }

        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('exercises_show');
        }

        return $this->render('coach/exercise/update-exercises.html.twig', [
            'form' => $form->createView(),
            'exercise' => $exercise,
        ]);
    }
}