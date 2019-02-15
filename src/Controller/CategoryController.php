<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository->findAll();
        
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/Topics/{categoryName}/{page<\d+>?1}", name="category_show")
     */
    public function show($categoryName, $page)
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $category = $repository->findOneBy(['categoryName' => $categoryName]);
        if (!$category) {
            throw $this->createNotFoundException(
                $categoryName . ' category does not exist'
            );
        }
        $repository = $this->getDoctrine()->getRepository(Topic::class);
        $topicsPerPage = 5;
        $topics = $repository->findByCategoryName($categoryName, $page, $topicsPerPage);

        return $this->render('category/show.html.twig', [
            'category'    => $category,
            'topics'      => $topics,
            'currentPage' => $page,
            'lastPage'    => ceil(count($category->getTopics()) / $topicsPerPage)
        ]);
    }
}
