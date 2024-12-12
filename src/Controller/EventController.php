<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\NewEventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EventController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    // Injection des dépendances via le constructeur
    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/event', name: 'event_home')]
    public function index(Request $request): Response
    {
        $limit = 5;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        $eventRepository = $this->entityManager->getRepository(Event::class);
        
        $events = $eventRepository->createQueryBuilder('e')
            ->orderBy('e.startDate', 'ASC')
            ->where('e.startDate >= :now')
            ->setParameter('now', new \DateTime())
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalEvents = $eventRepository->count([]);
        $totalPages = ceil($totalEvents / $limit);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request): Response
    {
        // Vérifie si l'utilisateur est connecté
        if (!$this->security->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $event = new Event();
        $event->setCreatedBy($this->security->getUser());  // Remplir 'createdBy' par l'utilisateur connecté

        $form = $this->createForm(NewEventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event); // Persister l'événement
            $this->entityManager->flush(); // Sauvegarder en base de données

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('event_home');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}', name: 'event_view')]
    public function view(int $id): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('event/view.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/event/{id}/join', name: 'event_join')]
    public function joinEvent(int $id): Response
    {
        if (!$this->security->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $event = $this->entityManager->getRepository(Event::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $currentUser = $this->security->getUser();
        if (!$event->getParticipants()->contains($currentUser)) {
            $event->addParticipant($currentUser);
            $this->entityManager->flush();
            $this->addFlash('success', 'You have successfully joined the event!');
        }

        return $this->redirectToRoute('event_view', ['id' => $id]);
    }

    #[Route('/event/{id}/leave', name: 'event_leave')]
    public function leaveEvent(int $id): Response
    {
        if (!$this->security->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $event = $this->entityManager->getRepository(Event::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $currentUser = $this->security->getUser();
        if ($event->getParticipants()->contains($currentUser)) {
            $event->removeParticipant($currentUser);
            $this->entityManager->flush();
            $this->addFlash('success', 'You have left the event.');
        }

        return $this->redirectToRoute('event_view', ['id' => $id]);
    }

    #[Route('/event/edit/{id}', name: 'event_edit')]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(NewEventType::class, $event, ['is_edit' => true]);

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

    #[Route('/event/delete/{id}', name: 'event_delete')]
    public function delete(Event $event): Response
    {
        $this->entityManager->remove($event);
        $this->entityManager->flush();
        $this->addFlash('success', 'Event deleted successfully.');

        return $this->redirectToRoute('account');
    }
}
