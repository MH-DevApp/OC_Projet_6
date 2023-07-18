<?php

namespace App\DataFixtures;

use App\Entity\GroupTrick;
use App\Entity\MediaTrick;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpFoundation\File\File;

class TrickFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $dataTricks = json_decode(file_get_contents(__DIR__ . '/data/data-tricks.json'), true);

        for ($i = 0; $i < 10; $i++) {
            foreach ($dataTricks as $dataTrick) {
                $user = $users[array_rand($users)];
                $groupTrick = $manager->getRepository(GroupTrick::class)->findOneBy([
                    "name" => $dataTrick['groupTrick']
                ]);

                $trick = new Trick();
                $trick
                    ->setAuthor($user)
                    ->setName($dataTrick['name']. ($i > 0 ? ("-".$i) : ""))
                    ->setSlug($dataTrick['slug']. ($i > 0 ? ("-".$i) : ""))
                    ->setIsPublished(true)
                    ->setDescription($dataTrick['description'])
                    ->setGroupTrick($groupTrick)
                    ->setCreatedAt(new \DateTimeImmutable("-".rand(0, 365)." days"));

                if (isset($dataTrick["featuredMedia"]) && $dataTrick["featuredMedia"]) {
                    $media = new MediaTrick();
                    $media
                        ->setTrick($trick)
                        ->setType("image")
                        ->setAddedAt($trick->getCreatedAt());

                    if (file_exists(
                        __DIR__ .
                        "/data/images/tricks/" .
                        $dataTrick['slug'] . "/" .
                        $dataTrick["featuredMedia"]["filename"]
                    )) {
                        $picture = new File(
                            __DIR__ .
                            "/data/images/tricks/" .
                            $dataTrick['slug'] . "/" .
                            $dataTrick["featuredMedia"]["filename"]
                        );
                        $pathSavePicture = __DIR__ . "/../../public/assets/images/tricks/";
                        $filenamePicture = bin2hex(random_bytes(10)) . "." . $picture->guessExtension() ?? "bin";;
                        copy(
                            $picture->getPath() . "/" . $picture->getFilename(),
                            $pathSavePicture . $filenamePicture
                        );

                        $media->setLink($filenamePicture);
                    }

                    if (isset($dataTrick["featuredMedia"]["sourceName"]) && $dataTrick["featuredMedia"]["sourceName"]) {
                        $media->setSourceName($dataTrick["featuredMedia"]["sourceName"]);
                    }

                    if (isset($dataTrick["featuredMedia"]["sourceLink"]) && $dataTrick["featuredMedia"]["sourceLink"]) {
                        $media->setSourceLink($dataTrick["featuredMedia"]["sourceLink"]);
                    }

                    $trick->setPictureFeatured($media);
                }

                if (isset($dataTrick["medias"]) && $dataTrick["medias"]) {
                    foreach ($dataTrick["medias"] as $mediaTrick) {
                        $media = new MediaTrick();
                        $media
                            ->setTrick($trick)
                            ->setType($mediaTrick["type"])
                            ->setAddedAt($trick->getCreatedAt());

                        if ($mediaTrick["type"] === "image") {
                            if (file_exists(
                                __DIR__ .
                                "/data/images/tricks/" .
                                $dataTrick['slug'] . "/" .
                                $mediaTrick["filename"]
                            )) {
                                $picture = new File(
                                    __DIR__ .
                                    "/data/images/tricks/" .
                                    $dataTrick['slug'] . "/" .
                                    $mediaTrick["filename"]
                                );
                                $pathSavePicture = __DIR__ . "/../../public/assets/images/tricks/";
                                $filenamePicture = bin2hex(random_bytes(10)) . "." . $picture->guessExtension() ?? "bin";;
                                copy(
                                    $picture->getPath() . "/" . $picture->getFilename(),
                                    $pathSavePicture . $filenamePicture
                                );

                                $media->setLink($filenamePicture);
                            }

                            if (isset($mediaTrick["sourceName"]) && $mediaTrick["sourceName"]) {
                                $media->setSourceName($mediaTrick["sourceName"]);
                            }

                            if (isset($mediaTrick["sourceLink"]) && $mediaTrick["sourceLink"]) {
                                $media->setSourceLink($mediaTrick["sourceLink"]);
                            }
                        } else if ($mediaTrick["type"] === "video") {
                            $media->setLink($mediaTrick["link"]);
                        }

                        $trick->addMediaTrick($media);
                    }
                }
                $manager->persist($trick);
            }
            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            GroupTrickFixture::class
        ];
    }
}
