<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Repair;
use AppBundle\Form\NotificationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package AppBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class NotificationController extends Controller
{
    /**
     * @Route("/profile/{carId}/{repairId}/send", name="message_send")
     * @param Request $request
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendAction(Request $request, $carId, $repairId)
    {
        $message = new Notification();
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $currentUser = $this->getUser();

        if ($currentUser !== $car->getOwner() && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('user_profile');
        }

        if ($repair !== null && $car !== null) {
            // if the repair is owned by the car
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car' => $car]);
            if (in_array($repair, $repairs)) {
                $form = $this->createForm(NotificationType::class, $message);
                $form->handleRequest($request);

                $message->setRepair($repair);
                $message->setIsRead(false);
                $repair->addNotification($message);
                $em = $this->getDoctrine()->getManager();
                $em->persist($repair);
                $em->flush();
                return $this->redirectToRoute('view_repair', ['carId' => $carId, 'repairId' => $repairId]);
            }
        }

        return $this->redirectToRoute('user_profile');
    }

    /**
     * @Route("/admin/notifications", name="view_notifications")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function viewAllAction()
    {
        $allMsgs = $this
            ->getDoctrine()
            ->getRepository(Notification::class)
            ->findAll();

        return $this->render('notification/view_all.html.twig', ['allMsgs' => $allMsgs]);
    }

    /**
     * @Route("/admin/notifications/{msgId}", name="view_notification")
     * @Security("is_granted('ROLE_ADMIN')")
     * @param $msgId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewOneAction($msgId)
    {
        $msg = $this
            ->getDoctrine()
            ->getRepository(Notification::class)
            ->find($msgId);
        $msg->setIsRead(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($msg);
        $em->flush();

        return $this->render('notification/view_one.html.twig', ['msg' => $msg]);
    }
}
