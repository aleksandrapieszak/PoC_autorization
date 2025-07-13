<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword(
            $this->hasher->hashPassword($user, 'adminpass')
        );

        $user1 = new User();
        $user1->setEmail('user@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword(
            $this->hasher->hashPassword($user1, 'adminpass')
        );

        $manager->persist($user);
        $manager->persist($user1);
        $manager->flush();
    }
}
