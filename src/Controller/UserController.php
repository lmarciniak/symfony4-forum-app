<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{username}", name="user_show")
     */
    public function show($username)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        
        return $this->render('user/index.html.twig', [
            'user' => $repository->findOneBy(['username' => $username])
        ]);
    }
}
