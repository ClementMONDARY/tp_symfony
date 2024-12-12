<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/account', name: 'account')]
    public function index(): Response
    {
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }

        // Get events created by user
        $createdEvents = $this->entityManager->getRepository(Event::class)
            ->findBy(['createdBy' => $user]);

        // Get events where user is participant using DQL
        $participatingEvents = $this->entityManager->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->innerJoin('e.participants', 'p')
            ->where('p.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        // Get user's articles
        $articles = $this->entityManager->getRepository(Article::class)
            ->findBy(['author' => $user], ['lastModified' => 'DESC']);

        return $this->render('account/index.html.twig', [
            'createdEvents' => $createdEvents,
            'participatingEvents' => $participatingEvents,
            'articles' => $articles,
        ]);
    }

    #[Route('/account/event/edit/{id}', name: 'event_edit')]
    public function editEvent(Request $request, Event $event): Response
    {
        // Logic to edit the event
        $form = $this->createForm(Event::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('account');
        }

        return $this->render('account/edit_event.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/event/delete/{id}', name: 'event_delete')]
    public function deleteEvent(Event $event): RedirectResponse
    {
        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->redirectToRoute('account');
    }

    #[Route('/account/article/edit/{id}', name: 'article_edit')]
    public function editArticle(Request $request, Article $article): Response
    {
        // Logic to edit the article
        $form = $this->createForm(Article::class, $article);

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

    #[Route('/account/article/delete/{id}', name: 'article_delete')]
    public function deleteArticle(Article $article): RedirectResponse
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->redirectToRoute('account');
    }
}
