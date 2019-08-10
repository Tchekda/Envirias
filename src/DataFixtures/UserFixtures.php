<?php

namespace App\DataFixtures;

use App\Entity\Badge;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 30; $i++){
            $user = new User();
            $user->setUsername($faker->userName)
                 ->setTotalScore($faker->numberBetween(0, 100))
                 ->setMonthScore($faker->numberBetween(0, 100))
                 ->setEmail($faker->email)
                 ->setPassword($this->passwordEncoder->encodePassword($user, $faker->password))
                 ->setValidated(true);
            if ($faker->boolean(50)){
                $user->addBadge($this->getReference(Badge::class . '_' . "certified"));
            }elseif ($faker->boolean(50)){
                $user->addBadge($this->getReference(Badge::class . '_' . "donnator"));
            }
            $manager->persist($user);
            $this->addReference(sprintf('%s_%d', User::class, $i), $user);
        }
        $tchekda = new User();
        $tchekda->setEmail('tchekda@gmail.com')
                ->setUsername('Tchekda')
                ->setPassword($this->passwordEncoder->encodePassword($tchekda, 'HelloWorld'))
                ->setWebsite('tchekda.fr')
                ->setRoles(['ROLE_ADMIN'])
                ->addBadge($this->getReference(Badge::class . '_' . "admin"))
                ->addBadge($this->getReference(Badge::class . '_' . "admin"))
                ->addBadge($this->getReference(Badge::class . '_' . "admin"))
                ->setValidated(true);

        $manager->persist($tchekda);

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies() {
        return [BadgeFixtures::class];
    }
}
