<?php

/**
 * @Route("/article/new", name="article_create")
 */

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

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
            // Si l'utilisateur n'est pas connecté, afficher un message d'erreur
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
}
