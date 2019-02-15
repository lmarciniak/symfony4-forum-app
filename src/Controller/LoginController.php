<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthorizationCheckerInterface $authChecker, AuthenticationUtils $utils): Response
    {
        if ($authChecker->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('You have to be logged out');
        }

        $lastUsername = $utils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'lastUsername'  => $utils->getLastUserName(),
            'error'         => $utils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {

    }
}
