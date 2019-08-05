<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i=0; $i < 10; $i++){
            $post = new Post();
            $post->setContent($faker->paragraph);
            /** @var User $user */
            $user = $this->getReference(User::class . '_' . $faker->numberBetween(0, 9));
            $post->setUser($user);
            if ($faker->boolean(70)){
                $post->setValidated(true);
            }
            $manager->persist($post);
            $this->addReference(Post::class . '_' . $i, $post);
        }

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies() {
        return [UserFixtures::class];
    }
}
