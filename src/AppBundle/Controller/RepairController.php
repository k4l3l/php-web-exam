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
    public function createAction(Request $request, $id, $carId)
    {

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
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->persist($car);
            $em->flush();

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

        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $repair = $car->getActiveRepair();

        $form = $this->createForm(RepairType::class, $repair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setIsUpdated(true);
            $repair->setDateModified(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($repair);
            $em->persist($car);
            $em->flush();

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
            // TODO if the repair is already archived
            $repairs = $this->getDoctrine()->getRepository(Repair::class)->findBy(['car' => $car]);
            if (in_array($repair, $repairs)) {
                $car->setActiveRepair(null);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAllAction($id)
    {
        $currentUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('user_profile');
        }

        $orderedRepairs = $this->getDoctrine()->getRepository(Repair::class)
            ->findBy(['client' => $user], ['isArchived' => 'asc', 'dateModified' => 'desc']);

        return $this->render('repair/view_all.html.twig',
            [
                'id' => $id,
                'repairs' => $orderedRepairs,
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
    public function setActive($id, $carId, $repairId)
    {

        $repair = $this->getDoctrine()->getRepository(Repair::class)->find($repairId);
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);

        if ($repair !== null && $car !== null) {
            // TODO if the repair is already active
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
     * @Route("/user/{id}/{carId}/{repairId}/set-active", name="repair_delete")
     * @param $id
     * @param $carId
     * @param $repairId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws \Exception
     */
    public function removeRepair($id, $carId, $repairId)
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
                    $em->persist($car);
                    $em->flush();
                }
                $em->remove($repair);
                $em->flush();
            }
        }
        return $this->redirectToRoute('repairs_view', ['id' => $id]);
    }

}
