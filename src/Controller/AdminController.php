<?php
// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/api/admin', name: 'api_admin', methods: ['GET'])]
    #only for users with ROLE_ADMIN
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        return $this->json(['message' => 'Hello, admin!']);
    }

    #[Route('/api/roles', name: 'api_role', methods: ['GET'])]
    #only for authenticated users
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #CurrentUser attribute injects the current user into the method
    #CurrentUser is a Symfony attribute that allows you to access the currently authenticated user
    public function role(#[CurrentUser] User $user): JsonResponse
    {

        $roles = $user->getRoles();

        return $this->json([
            'message' => 'Hello, ' . $user->getEmail(),
            'roles' => $roles,
        ]);
    }
}
