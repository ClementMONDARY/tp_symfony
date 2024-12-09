<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/post/{id}', name: 'post_view')]
    public function view(int $id): Response
    {
        return $this->render('post/post.html.twig', [
            'id' => $id
        ]);
    }
}
