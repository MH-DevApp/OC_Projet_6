<?php

namespace App\Controller;

use App\Entity\MediaTrick;
use App\Entity\Trick;
use App\Form\MediaTrickType;
use App\Form\TrickFirstStepType;
use App\Repository\TrickRepository;
use App\Utils\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        private EntityManagerInterface $em
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
        Request $request,
        TrickRepository $trickRepository
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
            $slugBase = $this->generateSlug($trick->getName());
            $slug = $slugBase;
            $count = 0;
            while (
                $trickRepository->getCountTricksBySlugNotEqualWithId(
                    $slug,
                    $trick->getId()
                ) > 0
            ) {
                $slug = $slugBase . ($count > 0 ? "-" . $count : "");
                $count++;
            }

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

        $trick = $mediaTrick->getTrick();

        if ($method === "add") {
            if ($trick->isPublished()) {
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
                }
            }

            $this->em->remove($mediaTrick);
            $this->em->flush();

            return $this->redirectToRoute("app_trick_medias");
        }

        return $this->redirectToRoute("home");

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
     * Publish trick
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
            $trick->addMediaTrick($mediaTrick);
        }

        $this->em->flush();

        return true;
    }

    /**
     * Generate slug function
     *
     * @param string $name
     *
     * @return string
     */
    private function generateSlug(string $name): string
    {
        $name = strtolower(preg_replace("/(é|è|ê|ë|É|È|Ê|Ë)/", "e", $name));
        $name = strtolower(preg_replace("/(â|à|ä|ã|Â|À|Ä|Ã)/", "a", $name));
        $name = strtolower(preg_replace("/(ï|î|ì|Ï|Î|Ì)/", "i", $name));
        $name = strtolower(preg_replace("/(ù|ü|û|Ù|Û|Ü)/", "u", $name));
        $name = strtolower(preg_replace("/(ô|ö|ò|õ|Ô|Ö|Ò|Õ)/", "o", $name));
        $name = strtolower(preg_replace("/(ÿ)/", "y", $name));
        $name = strtolower(preg_replace("/(ç|Ç)/", "c", $name));
        $name = strtolower(preg_replace("/(\s|')/", "-", $name));

        return strtolower(preg_replace("/[^a-zA-Z0-9\-]/", "", $name));
    }

}
