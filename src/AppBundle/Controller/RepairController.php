<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Repair;
use AppBundle\Entity\User;
use AppBundle\Form\NotificationType;
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
    protected $entityManager;
    protected $translator;
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repository;

    protected function initialise()
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->repository = $this->entityManager->getRepository('AppBundle:Repair');
        $this->translator = $this->get('translator');
    }
    /**
     * @Route("/user/{id}/{carId}/repair/create", name="repair_create")
     * @param Request $request
     * @param $id
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $id, $carId)
    {
        $this->initialise();

        $repair = new Repair();

        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($car->getActiveRepair()) {
            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setActiveRepair($repair);
            $repair->setCar($car);
            $repair->setClient($car->getOwner());
            $car->setIsUpdated(true);
            $repair->setIsArchived(false);
            $this->entityManager->persist($repair);
            $this->entityManager->persist($car);
            $this->entityManager->flush();
            // Inform user
            $flashBag = $this->translator->trans('folder_add_success', array(), 'flash');
            $request->getSession()->getFlashBag()->add('notice', $flashBag);
            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        return $this->render('repair/create.html.twig',
            [
                'id' => $id,
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
     * @throws \Exception
     */
    public function editAction(Request $request, $id, $carId, $repairId)
    {
        $this->initialise();

        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $repair = $car->getActiveRepair();

        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setIsUpdated(true);
            $repair->setDateModified(new \DateTime('now'));
            $this->entityManager->persist($repair);
            $this->entityManager->persist($car);
            $this->entityManager->flush();
            // Inform user
            $flashBag = $this->translator->trans('folder_edit_success', array(), 'flash');
            $request->getSession()->getFlashBag()->add('notice', $flashBag);

            return $this->redirectToRoute('admin_view_profile', ['id' => $id]);
        }

        return $this->render('repair/edit.html.twig',
            [
                'id' => $id,
                'car' => $car,
                'repair' => $repair,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/user/{id}/{carId}/{repairId}/archive", name="repair_archive")
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws \Exception
     */
    public function archiveAction($id, $carId, $repairId)
    {

        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($repair !== null && $car !== null) {
            // if the repair is owned by the car
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car' => $car]);
            if (in_array($repair, $repairs)) {
                $car->setActiveRepair(null);
                $car->setIsUpdated(false);
                $repair->setIsArchived(true);
                $repair->setDateModified(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($repair);
                $em->persist($car);
                $em->flush();
            }
        }

        return $this->redirectToRoute('repairs_view', ['id' => $id]);
    }

    /**
     * @Route("/user/{id}/repairs", name="repairs_view")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAllAction($id, Request $request)
    {
        $currentUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('user_profile');
        }

        $orderedRepairs = $this->getDoctrine()->getRepository(Repair::class)
            ->findBy(['client' => $user], ['isArchived' => 'asc', 'dateModified' => 'desc']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $orderedRepairs, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        return $this->render('repair/view_all.html.twig',
            [
                'id' => $id,
                'pagination' => $pagination,
                'user' => $user
            ]);
    }

    /**
     * @Route("/user/{id}/{carId}/{repairId}/set-active", name="repair_setActive")
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws \Exception
     */
    public function setActiveAction($id, $carId, $repairId)
    {

        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($repair !== null && $car !== null) {
            // if the repair is owned by the car
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car'=> $car]);
            if (in_array($repair, $repairs)) {
                $em = $this->getDoctrine()->getManager();
                $activeRepair = $car->getActiveRepair();
                // if there is an active repair already
                if ($activeRepair !== null) {
                    $activeRepair->setIsArchived(true);
                    $em->persist($activeRepair);
                }
                $car->setActiveRepair($repair);
                $repair->setIsArchived(false);
                $repair->setDateModified(new \DateTime('now'));
                $car->setIsUpdated(true);
                $em->persist($repair);
                $em->persist($car);
                $em->flush();
            }
        }
        return $this->redirectToRoute('repairs_view', ['id' => $id]);
    }

    /**
     * @Route("/user/{id}/{carId}/{repairId}/delete", name="repair_delete")
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws \Exception
     */
    public function removeAction($id, $carId, $repairId)
    {

        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($repair !== null && $car !== null) {
            // if the repair is owned by the car
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car'=> $car]);
            if (in_array($repair, $repairs)) {
                $activeRepair = $car->getActiveRepair();
                $em = $this->getDoctrine()->getManager();
                // if its an active repair
                if ($activeRepair === $repair) {
                    $car->setActiveRepair(null);
                    $car->setIsUpdated(false);
                    $em->persist($car);
                    $em->flush();
                }
                $em->remove($repair);
                $em->flush();
            }
        }
        return $this->redirectToRoute('repairs_view', ['id' => $id]);
    }

    /**
     * @Route("/profile/{carId}/{repairId}", name="view_repair")
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewRepair($carId, $repairId){
        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $currentUser = $this->getUser();

        if($currentUser !== $car->getOwner() && !in_array('ROLE_ADMIN', $currentUser->getRoles())){
            return $this->redirectToRoute('user_profile');
        }

        if ($repair !== null && $car !== null) {
            // if the repair is owned by the car
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car'=> $car]);
            if (in_array($repair, $repairs)) {
                $em = $this->getDoctrine()->getManager();
                $car->setIsUpdated(false);
                $em->persist($car);
                $em->flush();
                return $this->render('/repair/view_repair.html.twig',
                    ['car' => $car, 'repair' => $repair]);
            }
        }
    }

}
