<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/post/{id}', name: 'post_view')]
    public function view(int $id, ArticleRepository $articleRepository): Response
    {
        // Récupérer l'article par son ID
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('post/post.html.twig', [
            'article' => $article,
        ]);
    }
}
