<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "home", methods: ["GET"])]
    public function index(
        EntityManagerInterface $entityManager
    ): Response
    {
        $tricks = $entityManager->getRepository(Trick::class)->findBy(
            [
                'isPublished' => true
            ], [
                'createdAt' => 'DESC'
            ]
        );

        return $this->render('home/index.html.twig', [
            "tricks" => $tricks
        ]);
    }
}
