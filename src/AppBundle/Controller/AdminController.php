<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repair;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\CarType;
use AppBundle\Form\EngineType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Car;


/**
 * @Route("admin")
 * Class AdminController
 * @package AppBundle\Controller
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_panel")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allUsers = $em->getRepository(User::class)->findAll();
        if($request->isMethod('POST')){
            $criteria = $request->get('param');
            $value = $request->get('search');
            $allUsers = $em->getRepository(User::class)->findBy([$criteria => $value]);
        }
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $allUsers, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );
        return $this->render('admin/searchBar.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @Route("/user-profile/{id}", name="admin_view_profile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfileAction($id)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if ($user === $this->getUser()) {
            return $this->redirectToRoute('user_profile');
        }

        $repairsCount = count($user->getRepairs());

        $activeRepairs = count($this->getDoctrine()->getRepository(Repair::class)
            ->findBy(['client' => $user, 'isArchived' => false]));


        return $this->render('admin/user_profile.html.twig',
            [
                'user' => $user,
                'repairs' => $repairsCount,
                'active' => $activeRepairs,
                'id' => $id
            ]);
    }

    /**
     * @Route("/edit-profile/{id}", name="admin_edit_profile")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProfileAction(Request $request, $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $pass = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();
            $user->setEmail($email);
            $user->setPassword($pass);
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();

            return $this->redirectToRoute("admin_view_profile", ['id' => $id]);
        }

        return $this->render('admin/user_edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/delete", name="admin_delete_user")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUserAction($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute("admin_view_profile", ['id' => $id]);
        }
        $carWithActive = $this->getDoctrine()->getRepository(Car::class)->findByUserIdActiveRepairs($id);
        if ($carWithActive) {
            $this->addFlash('info', "Cannot delete user with active repairs!");
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_panel');
    }

    /**
     * @Route("/user/{id}/car/{carId}/edit", name="admin_car_edit")
     * @param Request $request
     * @param $id
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editCarAction(Request $request, $id, $carId)
    {
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $engine = $car->getEngine();
        $oldFile = $car->getImage();
        $carForm = $this->createForm(CarType::class, $car);
        $engineForm = $this->createForm(EngineType::class, $engine);
        $carForm->handleRequest($request);
        $engineForm->handleRequest($request);

        if ($carForm->isSubmitted() && $carForm->isValid() && $engineForm->isValid()) {

            /** @var UploadedFile $file */
            $file = $carForm->getData()->getImage();

            if ($file !== null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                if($oldFile !== null){
                    unlink($this->getParameter('car_directory'). $oldFile);
                }
                try {
                    $file->move($this->getParameter('car_directory'),
                        $fileName);
                } catch (FileException $ex) {

                }
                $car->setImage($fileName);
            } elseif ($file === null && $oldFile !== null){
                $car->setImage($oldFile);
            }
            $car->setEngine($engine);
            $em = $this->getDoctrine()->getManager();
            $em->persist($car);
            $em->persist($engine);
            $em->flush();

            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }
        return $this->render('admin/car_edit.html.twig', array(
            'id' => $id,
            'car' => $car,
            'carForm' => $carForm->createView(),
            'engineForm' => $engineForm->createView()
        ));
    }

    /**
     * @Route("/user/{id}/car/{carId}/delete", name="admin_car_delete")
     * @param $id
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCarAction($id, $carId)
    {
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($car === null) {
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }
        if ($car->getActiveRepair()) {
            $this->addFlash('info', "Cannot delete user with active repairs!");
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($car);
        $em->flush();
        return $this->redirectToRoute("admin_view_profile", array('id' => $id));
    }

    /**
     * @Route("/create-user", name="user_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $email = $form->getData()->getEmail();
            $userForm = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if(null !== $userForm){
                $this->addFlash('info', "Username with email " . $email . " already taken!");
                return $this->render('user/register.html.twig', ['form' => $form->createView()]);
            }
            $pass = rand(1,10000);
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $pass);

            $role = $this
                ->getDoctrine()
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($role);
            $user->setEmail($email);

            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', "Created user ". $email ." with pass " . $pass);
            return $this->redirectToRoute("homepage");
        }

        return $this->render('user/register.html.twig',
            ['form' => $form->createView()]);
    }

}
