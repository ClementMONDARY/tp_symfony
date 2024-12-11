<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
}
