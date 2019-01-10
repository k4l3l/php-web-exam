<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $checkUpdate = null;
        $msgCount = null;
        if ($user) {
            if(in_array('ROLE_ADMIN', $user->getRoles())){
                $msgCount = count($this->getDoctrine()->getRepository(Notification::class)->findBy(['isRead' => false]));
            }
            $checkUpdate = count($this->getDoctrine()->getRepository(Car::class)->findByUserIdUpdated($user->getId()));
        }
        return $this->render('default/index.html.twig', $user != null ?
            ['user' => $user, 'updateCount' => $checkUpdate, 'msgCount' => $msgCount] : []);
    }
}
