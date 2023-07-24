<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "home", methods: ["GET"])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $limit = $request->query->getInt("page", 1) * 15;
        $endOfTricks = $limit >= $entityManager->getRepository(Trick::class)->count([]);
        $tricks = $entityManager->getRepository(Trick::class)->findBy(
            [
                "isPublished" => true
            ], [
                "createdAt" => "DESC"
            ],
            $limit,
            0
        );

        return $this->render("home/index.html.twig", [
            "tricks" => $tricks,
            "endOfTricks" => $endOfTricks
        ]);
    }
}
