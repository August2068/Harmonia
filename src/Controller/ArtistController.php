<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistController extends AbstractController
{
    #[Route('/artist/{id}', name: 'app_artist_item')]
    public function item($id, ArtistRepository $artistRepository): Response
    {
        $artist = $artistRepository->find($id);
        if ($artist === null) {
            return $this->redirectToRoute("app_home");
        }
        return $this->render('artist/item.html.twig', [
            'artist' => $artist,
        ]);
    }

    #[Route('/artist', name: 'app_artist')]
    public function index(ArtistRepository $artistRepository): Response
    {
        $artists = $artistRepository->findAll();
        $artist = $artists[0];
        return $this->render('artist/index.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/artistAdd', name: 'app_artist_add')]
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($artist);
            $entityManager->flush();
            return $this->redirectToRoute("app_artist");
        }
        return $this->render('artist/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/artistEdit/{id}', name: 'app_artist_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, $id, ArtistRepository $artistRepository): Response
    {
        $artist = $artistRepository->find($id);
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($artist);
            $entityManager->flush();
            return $this->redirectToRoute("app_artist");
        }
        return $this->render('artist/edit.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist
        ]);
    }
}
