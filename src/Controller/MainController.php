<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PostRepository $repository): Response
    {
        $posts = $repository->findBy([], ['published_at' => 'DESC']);

        return $this->render('main/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: [
        'id' => Requirement::DIGITS
    ])]
    public function show(Post $post): Response
    {
        return $this->render('main/show.html.twig', [
            'post' => $post
        ]);
    }
}

