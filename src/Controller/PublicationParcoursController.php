<?php

namespace App\Controller;

use App\Entity\CommentairePublication;
use App\Entity\PublicationParcours;
use App\Form\PublicationParcoursType;
use App\Repository\CommentairePublicationRepository;
use App\Repository\ParcoursDeSanteRepository;
use App\Repository\PublicationParcoursRepository;
use App\Service\CommentSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publication/parcours')]
final class PublicationParcoursController extends AbstractController
{
    public function __construct(private readonly CommentSanitizer $commentSanitizer)
    {
    }

    private const EXPERIENCE_FILTERS = [
        'bad' => 'Bad',
        'good' => 'good',
        'excellent' => 'excellent',
    ];

    private const TYPE_PUBLICATION_FILTERS = [
        'opinion' => 'opinion',
        'event' => 'event',
    ];

    private const SORT_OPTIONS = ['ASC', 'DESC', 'HOT'];

    #[Route(name: 'app_publication_parcours_index', methods: ['GET'])]
    public function index(
        Request $request,
        PublicationParcoursRepository $publicationParcoursRepository,
        ParcoursDeSanteRepository $parcoursDeSanteRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $parcoursId = $request->query->getInt('parcoursId');
        $experienceQuery = trim((string) $request->query->get('experience', ''));
        $experienceKey = strtolower($experienceQuery);
        $selectedExperience = self::EXPERIENCE_FILTERS[$experienceKey] ?? null;
        $typePublicationQuery = trim((string) $request->query->get('typePublication', ''));
        $typePublicationKey = strtolower($typePublicationQuery);
        $selectedTypePublication = self::TYPE_PUBLICATION_FILTERS[$typePublicationKey] ?? null;
        $sortDateRaw = strtoupper(trim((string) $request->query->get('sortDate', 'DESC')));
        $selectedSortDate = in_array($sortDateRaw, self::SORT_OPTIONS, true) ? $sortDateRaw : 'DESC';
        $selectedParcours = null;

        if ($parcoursId > 0) {
            $selectedParcours = $parcoursDeSanteRepository->find($parcoursId);
        }

        $publications = $publicationParcoursRepository->findByFilters(
            $selectedParcours,
            $selectedExperience,
            $selectedTypePublication,
            $selectedSortDate
        );

        $publicationPagination = $paginator->paginate(
            $publications,
            max(1, $request->query->getInt('page', 1)),
            5
        );
        $publicationHotMetrics = $this->buildPublicationHotMetrics(
            $publicationPagination->getItems(),
            $publicationParcoursRepository
        );

        return $this->render('publication_parcours/index.html.twig', [
            'publication_parcours' => $publicationPagination,
            'publication_hot_metrics' => $publicationHotMetrics,
            'selected_parcours' => $selectedParcours,
            'selected_experience' => $selectedExperience,
            'selected_type_publication' => $selectedTypePublication,
            'selected_sort_date' => $selectedSortDate,
            'experience_filters' => [
                'Bad' => 'Bad',
                'Good' => 'good',
                'Excellent' => 'excellent',
            ],
            'type_publication_filters' => [
                'Opinion' => 'opinion',
                'Event' => 'event',
            ],
        ]);
    }

