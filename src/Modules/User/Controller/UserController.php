<?php

namespace Modules\User\Controller;

use Modules\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        return $this->render('@User/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $user = new User();
                $user->setFirstName($request->request->get('firstName'));
                $user->setLastName($request->request->get('lastName'));
                $user->setEmail($request->request->get('email'));
                $user->setRole($request->request->get('role'));
                $user->setIsActive($request->request->getBoolean('isActive', true));
                
                $plainPassword = $request->request->get('password');
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                } else {
                    $this->addFlash('error', 'Le mot de passe est obligatoire.');
                    return $this->render('@User/user/new.html.twig');
                }
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Utilisateur créé avec succès.');
                return $this->redirectToRoute('admin_users_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            }
        }
        
        return $this->render('@User/user/new.html.twig');
    }

    #[Route('/{id}', name: 'admin_users_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('@User/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $user->setFirstName($request->request->get('firstName'));
                $user->setLastName($request->request->get('lastName'));
                $user->setEmail($request->request->get('email'));
                $user->setRole($request->request->get('role'));
                $user->setIsActive($request->request->getBoolean('isActive', true));
                $user->setUpdatedAt(new \DateTimeImmutable());
                
                $plainPassword = $request->request->get('password');
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
                
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
                return $this->redirectToRoute('admin_users_show', ['id' => $user->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification de l\'utilisateur : ' . $e->getMessage());
            }
        }
        
        return $this->render('@User/user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Utilisateur supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_users_index');
    }
}
