<?php


namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact", methods={"GET","POST"})
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function index(Request $request, \Swift_Mailer $mailer) : Response
    {
        $message = new  Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            $email = $form->getData()->getEmail();

            $mailMessage = (new \Swift_Message('Wild Circus - Nous avons bien reçu ton message !'))
                ->setFrom($this->getParameter('mailer_from'))
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'email/contactConfirmation.html.twig'
                    ),
                    'text/html'
                );
            $mailer->send($mailMessage);

            $mailMessage2 = (new \Swift_Message('Wild Circus - Tu as reçu un nouveau message !'))
                ->setFrom($this->getParameter('mailer_from'))
                ->setTo($this->getParameter('mailer_from'))
                ->setBody(
                    $this->renderView(
                        'email/contactNotification.html.twig',
                        ['user' => $form->getData()]
                    ),
                    'text/html'
                );
            $mailer->send($mailMessage2);

            $this->addFlash('success', 'Ton message a bien été envoyé !');
            return $this->redirectToRoute('contact');
        }
        return $this->render('contact/index.html.twig',[
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }
}