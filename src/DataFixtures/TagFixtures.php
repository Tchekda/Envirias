<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TagFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            /** @var Post $post */
            $post = $this->getReference(Post::class . '_' . $faker->numberBetween(0, 9));
            $tag = new Tag();
            $name = $faker->word;
            $tag->setName($name);
            $tag->setCanonical(strtolower($name));
            $post->addTag($tag);
            $manager->persist($tag);
            $this->addReference(Tag::class . '_' . $i, $tag);
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
