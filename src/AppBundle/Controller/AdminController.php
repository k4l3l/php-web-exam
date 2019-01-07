<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Engine;
use AppBundle\Entity\Repair;
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
    public function editProfile(Request $request, $id)
    {
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

    /**
     * @Route("/{id}/delete", name="admin_delete_user")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute("admin_view_profile", ['id' => $id]);
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($user->getRoles() as $role){
            $user->removeRole($role);
        }
        $em->persist($user);
        $em->flush();
        foreach ($user->getCars() as $car) {
            $car->setActiveRepair(null);
            foreach ($car->getRepairs() as $repair){
                $em->remove($repair);
            }
            $em->flush();
            $em->remove($car);
            $em->remove($car->getEngine());
        }
        $em->flush();
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
        $carForm = $this->createForm(CarType::class, $car);
        $engineForm = $this->createForm(EngineType::class, $engine);
        $carForm->handleRequest($request);
        $engineForm->handleRequest($request);

        if ($carForm->isSubmitted() && $carForm->isValid() && $engineForm->isValid()) {

            /** @var UploadedFile $file */
            $file = $carForm->getData()->getImage();
            if ($file !== null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                try {
                    $file->move($this->getParameter('car_directory'),
                        $fileName);
                } catch (FileException $ex) {

                }
                $car->setImage($fileName);
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
     * @param Request $request
     * @param $id
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCarAction(Request $request, $id, $carId)
    {
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car' => $car]);
        $engine = $car->getEngine();
        if ($car === null) {
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }
        if ($car->getActiveRepair()) {
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }

        $em = $this->getDoctrine()->getManager();
        foreach ($repairs as $repair){
            $em->remove($repair);
        }
        $em->flush();
        $em->remove($car);
        $em->flush();
        $em->remove($engine);
        $em->flush();
        return $this->redirectToRoute("admin_view_profile", array('id' => $id));
    }

}
