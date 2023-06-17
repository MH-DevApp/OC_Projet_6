<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileAvatarType;
use App\Form\ProfileInfosType;
use App\Form\ResetPasswordType;
use App\Utils\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileController extends AbstractController
{

    /**
     * Constructor
     *
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    )
    {
    }

    /**
     * Show profile user
     *
     * @param Request $request
     * @return Response
     *
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route("/profile", name: "app_profile", methods: ["GET", "POST"])]
    public function index(
        Request $request
    ): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $formAvatar = $this->createForm(ProfileAvatarType::class);
        $formAvatar->handleRequest($request);

        if ($formAvatar->isSubmitted() && $formAvatar->isValid()) {
            $this->updateProfileAvatar($user, $formAvatar);
            return $this->redirectToRoute("app_profile");
        }

        $email = $user->getEmail();
        $username = $user->getUsername();
        $username[0] = strtoupper($username[0]);

        $defaultInfosData = [
            "email" => $email,
            "username" => $username,
        ];
        $formInfos = $this->createForm(ProfileInfosType::class, $defaultInfosData);
        $formInfos->handleRequest($request);

        if ($formInfos->isSubmitted() && $formInfos->isValid()) {
            if ($this->updateProfileInfos($user, $formInfos)) {
                return $this->redirectToRoute("app_profile");
            }
        }

        $formResetPassword = $this->createForm(ResetPasswordType::class, ["type" => "profile"]);
        $formResetPassword->handleRequest($request);

        if ($formResetPassword->isSubmitted() && $formResetPassword->isValid()) {
            $this->resetPasswordForm(
                $formResetPassword->get('repeatedPassword')->getData(),
                $user
            );

            return $this->redirectToRoute("app_profile");
        }

        return $this->render("profile/index.html.twig", [
            "formAvatar" => $formAvatar->createView(),
            "formInfos" => $formInfos->createView(),
            "formResetPassword" => $formResetPassword->createView(),
        ]);
    }

    /**
     * Delete Avatar
     *
     * @return Response
     *
     */
    #[Route("/profile/avatar/delete", name: "app_profile_avatar_delete", methods: ["POST"])]
    public function deleteAvatar(): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        FileManager::deleteFile(
            $this->getParameter("profile.folder"),
            $user->getPicture()
        );

        $user->setPicture(null);

        $this->em->flush();

        $this->addFlash(
            "success",
            "Votre photo de profil a été supprimé avec succès."
        );

        return $this->redirectToRoute("app_profile");
    }

    /**
     * Update profile avatar
     *
     * @param User $user
     * @param FormInterface $form
     *
     * @throws Exception
     */
    private function updateProfileAvatar(User $user, FormInterface $form): void
    {
        $filename = FileManager::uploadFile(
            $form->get("avatar")->getData(),
            $this->getParameter("profile.folder"),
            $user->getPicture()
        );

        $user->setPicture($filename);

        $this->em->flush();

        $this->addFlash(
            "success",
            "Votre photo de profil a été mise à jour."
        );
    }

    /**
     * Update profile infos
     *
     * @param User $user
     * @param FormInterface $form
     *
     * @return bool
     *
     * @throws TransportExceptionInterface
     */
    private function updateProfileInfos(User $user, FormInterface $form): bool
    {
        $result = true;

        $username = $form->get("username")->getData();
        $email = $form->get("email")->getData();

        if (strtolower($username) !== strtolower($user->getUsername())) {
            if ($this->em->getRepository(User::class)->findOneBy(["username" => $username])) {
                $form->get("username")->addError(new FormError("Ce nom d'utilisateur existe déjà."));
                $result = false;
            }
        }

        if (strtolower($email) !== strtolower($user->getEmail())) {
            if ($this->em->getRepository(User::class)->findOneBy(["email" => $email])) {
                $form->get("email")->addError(new FormError("Cet email existe déjà."));
                $result = false;
            }
        }

        if ($result) {
            if (
                strtolower($username) !== strtolower($user->getUsername()) ||
                strtolower($email) !== strtolower($user->getEmail())
            ) {
                $emailTemplated = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject("[P6] Snowtricks - Modification de vos informations personnelles !")
                    ->htmlTemplate("emails/profile-infos-updated.html.twig")
                    ->context([
                        "newUsername" => $username,
                        "newEmail" => $email
                    ]);
                if (strtolower($email) !== strtolower($user->getEmail())) {
                    $this->mailer->send($emailTemplated);
                }
                $user->setEmail($email);
                $user->setUsername($username);

                $emailTemplated->to($user->getEmail());
                $this->mailer->send($emailTemplated);

                $this->em->flush();

                $this->addFlash(
                    "success",
                    "Vos informations personnelles ont bien été modifiées."
                );
            } else {
                $this->addFlash(
                    "warning",
                    "Aucune modification n'a été effectuée."
                );
            }
        }

        return $result;
    }

    /**
     * Reset Password User
     *
     * @throws TransportExceptionInterface
     */
    private function resetPasswordForm(
        string $newPassword,
        User $user
    ): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $newPassword
            )
        );

        $this->em->flush();

        $url = $this->generateUrl("app_auth_login", referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject("[P6] Snowtricks - Mot de passe réinitialisé !")
            ->htmlTemplate("emails/reset-password-successful.html.twig")
            ->context([
                "username" => $user->getUsername(),
                "url" => $url
            ]);

        $this->mailer->send($email);

        $this->addFlash(
            "success",
            "Votre mot de passe a bien été réinitialisé."
        );
    }

}
