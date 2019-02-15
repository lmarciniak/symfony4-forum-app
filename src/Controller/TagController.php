<?php

namespace App\Controller;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @Route("/tag/{tagName}", name="tag_show")
     */
    public function show($tagName)
    {
        $repository = $this->getDoctrine()->getRepository(Tag::class);
        
        return $this->render('tag/index.html.twig', [
            'tag' => $repository->findOneBy(['tagName' => $tagName])
        ]);
    }
}
