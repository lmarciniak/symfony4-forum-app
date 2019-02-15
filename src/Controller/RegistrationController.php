<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function registration(
        AuthorizationCheckerInterface $authChecker,
        Request $request, 
        ValidatorInterface $validator, 
        UserPasswordEncoderInterface $encoder
    ):Response {
        if ($authChecker->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('You have to be logged out');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorsString = (string)$errors;
                return new Response($errorsString);
            } else {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
        }
        
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView()
        ]);
        
    }
}
