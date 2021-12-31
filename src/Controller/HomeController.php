<?php
namespace App\Controller;

use App\Entity\Image;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use finfo;
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
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (!$uploaded_file){
            return $this->render('upload_fail.html.twig');
        }
        if(array_search($finfo->file($uploaded_file),
                    array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',),
            true))
        {
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
            return $this->render('upload_success.html.twig');
        }
        else
        {
            return $this->render('upload_fail.html.twig');
        }
    }
    /*
     * @Route("/delete",name="Upload")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        if($request->isXmlHttpRequest()){
            // доп проверка на то, чтобы сильно умные без логина вручную с помощью пост запроса не удалили картинки
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
                $image = $doctrine->getRepository(Image::class)->findOneBy(['directory' => $_POST['imagesrc']]);
                $entityManager->remove($image);
                $entityManager->flush();
                unlink($this->getParameter('kernel.project_dir') . '/public' . $_POST['imagesrc']);
                echo 'File deleted.';
                die();
            }
        }
    }
}