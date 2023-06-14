<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RefreshConfirmEmailType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AuthController extends AbstractController
{
    /**
     * Register new user
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param CsrfTokenManagerInterface $csrfTokenManager
     *
     * @return Response
     *
     * @throws TransportExceptionInterface
     */
    #[Route('/auth/register', name: 'app_auth_register', methods: ["GET", "POST"])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setUsername(
                    strtolower($user->getUsername())
                )
                ->setCreatedAt(new \DateTimeImmutable("now"))
                ->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('repeatedPassword')->getData()
                    )
                )
                ->setValidEmailExpiredAt(new \DateTimeImmutable("+5 minutes"));

            $token = uniqid("tk_");
            $hash = $csrfTokenManager->getToken($token)->getValue();
            $user->setValidEmailToken($hash);

            $em->persist($user);
            $em->flush();

            $url = $this->generateUrl("app_auth_email_confirmation", [
                "username" => $user->getUsername(),
                "token" => $token
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject("[P6] Bienvenue sur Snowtricks - Veuillez confirmer votre adresse email")
                ->htmlTemplate("emails/register.html.twig")
                ->context([
                    "username" => $user->getUsername(),
                    "url" => $url
                ]);

            $mailer->send($email);

            $this->addFlash("success",
                "Votre compte a bien été créé, veuillez valider votre adresse email afin de vous connecter
                sur le site et ainsi profiter de toutes les fonctionnalités de Snowtricks."
            );

            return $this->redirectToRoute("home");
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirmation email with token
     * or refresh it
     *
     * @param string|null $username
     * @param string|null $token
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param CsrfTokenManagerInterface $csrfTokenManager
     *
     * @return Response
     *
     * @throws TransportExceptionInterface
     */
    #[Route(
        '/auth/email-confirmation/{username}_{token}',
        name: 'app_auth_email_confirmation',
        requirements: ["username" => "[a-zA-Z0-9]+", "token" => "(tk_){1}[a-z0-9]+"],
        methods: ["GET"]
    )]
    public function emailConfirmation(
        ?string $username,
        ?string $token,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        $user = null;

        if ($username) {
            $user = $em->getRepository(User::class)->findOneBy([
                "username" => $username
            ]);
        }

        if (!$user) {
            throw $this->createNotFoundException("La page n'a pas été trouvée.");
        }

        if (
            new \DateTimeImmutable("now") < $user->getValidEmailExpiredAt() &&
            $csrfTokenManager->isTokenValid(new CsrfToken($token, $user->getValidEmailToken()))
        ) {
            $user
                ->setStatus(true)
                ->setValidEmailToken(null)
                ->setValidEmailExpiredAt(null);
            $em->flush();

            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject("[P6] Snowtricks - Votre compte est activé !")
                ->htmlTemplate("emails/confirm-email.html.twig",)
                ->context([
                    "username" => $user->getUsername()
                ]);

            $mailer->send($email);

            $this->addFlash(
                "success",
                "Votre compte a bien été activé, vous pouvez vous connecter au site et profiter
                de toutes les fonctionnalités de Snowtricks."
            );

            return $this->redirectToRoute("home");
        }

        $this->addFlash(
            "warning",
            "Le lien de confirmation n'est plus valide, suivez la procédure de récupération d'un nouveau
            lien d'activation pour votre compte."
        );

        return $this->redirectToRoute("app_auth_email_refresh_link");
    }

    /**
     * Send new email with refresh link
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param CsrfTokenManagerInterface $csrfTokenManager
     *
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route(
        '/auth/email-refresh-link',
        name: 'app_auth_email_refresh_link',
        methods: ["GET", "POST"]
    )]
    public function emailRefreshLink(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        $form = $this->createForm(RefreshConfirmEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $em->getRepository(User::class)->findOneBy([
                "email" => $form->get('email')->getData()
            ]);

            if ($user) {
                $token = uniqid("tk_");
                $hash = $csrfTokenManager->getToken($token)->getValue();

                $user
                    ->setValidEmailToken($hash)
                    ->setValidEmailExpiredAt(new \DateTimeImmutable("+5 minutes"));

                $em->flush();

                $url = $this->generateUrl("app_auth_email_confirmation", [
                    "username" => $user->getUsername(),
                    "token" => $token
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject("[P6] Snowtricks - Lien de confirmation de votre compte expiré !")
                    ->htmlTemplate("emails/refresh-link-confirm-email.html.twig")
                    ->context([
                        "username" => $user->getUsername(),
                        "url" => $url
                    ]);

                $mailer->send($email);
            }

            $this->addFlash("success", "Votre lien de confirmation a bien été envoyé.");

            return $this->redirectToRoute("home");
        }

        return $this->render('auth/email-refresh-confirmation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
