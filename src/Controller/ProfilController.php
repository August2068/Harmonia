<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'app_profil')]
    public function item($id, UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute("app_login");
        }
        $user = $userRepository->find($id);
        if ($user === null) {
            return $this->redirectToRoute("app_login");
        }
        return $this->render('profil/item.html.twig', [
            'user' => $user,
        ]);
    }
}
