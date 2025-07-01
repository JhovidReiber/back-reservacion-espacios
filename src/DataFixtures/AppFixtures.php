<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Space;
use App\Entity\TypeSpace;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        //Creacion de Roles
        $adminRole = new Role;
        $adminRole->setName('ROL_ADMIN');
        $adminRole->setDescription('Usuario con control total');
        $manager->persist($adminRole);

        $userRole = new Role;
        $userRole->setName('ROL_USUARIO');
        $userRole->setDescription('Usuario con permisos generales');
        $manager->persist($userRole);

        //Creacion Usuarios
        $adminUser = new User;
        $adminUser->setName('Administador');
        $adminUser->setUsername('admin');
        $adminUser->setPassword('admin', $this->passwordHasher);
        $adminUser->setRole($adminRole);
        $adminUser->setState(true);
        $manager->persist($adminUser);

        $user = new User;
        $user->setName('David R');
        $user->setUsername('davidr');
        $user->setPassword('1234', $this->passwordHasher);
        $user->setRole($userRole);
        $user->setState(true);
        $manager->persist($user);

        //Creacion de tipos de espacios
        $auditorium = new TypeSpace();
        $auditorium->setName('Auditorio');
        $auditorium->setDescription('Espacio para presentaciones y conferencias principales.');
        $manager->persist($auditorium);

        $conferenceRoom = new TypeSpace();
        $conferenceRoom->setName('Sala de Conferencias');
        $conferenceRoom->setDescription('Espacio para sesiones de conferencias.');
        $manager->persist($conferenceRoom);

        $restaurant = new TypeSpace();
        $restaurant->setName('Restaurante');
        $restaurant->setDescription('Espacio de convivencia');
        $manager->persist($restaurant);

        //Creacion de Espacios
        $space1 = new Space;
        $space1->setTypeSpace($auditorium);
        $space1->setName('Presentacion de Prueba');
        $space1->setDescription('Presentacion de Prueba');
        $space1->setCapacity(10);
        $space1->setPhotos("N/A");
        $manager->persist($space1);

        $space2 = new Space;
        $space2->setTypeSpace($conferenceRoom);
        $space2->setName('Conferencia de Prueba');
        $space2->setDescription('Conferencia de Prueba');
        $space2->setCapacity(5);
        $space2->setPhotos("N/A");
        $manager->persist($space2);

        $space3 = new Space;
        $space3->setTypeSpace($restaurant);
        $space3->setName('Almuerzo de bienvenida de Prueba');
        $space3->setDescription('Almuerzo de Prueba');
        $space3->setCapacity(30);
        $space3->setPhotos("N/A");
        $manager->persist($space3);

        $manager->flush();
    }
}
