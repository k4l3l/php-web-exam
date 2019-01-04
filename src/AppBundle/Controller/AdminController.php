<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Car;


/**
 * @Route("admin")
 * Class AdminController
 * @package AppBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_panel")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $allUsers = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        return $this->render('admin/index.html.twig', ['allUsers' => $allUsers]);
    }

    /**
     * @Route("/user-profile/{id}", name="admin_view_profile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfile($id)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if($user === $this->getUser()){
            return $this->redirectToRoute('user_profile');
        }

        $repairsCount = 0;

        foreach ($user->getCars() as $car){
            $repairsCount += count($car->getRepairs());
        }

        return $this->render('admin/user_profile.html.twig',
            ['user' => $user, 'repairs' => $repairsCount]);
    }

    /**
     * @Route("/edit-profile/{id}", name="admin_edit_profile")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProfile(Request $request, $id){
        $this->get('session')->getFlashBag()->clear();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $pass = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($pass);
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();

            return $this->redirectToRoute("admin_view_profile", ['id' => $id]);
        }

        return $this->render('admin/user_edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }
}
