<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/blog', name: 'admin_blog_')]
final class BlogController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PostRepository $repository): Response
    {
        $posts = $repository->findBy([], orderBy: ['published_at' => 'DESC']);
        return $this->render('admin/blog/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $repository): Response
    {
        $user = $repository->findOneBy(['username' => 'jane_admin']);
        $post = new Post();
        $post->setAuthor($user);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('admin_blog_index', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/blog/new.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_blog_show', [
                'id' => $post->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/blog/edit.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: [
        'id' => Requirement::DIGITS
    ], methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('admin/blog/show.html.twig', [
            'post' => $post
        ]);
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if ($this->isCsrfTokenValid('delete', $token)) {
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('admin_blog_index', status: Response::HTTP_SEE_OTHER);
    }
}
