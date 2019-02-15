<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Topic;
use App\Entity\Tag;
use App\Form\PostType;
use App\Form\TopicType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface; // to use in delete method
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TopicController extends AbstractController
{
    /**
     * @Route("/{id<\d+>}/{topicName}", name="topic_show")
     */
    public function show($id, $topicName, Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Topic::class);
        $topic = $repository->find($id);
        if (!$topic) {
            throw $this->createNotFoundException('The topic does not exist');
        }
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $em = $this->getDoctrine()->getManager();
            $post->setTopicId($topic);
            $post->setUserId($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $em->persist($post);
            $topic->setLastReply($post->getCreatedAt());
            $em->flush();
        }
        return $this->render('topic/index.html.twig', [
            'topic' => $topic,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @Route("Topics/{categoryName}/New", name="topic_add")
     */
    public function add($categoryName, Request $request, ValidatorInterface $validator): Response
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['categoryName' => $categoryName]);
        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }
        if (!$this->getUser()) {
            return $this->redirectToRoute('category_show', [
                'categoryName' => $category->getCategoryName()
            ]);
        }
        $topic = new Topic();
        $tag = new Tag();
        $topic->addTag($tag);
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($topic->getTags());
            if (count($errors) > 0) {
                $errorsString = (string)$errors;

                return new Response($errorsString);
            }
            $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
            foreach ($topic->getTags() as $tag) {
                $existingTag = $tagRepository->findOneBy(['tagName' => $tag->getTagName()]);
                if ($existingTag) {
                    $topic->removeTag($tag);
                    $topic->addTag($existingTag);
                }
            }
            $topic->setCategoryId($category);
            $topic->setUserId($this->getUser());
            $topic->setCreatedAt(new \DateTime());
            $topic->setLastReply(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($topic);
            $em->flush();

            return $this->redirectToRoute('topic_show', [
                'id'        => $topic->getId(),
                'topicName' => str_replace(' ', '-', $topic->getTopicName())
            ]);
        } 

        return $this->render('topic/addTopic.html.twig', [
            'form'     => $form->createView(),
            'category' => $category 
        ]);
    }

    /**
     * @Route("/{id<\d+>}/{topicName}/edit", name="topic_edit")
     */
    public function edit($id, $topicName, Request $request, AuthorizationCheckerInterface $authChecker): Response
    {
        $repository = $this->getDoctrine()->getRepository(Topic::class);
        $topic = $repository->find($id);
        if (!$topic) {
            throw $this->createNotFoundException('The topic does not exist');
        }
        if (!$this->getUser() || $topic->getUserId()->getId() !== $this->getUser()->getId()) {

            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'topicName' => str_replace(' ', '-', $topic->getTopicName())
            ]);
        }
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
            foreach ($topic->getTags() as $tag) {
                $existingTag = $tagRepository->findOneBy(['tagName' => $tag->getTagName()]);
                if ($existingTag) {
                    $topic->removeTag($tag);        
                    $topic->addTag($existingTag);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'topicName' => str_replace(' ', '-', $topic->getTopicName())
            ]);
        }
        
        return $this->render('topic/editTopic.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
