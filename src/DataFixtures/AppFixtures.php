<?php

namespace App\DataFixtures;

use App\Entity\Professor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        #[Autowire('%kernel.environment%')] private string $environment,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        if ($this->environment === 'prod') {
            return;
        }

        $admin = new Professor();
        $admin->setEmail('admin@lycee-faure.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('DDFPT');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));

        $manager->persist($admin);
        $manager->flush();
    }
}
