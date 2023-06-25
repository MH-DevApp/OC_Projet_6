<?php

namespace App\DataFixtures;

use App\Entity\GroupTrick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupTrickFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $listGroupsTrick = [
            "Grabs",
            "Rotation",
            "Flip",
            "Slide/Jibs"
        ];

        foreach ($listGroupsTrick as $group) {
            $groupTrick = new GroupTrick();
            $groupTrick->setName($group);

            $manager->persist($groupTrick);
        }

        $manager->flush();
    }
}
