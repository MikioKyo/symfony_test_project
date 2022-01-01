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
        if($request->isXmlHttpRequest()) {
            // доп проверка на то, чтобы сильно умные без логина вручную с помощью пост запроса не добавили картинки
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
                $uploaded_file = $request->files->get('img');

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (!$uploaded_file) {
                    echo 'не вышло';
                    die();
                }
                if (array_search($finfo->file($uploaded_file),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',),
                    true)) {
                    $destination = $this->getParameter('kernel.project_dir') . '/public/gallery_uploads';
                    $newFileName = uniqid() . '.' . $uploaded_file->guessExtension();
                    $uploaded_file->move(
                        $destination,
                        $newFileName,
                    );
                    $image = new Image();
                    $image->setDirectory('/gallery_uploads/' . $newFileName);
                    $datetime = new DateTime;
                    $image->setAddedAt(DateTimeImmutable::createFromMutable($datetime));
                    $entityManager->persist($image);
                    $entityManager->flush();
                    echo $image->getDirectory();
                    die();
                } else {
                    echo 'не вышло';
                    die();
                }
            }
        }
    }
    /*
     * @Route("/delete",name="Delete")
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
    /*
     * @Route("/update",name="Update")
     */
    public function update(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        if($request->isXmlHttpRequest()) {
            // доп проверка на то, чтобы сильно умные без логина вручную с помощью пост запроса не удалили картинки
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
                $uploaded_file = $request->files->get('img');
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (!$uploaded_file) {
                    echo 'не вышло';
                    die();
                }
                if (array_search($finfo->file($uploaded_file),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',),
                    true)) {
                    $destination = $this->getParameter('kernel.project_dir') . '/public/gallery_uploads';
                    $newFileName = uniqid() . '.' . $uploaded_file->guessExtension();
                    $uploaded_file->move(
                        $destination,
                        $newFileName,
                    );
                    $old_image = $doctrine->getRepository(Image::class)->findOneBy(['directory' => $_POST['img_src']]);
                    $new_image = new Image();
                    $new_image->setDirectory('/gallery_uploads/'.$newFileName);
                    $datetime = new DateTime;
                    $new_image->setAddedAt(DateTimeImmutable::createFromMutable($datetime));
                    $entityManager->persist($new_image);
                    $entityManager->remove($old_image);
                    $entityManager->flush();
                    unlink($this->getParameter('kernel.project_dir') . '/public' . $old_image->getDirectory());
                    echo $new_image->getDirectory();
                    die();
                } else {
                    echo 'не вышло';
                    die();
                }
            }
        }
    }
}