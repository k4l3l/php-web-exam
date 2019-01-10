<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repair;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $emailForm = $form->getData()->getEmail();
            $userForm = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $emailForm]);

            if (null !== $userForm) {
                $this->addFlash('info', "Username with email " . $emailForm . " already taken!");
                return $this->render('user/register.html.twig', ['form' => $form->createView()]);
            }

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());

            $role = $this
                ->getDoctrine()
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($role);
            $user->setEmail($emailForm);

            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("security_login");
        }

        return $this->render('user/register.html.twig',
            ['form' => $form->createView()]);
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile()
    {
//        $validator = $this->get('validator');
//        $errors = $validator->validate(***);
//        return $this->render('', array('name' => $name));
        $user = $this->getUser();
        $repairsCount = count($user->getRepairs());
        $activeRepairs = count($this->getDoctrine()->getRepository(Repair::class)
            ->findBy(['client' => $user, 'isArchived' => false]));

        return $this->render('user/profile.html.twig',
            [
                'user' => $user,
                'repairs' => $repairsCount,
                'active' => $activeRepairs
            ]);
    }

    /**
     * @Route("/edit", name="user_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        $email = $user->getEmail();
        $pass = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPassword()) {
                $pass = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPassword());
            }

            $user->setPassword($pass);
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $user->setEmail($email);
            }
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();

            return $this->redirectToRoute("user_profile");
        }
        return $this->render('user/edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }
}
