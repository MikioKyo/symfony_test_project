<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

Class LuckyController extends AbstractController
{
    /*
     * @Route("/lucky/number",name="lucky_number")
     */
    public function number(): Response
    {
        $number = random_int(0,100);

        return $this->render('lucky.html.twig',[
            'number' => $number,
        ]);
    }
}