<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AlbumController extends AbstractController
{
    #[Route('/album/{id}', name: 'app_album_item')]
    public function item($id, AlbumRepository $albumRepository, TrackRepository $trackRepository): Response
    {
        $album = $albumRepository->find($id);
        if ($album === null) {
            return $this->redirectToRoute('app_home');
        }
        if (count($album->getTracks()) > 0) {
            $duration = (int)$trackRepository->findTotalDuration($album);
        } else {
            $duration = 0;
        }

        return $this->render('album/item.html.twig', [
            'album' => $album,
            'duration' => $duration
        ]);
    }

    #[Route('/albumAdd/{id}', name: 'app_album_add')]
    public function add(
        EntityManagerInterface $entityManager,
        Request $request,
        $id,
        ArtistRepository $artistRepository,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $imgDirectory
    ): Response {
        $album = new Album();
        $artist = $artistRepository->find($id);
        if ($artist === null) {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setCreatedAt(new \DateTimeImmutable());
            $album->setArtist($artist);
            $img = $form->get('poster')->getData();

            if ($img) {
                $ogFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($ogFilename);
                $newFilename = 'uploads/' . $safeFilename . '-' . uniqid() . '.' . $img->guessExtension();

                try {
                    $img->move($imgDirectory, $newFilename);
                } catch (FileException $e) {
                }
                $album->setPoster($newFilename);
            }
            $entityManager->persist($album);
            $entityManager->flush();
            return $this->redirectToRoute("app_artist_item", ['id' => $id]);
        }
        return $this->render('album/add.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist
        ]);
    }

    #[Route('/albumEdit/{id}', name: 'app_album_edit')]
    public function edit(
        EntityManagerInterface $entityManager,
        Request $request,
        $id,
        AlbumRepository $albumRepository,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $imgDirectory
    ): Response {
        $album = $albumRepository->find($id);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $album->setCreatedAt(new \DateTimeImmutable());
            $img = $form->get('poster')->getData();

            if ($img) {
                $ogFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($ogFilename);
                $newFilename = 'uploads/' . $safeFilename . '-' . uniqid() . '.' . $img->guessExtension();

                try {
                    $img->move($imgDirectory, $newFilename);
                } catch (FileException $e) {
                }
                $album->setPoster($newFilename);
            }
            $entityManager->persist($album);
            $entityManager->flush();
            return $this->redirectToRoute("app_artist_item", ['id' => $album->getArtist()->getId()]);
        }
        return $this->render('album/edit.html.twig', [
            'form' => $form->createView(),
            'album' => $album
        ]);
    }
}