    #[Route('/new', name: 'app_publication_parcours_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ParcoursDeSanteRepository $parcoursDeSanteRepository
    ): Response
    {
        $parcoursId = $request->query->getInt('parcoursId');
        $selectedParcours = $parcoursId > 0 ? $parcoursDeSanteRepository->find($parcoursId) : null;

        if (!$selectedParcours) {
            $this->addFlash('warning', 'Please choose a trail first, then create a publication from that trail.');

            return $this->redirectToRoute('app_parcours_de_sante_index', [], Response::HTTP_SEE_OTHER);
        }

        $publicationParcour = new PublicationParcours();
        $publicationParcour->setParcoursDeSante($selectedParcours);

        $form = $this->createForm(PublicationParcoursType::class, $publicationParcour, [
            'require_image' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newImagePath = $this->uploadPublicationImage($imageFile);
                if ($newImagePath === null) {
                    $form->get('imageFile')->addError(new FormError('Unable to upload image. Please try again.'));
                } else {
                    $publicationParcour->setImagePublication($newImagePath);
                }
            }

            if ($form->isValid()) {
                $entityManager->persist($publicationParcour);
                $entityManager->flush();

                $selectedParcoursId = $publicationParcour->getParcoursDeSante()?->getId();

                return $this->redirectToRoute(
                    'app_publication_parcours_index',
                    $selectedParcoursId ? ['parcoursId' => $selectedParcoursId] : [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('publication_parcours/new.html.twig', [
            'publication_parcour' => $publicationParcour,
            'form' => $form,
            'cancel_parcours_id' => $publicationParcour->getParcoursDeSante()?->getId(),
        ]);
    }

    #[Route('/{id}/comment', name: 'app_publication_parcours_comment_add', methods: ['POST'])]
    public function addComment(
        Request $request,
        PublicationParcours $publicationParcour,
        EntityManagerInterface $entityManager
    ): Response
    {
        $redirectParams = $this->buildFeedRedirectParams($request);

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('add_comment' . $publicationParcour->getId(), $token)) {
            $this->addFlash('error', 'Invalid comment form. Please try again.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $commentText = trim((string) $request->request->get('commentaire', ''));
        if ($commentText === '') {
            $this->addFlash('error', 'Comment cannot be empty.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $comment = new CommentairePublication();
        $comment->setCommentaire($this->commentSanitizer->sanitize($commentText));
        $comment->setDateCommentaire(new \DateTime());
        $comment->setPublicationParcours($publicationParcour);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
    }

    #[Route('/{id}/comment/{commentId}/edit', name: 'app_publication_parcours_comment_edit', methods: ['POST'])]
    public function editComment(
        Request $request,
        PublicationParcours $publicationParcour,
        int $commentId,
        CommentairePublicationRepository $commentairePublicationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $redirectParams = $this->buildFeedRedirectParams($request);
        $comment = $commentairePublicationRepository->find($commentId);

        if (!$comment || $comment->getPublicationParcours()?->getId() !== $publicationParcour->getId()) {
            $this->addFlash('error', 'Comment not found.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('edit_comment' . $comment->getId(), $token)) {
            $this->addFlash('error', 'Invalid edit form. Please try again.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $commentText = trim((string) $request->request->get('commentaire', ''));
        if ($commentText === '') {
            $this->addFlash('error', 'Comment cannot be empty.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $comment->setCommentaire($this->commentSanitizer->sanitize($commentText));

        $entityManager->flush();

        return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
    }

    #[Route('/{id}/comment/{commentId}/delete', name: 'app_publication_parcours_comment_delete', methods: ['POST'])]
    public function deleteComment(
        Request $request,
        PublicationParcours $publicationParcour,
        int $commentId,
        CommentairePublicationRepository $commentairePublicationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $redirectParams = $this->buildFeedRedirectParams($request);
        $comment = $commentairePublicationRepository->find($commentId);

        if (!$comment || $comment->getPublicationParcours()?->getId() !== $publicationParcour->getId()) {
            $this->addFlash('error', 'Comment not found.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('delete_comment' . $comment->getId(), $token)) {
            $this->addFlash('error', 'Invalid delete form. Please try again.');

            return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectAfterCommentAction($request, $publicationParcour, $redirectParams);
    }

    #[Route('/{id}/comments', name: 'app_publication_parcours_comments', methods: ['GET'])]
    public function comments(Request $request, PublicationParcours $publicationParcour): Response
    {
        $backFeedParams = $this->buildFeedRedirectParams($request);

        return $this->render('publication_parcours/show.html.twig', [
            'publication_parcour' => $publicationParcour,
            'comments_page' => true,
            'back_feed_params' => $backFeedParams,
        ]);
    }

    #[Route('/{id}', name: 'app_publication_parcours_show', methods: ['GET'])]
    public function show(PublicationParcours $publicationParcour): Response
    {
        return $this->render('publication_parcours/show.html.twig', [
            'publication_parcour' => $publicationParcour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_publication_parcours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PublicationParcours $publicationParcour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationParcoursType::class, $publicationParcour, [
            'require_image' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newImagePath = $this->uploadPublicationImage($imageFile);
                if ($newImagePath === null) {
                    $form->get('imageFile')->addError(new FormError('Unable to upload image. Please try again.'));
                } else {
                    $this->removeLocalImageIfManaged($publicationParcour->getImagePublication());
                    $publicationParcour->setImagePublication($newImagePath);
                }
            }

            if ($form->isValid()) {
                $entityManager->flush();

                $selectedParcoursId = $publicationParcour->getParcoursDeSante()?->getId();

                return $this->redirectToRoute(
                    'app_publication_parcours_index',
                    $selectedParcoursId ? ['parcoursId' => $selectedParcoursId] : [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('publication_parcours/edit.html.twig', [
            'publication_parcour' => $publicationParcour,
            'form' => $form,
            'cancel_parcours_id' => $publicationParcour->getParcoursDeSante()?->getId(),
        ]);
    }

    #[Route('/{id}', name: 'app_publication_parcours_delete', methods: ['POST'])]
    public function delete(Request $request, PublicationParcours $publicationParcour, EntityManagerInterface $entityManager): Response
    {
        $selectedParcoursId = $publicationParcour->getParcoursDeSante()?->getId();

        if ($this->isCsrfTokenValid('delete'.$publicationParcour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicationParcour);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_publication_parcours_index',
            $selectedParcoursId ? ['parcoursId' => $selectedParcoursId] : [],
            Response::HTTP_SEE_OTHER
        );
    }

    private function uploadPublicationImage(UploadedFile $imageFile): ?string
    {
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/parcours';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalFilename);
        if (!$safeFilename) {
            $safeFilename = 'publication';
        }

        $extension = $imageFile->guessExtension() ?: $imageFile->getClientOriginalExtension() ?: 'jpg';
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        try {
            $imageFile->move($uploadsDir, $newFilename);
        } catch (FileException $e) {
            return null;
        }

        return 'uploads/parcours/' . $newFilename;
    }

    private function removeLocalImageIfManaged(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        $normalizedPath = ltrim($imagePath, '/');
        if (!str_starts_with($normalizedPath, 'uploads/parcours/')) {
            return;
        }

        $absolutePath = $this->getParameter('kernel.project_dir') . '/public/' . $normalizedPath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function buildFeedRedirectParams(Request $request): array
    {
        $parcoursId = $request->request->getInt('parcoursId');
        if ($parcoursId <= 0) {
            $parcoursId = $request->query->getInt('parcoursId');
        }

        $experience = trim((string) ($request->request->get('experience', $request->query->get('experience', ''))));
        $typePublication = trim((string) ($request->request->get('typePublication', $request->query->get('typePublication', ''))));
        $sortDateRaw = strtoupper(trim((string) ($request->request->get('sortDate', $request->query->get('sortDate', 'DESC')))));
        $sortDate = in_array($sortDateRaw, self::SORT_OPTIONS, true) ? $sortDateRaw : 'DESC';
        $page = $request->request->getInt('page');
        if ($page <= 0) {
            $page = $request->query->getInt('page');
        }

        $redirectParams = [
            'sortDate' => $sortDate,
        ];

        if ($parcoursId > 0) {
            $redirectParams['parcoursId'] = $parcoursId;
        }

        if ($experience !== '') {
            $redirectParams['experience'] = $experience;
        }

        if ($typePublication !== '') {
            $redirectParams['typePublication'] = $typePublication;
        }

        if ($page > 1) {
            $redirectParams['page'] = $page;
        }

        return $redirectParams;
    }

    private function redirectAfterCommentAction(Request $request, PublicationParcours $publicationParcour, array $feedRedirectParams): Response
    {
        $redirectTo = strtolower(trim((string) $request->request->get('redirectTo', '')));

        if ($redirectTo === 'admin_user_behavior') {
            return $this->redirectToRoute(
                'admin_trail_user_behavior',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        if ($redirectTo === 'comments') {
            return $this->redirectToRoute(
                'app_publication_parcours_comments',
                ['id' => $publicationParcour->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        if ($redirectTo === 'show') {
            return $this->redirectToRoute(
                'app_publication_parcours_show',
                ['id' => $publicationParcour->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->redirectToRoute('app_publication_parcours_index', $feedRedirectParams, Response::HTTP_SEE_OTHER);
    }

    /**
     * @param PublicationParcours[] $publications
     * @return array<int, array{hotScore: int, commentCount: int, ageDays: int}>
     */
    private function buildPublicationHotMetrics(array $publications, PublicationParcoursRepository $publicationParcoursRepository): array
    {
        $publicationIds = [];
        foreach ($publications as $publication) {
            if (!$publication instanceof PublicationParcours) {
                continue;
            }

            $publicationId = $publication->getId();
            if ($publicationId !== null) {
                $publicationIds[] = $publicationId;
            }
        }

        return $publicationParcoursRepository->findHotMetricsForPublicationIds($publicationIds);
    }
}
