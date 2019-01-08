<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Car;
use AppBundle\Entity\Engine;
use AppBundle\Entity\CarImage;
use AppBundle\Entity\User;
use AppBundle\Form\CarType;
use AppBundle\Form\EngineType;
use AppBundle\Form\ImageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CarController
 * @package AppBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class CarController extends Controller
{
    /**
 * @Route("admin/user/{id}/car/create", name="car_create")
 * @Security("is_granted('ROLE_ADMIN')")
 * @param Request $request
 * @param $id
 * @return \Symfony\Component\HttpFoundation\Response
 */
    public function createAction(Request $request, $id)
    {
        $car = new Car();
        $engine = new Engine();

        $engineForm = $this->createForm(EngineType::class, $engine);
        $carForm = $this->createForm(CarType::class, $car);
        $carForm->handleRequest($request);
        $engineForm->handleRequest($request);

        if($carForm->isSubmitted() && $carForm->isValid() && $engineForm->isValid()){

            $user = $this->getDoctrine()->getRepository(User::class)->find($id);
            $car->setOwner($user);
            $car->setEngine($engine);
            $car->setIsUpdated(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($car);
            $em->persist($engine);
            $em->flush();
            return $this->redirectToRoute("admin_view_profile", array('id' => $id));
        }
        return $this->render('car/create.html.twig', array(
            'id' => $id,
            'carForm' => $carForm->createView(),
            'engineForm' => $engineForm->createView()
        ));
    }

    /**
     * @Route("/car/{carId}/edit", name="car_edit")
     * @param Request $request
     * @param $carId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $carId)
    {
        $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
        $engine = $car->getEngine();
        $oldFile = $car->getImage();
        $carForm = $this->createForm(CarType::class, $car);
        $engineForm = $this->createForm(EngineType::class, $engine);
        $carForm->handleRequest($request);
        $engineForm->handleRequest($request);

        if($carForm->isSubmitted() && $carForm->isValid() && $engineForm->isValid()){
            /** @var UploadedFile $file */
            $file = $carForm->getData()->getImage();
            if($file !== null){
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                if($oldFile !== null){
                    unlink('uploads/images/cars/'. $oldFile);
                }
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
            return $this->redirectToRoute("user_profile");
        }
        return $this->render('car/edit.html.twig', array(
            'car' => $car,
            'carForm' => $carForm->createView(),
            'engineForm' => $engineForm->createView()
        ));
    }

}

