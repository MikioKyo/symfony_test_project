<?php
namespace App\Controller;

use App\Entity\Image;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

Class HomeController extends AbstractController
{
    /*
     * @Route("/",name="Home")
     */
    public function home(ManagerRegistry $doctrine): Response
    {
        $images = $doctrine->getRepository(Image::class)->getAllImages();
        return $this->render('home.html.twig',['images' => $images]);
    }
    /*
     * @Route("/upload",name="Upload")
     */
    public function upload(Request $request, EntityManagerInterface $entityManager)
    {
        $uploaded_file = $request->files->get('image');
        $destination = $this->getParameter('kernel.project_dir').'/public/gallery_uploads';
        $newFileName = uniqid().'.'.$uploaded_file->guessExtension();
        $uploaded_file->move(
            $destination,
            $newFileName,
        );
        $image = new Image();
        $image->setDirectory('/gallery_uploads/'.$newFileName);
        $datetime = new DateTime;
        $image->setAddedAt(DateTimeImmutable::createFromMutable($datetime));
        $entityManager->persist($image);
        $entityManager->flush();
        return $this->render('upload.html.twig');
    }
}