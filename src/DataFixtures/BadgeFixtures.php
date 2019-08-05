<?php

namespace App\DataFixtures;

use App\Entity\Badge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class BadgeFixtures extends Fixture {
    public function load(ObjectManager $manager) {
        $badge_verified = new Badge();
        $badge_verified
            ->setIcon("verified_user")
            ->setTitle("Certifié")
            ->setColor("green")
            ->setBgColor("transparent")
            ->setCost(90)
        ;
        $manager->persist($badge_verified);
        $this->addReference(Badge::class . '_' . "certified", $badge_verified);


        $badge_donnator = new Badge();
        $badge_donnator
            ->setIcon("euro_symbol")
            ->setTitle("Donnateur")
            ->setColor("blue")
            ->setBgColor("transparent")
            ->setCost(80)
        ;
        $manager->persist($badge_donnator);
        $this->addReference(Badge::class . '_' . "donnator", $badge_donnator);


        $badge_admin = new Badge();
        $badge_admin
            ->setIcon("grade")
            ->setTitle("Administrateur")
            ->setColor("red")
            ->setBgColor("transparent")
            ->setCost(100)
        ;
        $manager->persist($badge_admin);
        $this->addReference(Badge::class . '_' . "admin", $badge_admin);

        foreach (range(1, 9) as $i) {

            $badge = new Badge();
            $badge
                ->setIcon("filter_" . $i)
                ->setTitle($i . "00 Posts")
                ->setColor("green")
                ->setBgColor("transparent")
                ->setCost($i * 10)
            ;
            $manager->persist($badge);
            $this->addReference(Badge::class . '_' . $i . "00", $badge);


        }

        $manager->flush();
    }
}
