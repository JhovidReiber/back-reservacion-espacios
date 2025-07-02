<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Json;

class AuthController extends AbstractController
{
    private $passwordHasher;
    private $entityManager;
    private $jwtManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());

        // Validamos si el username ya existe
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data->username]);
        if ($existingUser) {
            return new JsonResponse(['message' => "El username {$data->username} ya existe, por favor ingrese otro"], JsonResponse::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setName($data->name);
        $user->setUsername($data->username);
        $user->setPassword($data->password, $this->passwordHasher); // Usamos $this->passwordHasher, para codificar la contraseña
        $user->setState(true);
        //Buscamos el rol que vamos a asignar
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $data->role]);
        $user->setRole($role);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Usuario Registrado correctamente'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data->username]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data->password)) return new JsonResponse(['message' => 'Credenciales Invalidas'], JsonResponse::HTTP_UNAUTHORIZED);

        $token = $this->jwtManager->create($user); // Generamos el token

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/check-username', name: 'api_check_username', methods: ['POST'])]
    public function checkUserNameExists(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data->username]);
        return new JsonResponse(['exists' => $user !== null]);
    }
}