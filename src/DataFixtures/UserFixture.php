<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $dataUsers = json_decode(file_get_contents(__DIR__ . '/data/data-user.json'), true);

        foreach ($dataUsers as $index => $dataUser) {
            $filenamePicture = null;
            if (file_exists(__DIR__ . "/data/images/profile/".$index.".png")) {
                $picture = new File(
                    __DIR__ . "/data/images/profile/".$index.".png"
                );
                $pathSavePicture = __DIR__ . "/../../public/assets/images/profiles/";
                $filenamePicture = bin2hex(random_bytes(10)) . "." . $picture->guessExtension() ?? "bin";;
                copy(
                    $picture->getPath() . "/" . $picture->getFilename(),
                    $pathSavePicture . $filenamePicture
                );
            }

            $user = new User();
            $user
                ->setUsername($dataUser['username'])
                ->setEmail($dataUser['email'])
                ->setCreatedAt(new \DateTimeImmutable("-".rand(0, 365)." days"))
                ->setStatus(true)
                ->setPicture($filenamePicture)
                ->setPassword($this->passwordHasher->hashPassword(
                    $user,
                    "123456"
                ));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
