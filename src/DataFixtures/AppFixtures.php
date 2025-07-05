<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Space;
use App\Entity\TypeSpace;
use App\Entity\User;
use App\Factory\SpaceFactory;
use App\Factory\TypeSpaceFactory;
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

        //Tipos de Espaciones
        $types = [
            ['Auditorio', 'Espacio para presentaciones, seminarios y eventos principales.'],
            ['Sala de Conferencias', 'Ideal para reuniones, presentaciones y talleres.'],
            ['Restaurante', 'Área destinada para almuerzos, cenas o eventos sociales.'],
            ['Sala de Talleres', 'Espacio equipado para actividades prácticas y educativas.'],
            ['Área de Networking', 'Zona abierta para interacción entre participantes.'],
            ['Terraza', 'Espacio al aire libre para eventos sociales o pausas activas.'],
        ];

        $typeSpaces = [];

        foreach ($types as [$name, $description]) {
            $type = new TypeSpace();
            $type->setName($name);
            $type->setDescription($description);
            $manager->persist($type);
            $typeSpaces[$name] = $type;
        }
        //ESpacios
        $spaces = [
            ['Auditorio Central', 'Auditorio principal con capacidad para grandes eventos.', 100, 'auditorium.jpg', '08:00-18:00', 'Auditorio'],
            ['Sala Omega', 'Sala equipada con tecnología para presentaciones interactivas.', 30, 'omega.jpg', '09:00-17:00', 'Sala de Conferencias'],
            ['Comedor Ejecutivo', 'Restaurante exclusivo para almuerzos ejecutivos.', 50, 'comedor.jpg', '12:00-15:00', 'Restaurante'],
            ['Sala Creativa', 'Espacio colaborativo para talleres y dinámicas grupales.', 20, 'creativa.jpg', '10:00-16:00', 'Sala de Talleres'],
            ['Terraza Norte', 'Espacio al aire libre ideal para coffee breaks y networking.', 40, 'terraza.jpg', '09:00-18:00', 'Terraza'],
            ['Sala Beta', 'Espacio cerrado para charlas técnicas y capacitaciones.', 25, 'beta.jpg', '08:00-16:00', 'Sala de Conferencias'],
            ['Zona Café', 'Área para reuniones informales y conexión entre participantes.', 15, 'cafe.jpg', '08:00-18:00', 'Área de Networking'],
            ['Auditorio Alterno', 'Auditorio secundario para actividades simultáneas.', 80, 'auditorio2.jpg', '09:00-17:00', 'Auditorio'],
            ['Sala Maker', 'Taller equipado con herramientas para prototipado rápido.', 15, 'maker.jpg', '10:00-18:00', 'Sala de Talleres'],
        ];

        foreach ($spaces as [$name, $description, $capacity, $photo, $schedule, $typeName]) {
            $space = new Space();
            $space->setName($name);
            $space->setDescription($description);
            $space->setCapacity($capacity);
            $space->setPhotos($this->generarPhotosJson());
            $space->setSchedules($this->generarScheduleJson(3));
            $space->setTypeSpace($typeSpaces[$typeName]);
            $manager->persist($space);
        }

        TypeSpaceFactory::createMany(5);
        SpaceFactory::createMany(10);

        $manager->flush();
    }

    function generarScheduleJson(int $cantidad = 3): string
    {
        $schedules = [];

        for ($i = 0; $i < $cantidad; $i++) {

            $timestamp = strtotime('+' . rand(0, 10) . ' days');
            $date = date(DATE_ATOM, $timestamp); // Formato ISO 8601

            // Hora de inicio aleatoria
            $startHour = str_pad((string) rand(6, 20), 2, '0', STR_PAD_LEFT);
            $startMin = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);

            $endHour = str_pad((string) rand((int)$startHour + 1, 22), 2, '0', STR_PAD_LEFT);
            $endMin = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);

            $schedules[] = [
                'date' => $date,
                'startTime' => "$startHour:$startMin",
                'endTime' => "$endHour:$endMin",
                'available' => false
            ];
        }
        return json_encode($schedules);
    }

    function generarPhotosJson(int $cantidad = 3): string
    {
        $urls = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $id = rand(1, 1000);
            $urls[] = "https://picsum.photos/id/$id/640/480";
        }

        return json_encode($urls);
    }
}
