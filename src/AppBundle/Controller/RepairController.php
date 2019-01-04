<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Repair;
use AppBundle\Form\RepairType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RepairController extends Controller
{
    /**
     * @Route("/user/{id}/repair/create", name="repair_create")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request, $id){

        $repair = new Repair();
        $car = new Car();
        $form1 = $this->get('form.factory')->createNamedBuilder('repairForm', 'AppBundle\Form\RepairType')
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextareaType')
            ->add('isFullService', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType')
            ->add('isDiagnostics', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType')
            ->add('isRunGear', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType')
            ->getForm();
//
//        $form2 = $this->get('form.factory')->createNamedBuilder('carForm', 'AppBundle\Form\CarType')
//            ->add('bar', 'text')
//            ->getForm();

        if('POST' === $request->getMethod()) {

            if ($request->request->has('repairForm')) {
                $form1->handleRequest($request);
            };

            if ($request->request->has('carForm')) {
                // handle the second form
            };
    }
        return $this->render('repair/create.html.twig', ['id'=> $id, 'form1' => $form1->createView()]);
    }
}
