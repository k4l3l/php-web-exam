<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Repair;
use AppBundle\Entity\User;
use AppBundle\Form\RepairType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RepairController
 * @package AppBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class RepairController extends Controller
{
    /**
     * @Route("/user/{id}/{carId}/repair/create", name="repair_create")
     * @param Request $request
     * @param $id
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $id, $carId){

        $repair = new Repair();
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        if($car->getActiveRepair()){
            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }
        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $car->setActiveRepair($repair);
            $repair->setCar($car);
            $repair->setClient($car->getOwner());
            $car->setIsUpdated(true);
            $repair->setIsArchived(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        return $this->render('repair/create.html.twig',
            [
                'id'=> $id,
                'car' => $car,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/user/{id}/{carId}/{repairId}", name="repair_edit")
     * @param Request $request
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $id, $carId, $repairId){

        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $repair = $car->getActiveRepair();

        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $car->setIsUpdated(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        return $this->render('repair/edit.html.twig',
            [
                'id'=> $id,
                'car' => $car,
                'repair' => $repair,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/user/{id}/{carId}/{repairId}/archive", name="repair_archive")
     * @param Request $request
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function archiveAction(Request $request, $id, $carId, $repairId){

        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $form = $this->createForm(RepairType::class, $repair);

        if($repair !== null && $car !== null){
            $car->setIsUpdated(true);
            $car->setActiveRepair(null);
            $repair->setIsArchived(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        return $this->render('repair/edit.html.twig',
            [
                'id'=> $id,
                'car' => $car,
                'repair' => $repair,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/user/{id}/repairs", name="repairs_view")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAllAction(Request $request, $id){

        $currentUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles())){
            return $this->redirectToRoute('user_profile');
        }

        $activeRepairs = $this->getDoctrine()->getRepository(Repair::class)
            ->findBy(['client' => $user, 'isArchived' => false],['dateCreated' => 'desc']);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $repair->setCar($car);
            $repair->setIsUpdated(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->flush();

            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }
//
//        return $this->render('repair/edit.html.twig',
//            [
//                'id'=> $id,
//                'car' => $car,
//                'repair' => $repair,
//                'form' => $form->createView()
//            ]);
    }

}
