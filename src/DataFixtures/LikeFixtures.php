<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class LikeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i=0; $i < 50; $i++) {
            $like = new Like();
            /** @var Post $post */
            $post = $this->getReference(Post::class . '_' . $faker->numberBetween(0, 29));
            $like->setPost($post);
            /** @var Post $post */
            $user = $this->getReference(User::class . '_' . $faker->numberBetween(0, 29));
            $like->setUser($user);
            $manager->persist($like);
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
        return [PostFixtures::class];
    }
}
