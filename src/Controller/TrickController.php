<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MediaTrick;
use App\Entity\Trick;
use App\Entity\TrickHistory;
use App\Form\CommentType;
use App\Form\MediaTrickType;
use App\Form\TrickFirstStepType;
use App\Repository\TrickRepository;
use App\Utils\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private EntityManagerInterface $em,
        private TrickRepository $trickRepository
    )
    {
    }

    /**
     * First step add trick
     *
     * @param Request $request
     * @param TrickRepository $trickRepository
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Route('/trick/add', name: 'app_trick_add', methods: ['GET', 'POST'])]
    public function firstStep(
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "author" => $this->getUser(),
            "isPublished" => false
        ]);

        if (!$trick) {
            $trick = new Trick();
        }

        $form = $this->createForm(TrickFirstStepType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->generateSlugTrick($trick);
            $trick
                ->setAuthor($this->getUser())
                ->setSlug($slug)
                ->setCreatedAt(new \DateTimeImmutable())
            ;

            if(!$trick->getId()) {
                $this->em->persist($trick);
            }
            $this->em->flush();

            return $this->redirectToRoute("app_trick_medias");
        }

        return $this->render('trick/add-trick.html.twig', [
            'form' => $form->createView(),
            "isCreated" => $trick->getId() !== null
        ]);
    }

    /**
     * Second step add trick
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    #[Route('/trick/add/medias', name: 'app_trick_medias', methods: ['GET', 'POST'])]
    public function secondStep(
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "author" => $this->getUser(),
            "isPublished" => false
        ]);

        if (!$trick) {
            return $this->redirectToRoute("app_trick_add");
        }

        $mediaTrick = new MediaTrick();

        $form = $this->createForm(MediaTrickType::class, $mediaTrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addMedia = $this->addMedia($form, $mediaTrick, $trick);
            if ($addMedia) {
                return $this->redirectToRoute("app_trick_medias");
            }
        }

        return $this->render('trick/add-trick-medias.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    /**
     * Cancel add trick
     *
     * @return Response
     */
    #[Route('/trick/cancel-add-trick', name: 'app_trick_cancel_add_trick', methods: ['GET'])]
    public function cancelAddTrick(): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "author" => $this->getUser(),
            "isPublished" => false
        ]);

        if (!$trick) {
            $this->addFlash(
                "warning",
                "Aucune figure n'est en cours de création."
            );

            return $this->redirectToRoute("home");
        }

        $this->deleteTrick($trick);

        $this->addFlash(
            "success",
            "La figure a été annulé avec succès."
        );

        return $this->redirectToRoute("app_trick_add");

    }

    /**
     * Delete media of trick
     *
     * @param string $method
     * @param string $id
     *
     * @return Response
     */
    #[Route('/trick/{method}/medias/delete/{id}', name: 'app_trick_delete_media', methods: ['GET'])]
    public function trickDeleteMedia(
        string $method,
        string $id
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $mediaTrick = $this->em->getRepository(MediaTrick::class)->findOneBy([
            "id" => $id
        ]);

        $trick = $mediaTrick->getTrick() ?? null;

        $isFeatured = false;

        if (
            $trick &&
            (($method === "add" && $trick->isPublished()) ||
            ($method === "edit" && !$trick->isPublished()))
        ) {
            $this->addFlash(
                "danger",
                "Action interdite"
            );

            return $this->redirectToRoute("home");
        }

        if ($mediaTrick->getType() === "image") {
            FileManager::deleteFile(
                $this->getParameter("trick.folder"),
                $mediaTrick->getLink()
            );

            if ($trick->getPictureFeatured() === $mediaTrick) {
                $trick->setPictureFeatured(null);
                $isFeatured = true;
            }
        }

        $this->em->remove($mediaTrick);

        $this->addFlash(
            "success",
            "Le média a été supprimé avec succès."
        );

        if ($method === "add") {
            $this->em->flush();

            return $this->redirectToRoute("app_trick_medias");
        } else if ($method === "edit") {
            $trickHistory = new TrickHistory();
            $trickHistory
                ->setTrick($trick)
                ->setAuthor($this->getUser())
                ->setUpdatedAt(new \DateTimeImmutable("now"));

            if ($isFeatured) {
                $trickHistory->setIsMediaFeatured(true);
            }

            if ($mediaTrick->getType() === "image") {
                $trickHistory->setIsMediaImageDeleted(true);
            } else {
                $trickHistory->setIsMediaVideoDeleted(true);
            }

            $trick->setLastUpdatedAt(new \DateTimeImmutable("now"));

            $this->em->persist($trickHistory);
            $this->em->flush();

            return $this->redirectToRoute("app_trick_edit", [
                "slug" => $trick->getSlug()
            ]);
        }

        return $this->redirectToRoute("home");

    }

    /**
     * Details trick
     *
     * @param string $slug
     * @param Request $request
     *
     * @return Response
     */
    #[Route("/trick/details/{slug}", name: "app_trick_details", methods: ["GET", "POST"])]
    public function trickDetails(
        string $slug,
        Request $request
    ): Response
    {
        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "slug" => $slug,
            "isPublished" => true
        ]);

        if (!$trick) {
            throw $this->createNotFoundException();
        }

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $this->denyAccessUnlessGranted("ROLE_USER");

            $comment
              ->setTrick($trick)
              ->setAuthor($this->getUser())
              ->setCreatedAt(new \DateTimeImmutable("now"));

            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash(
                "success",
              "Commentaire ajouté avec succès."
            );

            return $this->redirectToRoute("app_trick_details", [
                "slug" => $slug
            ]);

        }

        $formEditComment = $this->createForm(CommentType::class);
        $formEditComment->add("commentEdited", TextType::class, [
            "label" => null,
            "row_attr" => [
                "class" => "mb-3"
            ],
            "required" => false,
            "attr" => [
                "class" => "form-control"
            ],
            "mapped" => false
        ]);
        $formEditComment->handleRequest($request);

        if ($formEditComment->isSubmitted() && $formEditComment->isValid()) {
            $this->denyAccessUnlessGranted("ROLE_USER");

            $commentEdited = $this->em->getRepository(Comment::class)->findOneBy([
                "id" => $formEditComment->get("commentEdited")->getData(),
            ]);

            if ($commentEdited && $commentEdited->getAuthor() === $this->getUser()) {
                $commentEdited->setContent($formEditComment->get("content")->getData());
                $commentEdited->setUpdatedAt(new \DateTimeImmutable("now"));

                $this->em->flush();

                $this->addFlash(
                    "success",
                    "Commentaire modifié avec succès."
                );

                return $this->redirectToRoute("app_trick_details", [
                    "slug" => $slug
                ]);
            } else {
                $this->addFlash(
                    "danger",
                    "Action interdite."
                );
            }
        }

        return $this->render("trick/trick-details.html.twig", [
            "trick" => $trick,
            "formComment" => $formComment->createView(),
            "formEditComment" => $formEditComment->createView()
        ]);
    }

    /**
     * Details trick
     *
     * @param string $slug
     * @param Request $request
     * @param TrickRepository $trickRepository
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    #[Route('/trick/edit/{slug}', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    public function trickEdit(
        string $slug,
        Request $request,
        TrickRepository $trickRepository
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "slug" => $slug,
            "isPublished" => true
        ]);

        if (!$trick) {
            throw $this->createNotFoundException();
        }

        $originalTrick = [
            "name" => $trick->getName(),
            "description" => $trick->getDescription(),
            "groupTrick" => $trick->getGroupTrick(),
            "pictureFeatured" => $trick->getPictureFeatured(),
            "mediaTricks" => $trick->getMediaTricks()
        ];

        $mediaTrick = new MediaTrick();
        $formMediaTrick = $this->createForm(MediaTrickType::class, $mediaTrick);
        $formMediaTrick
            ->add("isEditMediaTrick", CheckboxType::class, [
                "label" => null,
                "row_attr" => [
                    "class" => "d-none mb-3"
                ],
                "required" => false,
                "attr" => [
                    "class" => "form-check"
                ],
                "mapped" => false
            ])
            ->add("mediaTrickEdited", TextType::class, [
                "label" => null,
                "row_attr" => [
                    "class" => "d-none mb-3"
                ],
                "required" => false,
                "attr" => [
                    "class" => "form-control"
                ],
                "mapped" => false
            ]);
        $formMediaTrick->handleRequest($request);

        if ($formMediaTrick->isSubmitted() && $formMediaTrick->isValid()) {
            $originalMediaTrick = null;

            if ($formMediaTrick->get("isEditMediaTrick")->getData()) {
                $originalMediaTrick = $this->em->getRepository(MediaTrick::class)->findOneBy([
                    "id" => $formMediaTrick->get("mediaTrickEdited")->getData()
                ]);
            }

            if ($this->addMedia($formMediaTrick, $originalMediaTrick ?? $mediaTrick, $trick)) {
                $this->setMediaHistory($formMediaTrick, $trick);

                return $this->redirectToRoute("app_trick_edit", [
                    "slug" => $trick->getSlug()
                ]);
            }

            $this->addFlash(
                "danger",
                "Une erreur s'est produite, veuillez réessayer l'opération."
            );
        }

        $formTrick = $this->createForm(TrickFirstStepType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            if (!$this->editTrick($originalTrick, $trick)) {
                $this->addFlash(
                    "danger",
                    "Une erreur s'est produite lors de la modification de la figure '"
                    . $trick->getName() . "'."
                );
                return $this->redirectToRoute("home");
            }

            $this->addFlash(
                "success",
                "La figure a été modifiée avec succès."
            );

            return $this->redirectToRoute("app_trick_edit", [
                "slug" => $trick->getSlug()
            ]);
        }

        return $this->render("trick/edit-trick.html.twig", [
            "trick" => $trick,
            "formMediaTrick" => $formMediaTrick->createView(),
            "formTrick" => $formTrick->createView()
        ]);
    }

    /**
     * Publish trick
     *
     * @param string $slug
     *
     * @return Response
     */
    #[Route('/trick/publish/{slug}', name: 'app_trick_publish', methods: ['GET'])]
    public function trickPublish(
        string $slug
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "slug" => $slug,
            "author" => $this->getUser(),
            "isPublished" => false
        ]);

        if (!$trick) {
            $this->addFlash(
                "danger",
                "Action interdite"
            );
            return $this->redirectToRoute("home");
        }

        $trick->setIsPublished(true);
        $this->em->flush();

        $this->addFlash(
            "success",
            "La figure a été publié avec succès."
        );

        return $this->redirectToRoute("app_trick_add");
    }

    /**
     * Delete trick
     *
     * @param string $slug
     *
     * @return Response
     */
    #[Route('/trick/delete/{slug}', name: 'app_trick_delete', methods: ['GET'])]
    public function trickDeleteSlug(
        string $slug
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $trick = $this->em->getRepository(Trick::class)->findOneBy([
            "slug" => $slug
        ]);

        if (!$trick) {
            $this->addFlash(
                "danger",
                "La suppression n'a pu aboutir, la figure n'a pas été trouvé."
            );
        } else {
            $this->deleteTrick($trick);

            $this->addFlash(
                "success",
                "La figure a été supprimé avec succès."
            );
        }


        return $this->redirect(
            $this->generateUrl(
                "home"
            )."#containerTricks"
        );
    }

    /**
     * Delete comment
     *
     * @param string $id
     *
     * @return Response
     */
    #[Route('/comment/delete/{id}', name: 'app_comment_delete', methods: ['GET'])]
    public function commentDeleteId(
        string $id
    ): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $comment = $this->em->getRepository(Comment::class)->findOneBy([
            "id" => $id
        ]);

        if (!$comment) {
            throw $this->createNotFoundException();
        }

        if ($comment->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                "danger",
                "Vous ne pouvez pas supprimer ce commentaire."
            );
        } else {
            $this->em->remove($comment);
            $this->em->flush();

            $this->addFlash(
                "success",
                "Le commentaire a été supprimé avec succès."
            );
        }

        return $this->redirectToRoute("app_trick_details", [
            "slug" => $comment->getTrick()->getSlug()
        ]);

    }

    /**
     * Delete trick
     *
     * @param Trick $trick
     *
     * @return void
     */
    public function deleteTrick(Trick $trick): void
    {
        foreach ($trick->getMediaTricks() as $mediaTrick) {
            if ($mediaTrick->getType() === "image") {
                FileManager::deleteFile(
                    $this->getParameter("trick.folder"),
                    $mediaTrick->getLink()
                );
            }
            $this->em->remove($mediaTrick);
        }

        if ($trick->getPictureFeatured()) {
            $pictureFeatured = $trick->getPictureFeatured();
            FileManager::deleteFile(
                $this->getParameter("trick.folder"),
                $pictureFeatured->getLink()
            );
            $trick->setPictureFeatured(null);
            $this->em->remove($pictureFeatured);
        }

        $this->em->flush();

        $this->em->remove($trick);

        $this->em->flush();
    }

    /**
     * Function edit trick
     *
     * @param array $originalTrick
     * @param Trick $trick
     *
     * @return bool
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function editTrick(array $originalTrick, Trick $trick): bool
    {
        $trickHistory = new TrickHistory();

        if ($originalTrick["name"] !== $trick->getName()) {
            $slug = $this->generateSlugTrick($trick);
            $trick->setSlug($slug);
            $trickHistory->setIsNameUpdated(true);
        }

        if ($originalTrick["description"] !== $trick->getDescription()) {
            $trickHistory->setIsDescriptionUpdated(true);
        }

        if ($originalTrick["groupTrick"] !== $trick->getGroupTrick()) {
            $trickHistory->setIsGroupTrickUpdated(true);
        }

        $trickHistory->setTrick($trick);
        $trickHistory->setAuthor($this->getUser());
        $trickHistory->setUpdatedAt(new \DateTimeImmutable("now"));
        $this->em->persist($trickHistory);

        $trick->setLastUpdatedAt(new \DateTimeImmutable("now"));
        $trick->addTrickHistory($trickHistory);
        $this->em->persist($trick);

        $this->em->flush();

        return true;
    }

    /**
     * @param FormInterface $form
     * @param MediaTrick $mediaTrick
     * @param Trick $trick
     *
     * @return bool
     *
     * @throws Exception
     */
    private function addMedia(
        FormInterface $form,
        MediaTrick $mediaTrick,
        Trick $trick
    ): bool
    {
        $link = $form->get("linkMovie")->getData();

        if ($form->get("type")->getData() === "image") {
            if ($form->get("imageFile")->getData() === null) {
                $form->get("imageFile")->addError(new FormError("Veuillez sélectionner une image."));
                return false;
            }

            $pictureFeaturedLink = null;
            if ($form->get("isFeatured")->getData()) {
                $pictureFeaturedLink = $trick->getPictureFeatured()?->getLink();
                $mediaTrick = $trick->getPictureFeatured() ?? new MediaTrick();
            } else {
                $pictureFeaturedLink = $mediaTrick->getId() ? $mediaTrick->getLink() : null;
            }

            $link = FileManager::uploadFile(
                $form->get("imageFile")->getData(),
                $this->getParameter("trick.folder"),
                $pictureFeaturedLink
            );

            $mediaTrick
                ->setLink($link)
                ->setAddedAt(new \DateTimeImmutable())
                ->setType($form->get("type")->getData())
                ->setSourceLink($form->get("sourceLink")->getData())
                ->setSourceName($form->get("sourceName")->getData())
            ;

        } else {
            if ($form->get("linkMovie")->getData() === null) {
                $form->get("linkMovie")->addError(new FormError("Veuillez saisir le lien de la vidéo."));
                return false;
            }

            if ($mediaTrick->getType() === "image") {
                FileManager::deleteFile(
                    $this->getParameter("trick.folder"),
                    $mediaTrick->getLink()
                );
            }

            $mediaTrick
                ->setLink($link)
                ->setAddedAt(new \DateTimeImmutable())
                ->setType($form->get("type")->getData())
                ->setSourceLink($form->get("sourceLink")->getData())
                ->setSourceName($form->get("sourceName")->getData())
            ;
        }

        if ($form->get("isFeatured")->getData()) {
            $trick->setPictureFeatured($mediaTrick);
        } else {
            if (!$mediaTrick->getId()) {
                $trick->addMediaTrick($mediaTrick);
            }
        }

        $this->em->flush();

        return true;
    }

    /**
     * Function add media trick History
     *
     * @param FormInterface $form
     * @param Trick $trick
     *
     * @return void
     *
     */
    private function setMediaHistory(
        FormInterface $form,
        Trick $trick
    ): void
    {
        $trickHistory = (new TrickHistory())
            ->setTrick($trick)
            ->setAuthor($this->getUser())
            ->setUpdatedAt(new \DateTimeImmutable("now"));

        if ($form->get("type")->getData() === "image") {
            if ($form->get("isFeatured")->getData()) {
                $trickHistory->setIsMediaFeatured(true);
            }

            if ($form->get("isEditMediaTrick")->getData()) {
                $trickHistory->setIsMediaImageUpdated(true);
            } else {
                $trickHistory->setIsMediaImageAdded(true);
            }
        } else {
            if ($form->get("isEditMediaTrick")->getData()) {
                $trickHistory->setIsMediaVideoUpdated(true);
            } else {
                $trickHistory->setIsMediaVideoAdded(true);
            }
        }

        $trick->setLastUpdatedAt(new \DateTimeImmutable("now"));

        $this->em->persist($trickHistory);
        $this->em->flush();

    }

    /**
     * Generate slug function
     *
     * @param Trick $trick
     *
     * @return string
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function generateSlugTrick(Trick $trick): string
    {
        $slugBase = strtolower(preg_replace("/(é|è|ê|ë|É|È|Ê|Ë)/", "e", $trick->getName()));
        $slugBase = strtolower(preg_replace("/(â|à|ä|ã|Â|À|Ä|Ã)/", "a", $slugBase));
        $slugBase = strtolower(preg_replace("/(ï|î|ì|Ï|Î|Ì)/", "i", $slugBase));
        $slugBase = strtolower(preg_replace("/(ù|ü|û|Ù|Û|Ü)/", "u", $slugBase));
        $slugBase = strtolower(preg_replace("/(ô|ö|ò|õ|Ô|Ö|Ò|Õ)/", "o", $slugBase));
        $slugBase = strtolower(preg_replace("/(ÿ)/", "y", $slugBase));
        $slugBase = strtolower(preg_replace("/(ç|Ç)/", "c", $slugBase));
        $slugBase = strtolower(preg_replace("/(\s|')/", "-", $slugBase));
        $slugBase = strtolower(preg_replace("/[^a-zA-Z0-9\-]/", "", $slugBase));

        $slug = $slugBase;
        $count = 0;

        while (
            $this->trickRepository->getCountTricksBySlugNotEqualWithId(
                $slug,
                $trick->getId()
            ) > 0
        ) {
            $slug = $slugBase . ($count > 0 ? "-" . $count : "");
            $count++;
        }

        return $slug;
    }

}
