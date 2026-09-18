<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FavoriteController extends AbstractController
{
    #[Route('/favorite/{id}', name: 'app_favorite')]
    public function item($id, TrackRepository $trackRepository, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManagerInterface, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute("app_login");
        }
        $track = $trackRepository->find($id);
        $user = $this->getUser();
        $favorites = $user->getFavorites();
        $favTracks = [];
        foreach ($favorites as $favorite) {
            array_push($favTracks, $favorite->getTrack());
        }
        if (in_array($track, $favTracks)) {
            $favorite = $favoriteRepository->findOneBy(['user' => $user, 'track' => $track]);
            $entityManagerInterface->remove($favorite);
            $entityManagerInterface->flush();
            return $this->redirectToRoute("app_profil", ['id' => $user->getId()]);
        } else {
            $favorite = new Favorite();
            $favorite->setCreatedAt(new \DateTimeImmutable());
            $favorite->setTrack($track);
            $favorite->setUser($user);
            $entityManagerInterface->persist($favorite);
            $entityManagerInterface->flush();
            return $this->redirectToRoute("app_profil", ['id' => $user->getId()]);
        }
        return $this->render('favorite/item.html.twig', []);
    }
}
