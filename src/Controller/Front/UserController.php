<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * @Route("register", name="register_user")
     */
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        MailerInterface $mailerInterface
    ){
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){
            $user->setRoles(["ROLE_USER"]);

            $user_email = $userForm->get('email')->getData();
            $user_name = $userForm->get('name')->getData();
            $user_firstname =  $userForm->get('firstname')->getData();
            
            $plainPassword = $userForm->get('password')->getData();
            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $email = (new TemplatedEmail())
                ->from('admin@test.com')
                ->to($user_email)
                ->subject('Inscription')
                ->htmlTemplate('front/emailregister.html.twig')
                ->context([
                    'name' => $user_name,
                    'firstname' => $user_firstname
                ]);

            $mailerInterface->send($email);
            return $this->redirectToRoute('front_home');
        }
        return $this->render("registration/register.html.twig", ['userForm' => $userForm->createView()]);
    }

    /**
     * @Route("update/registration", name="update_user")
     */
    public function updateUser(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        UserRepository $userRepository
    ){
        $user_connected = $this->getUser();
        $user_email = $user_connected->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $user_email]);

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){

            $plainPassword = $userForm->get('password')->getData();
            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('front_home');
        }
    }

    /**
     * @Route("delete/registration", name="delete_user")
     */
    public function deleteUser(
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $user_connected = $this->getUser();
        $user_email = $user_connected->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $user_email]);

        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('front_home');
    }
}