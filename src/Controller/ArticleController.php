<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleRepository;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/article/new', name: 'article_new')]
    public function new(Request $request, UserInterface $user): Response
    {
        if (!$user) {
            return $this->json(['message' => 'You must be logged in to submit an article.'], 403);
        }

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter l'auteur et la date de dernière modification
            $article->setAuthor($user);
            $article->setLastModified(new \DateTime());

            // Sauvegarder l'article
            $this->entityManager->persist($article);
            $this->entityManager->flush();
            $this->addFlash('success', 'Your article has been successfully submitted!');

            return $this->redirectToRoute('home');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'article_edit')]
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Article updated successfully.');

            return $this->redirectToRoute('account');
        }

        return $this->render('account/edit_article.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/delete/{id}', name: 'article_delete')]
    public function delete(Article $article): Response
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
        $this->addFlash('success', 'Article deleted successfully.');

        return $this->redirectToRoute('account');
    }

    #[Route('/', name: 'articles_home')]
    public function index(ArticleRepository $articleRepository, Request $request): Response
    {
        // Nombre d'articles par page
        $limit = 5;

        // Page actuelle, par défaut 1
        $page = $request->query->getInt('page', 1);

        // Calcul du début de la page (pour la pagination)
        $offset = ($page - 1) * $limit;

        // Récupérer les articles de la page actuelle
        $articles = $articleRepository->createQueryBuilder('a')
            ->orderBy('a.lastModified', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Calculer le nombre total d'articles
        $totalArticles = $articleRepository->count([]);

        // Calcul du nombre total de pages
        $totalPages = ceil($totalArticles / $limit);

        return $this->render('post/index.html.twig', [
            'articles' => $articles,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }
}
