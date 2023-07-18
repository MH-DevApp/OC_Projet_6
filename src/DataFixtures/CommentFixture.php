<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class CommentFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $dataComments = json_decode(file_get_contents(__DIR__ . "/data/data-comments.json"), true);

        foreach ($dataComments as $dataComment) {
            $offset = array_rand($manager->getRepository(User::class)->findAll());
            $offset = $offset < 5 ? 0 : $offset -5;
            $users = $manager->getRepository(User::class)->findBy([], null, 5, $offset);

            for ($i = 0; $i < 10; $i++) {
                $slug = $dataComment["slug"]. ($i > 0 ? ("-".$i) : "");
                $trick = $manager->getRepository(Trick::class)->findOneBy([
                    "slug" => $slug
                ]);

                foreach ($dataComment["comments"] as $index => $comment) {
                    $createdAt = new \DateTime($trick->getCreatedAt()->format("Y-m-d H:i:s"));
                    $createdAt->add(new \DateInterval("P".$index."D"));
                    $commentEntity = new Comment();
                    $commentEntity
                        ->setAuthor($users[$comment["user"]])
                        ->setTrick($trick)
                        ->setContent($comment["message"])
                        ->setCreatedAt(new \DateTimeImmutable($createdAt->format("Y-m-d H:i:s")));

                    $manager->persist($commentEntity);
                }

            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            GroupTrickFixture::class,
            TrickFixture::class
        ];
    }
}
