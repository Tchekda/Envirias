<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordFormType;
use App\Form\UserEditFormType;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController {
    /**
     * @Route("/myaccount", name="myaccount")
     */
    public function myaccount(UserRepository $userRepository, PostRepository $postRepository, LikeRepository $likeRepository) {
        return $this->render('account/account.html.twig', [
            'user' => $userRepository->getAllUserData($this->getUser()),
            'posts' => $postRepository->findBy(['user' => $this->getUser()]),
            'likes' => $likeRepository->findUserLike($this->getUser())
        ]);
    }


    /**
     * @Route("/account/{id}", name="user_show", requirements={"id"="\d+"})
     */
    public function show(User $user,UserRepository $userRepository, LikeRepository $likeRepository, PostRepository $postRepository) {
        return $this->render('account/account.html.twig', [
            'user' => $userRepository->getAllUserData($user),
            'posts' => $postRepository->findBy(['user' => $user]),
            'likes' => $likeRepository->findUserLike($user)
        ]);
    }

    /**
     * @Route("/myaccount/edit", name="user_edit")
     */
    public function editUser(Request $request, EntityManagerInterface $entityManager) {
        $form = $this->createForm(UserEditFormType::class, $this->getUser(), ['roles' => $this->getUser()->getRoles()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            /** @var UploadedFile $picture */
            if ($website = $user->getWebsite()){
                $user->setWebsite(preg_replace('#^https?://#', '', $website));
            }
            if ($picture = $form['picture']->getData()) {
                if ($user->getPicture() != 'default.png') {
                    unlink($this->getParameter('upload_directory') . '/users/' . $user->getPicture());
                }
                $fileName = $user->getId() . '.' . $picture->guessExtension();
                $picture->move($this->getParameter('upload_directory') . '/users', $fileName);
                $user->setPicture($fileName);
            }
            $entityManager->flush();
            return $this->redirectToRoute('myaccount');
        }
        return $this->render('account/edit.html.twig', [
            'userEditForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/myaccount/password", name="user_password_change")
     */
    public function newPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager) {
        $form = $this->createForm(ResetPasswordFormType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setPassword($passwordEncoder->encodePassword(
                $this->getUser(),
                $form['newPassword']->getData()
            ));
            $this->getUser()->setToken(null);
            $entityManager->flush();
            $this->addFlash('green', 'Votre mot de passe à bien été mis à jour');
            return $this->redirectToRoute('myaccount');
        }
        return $this->render('account/reset.html.twig', [
            'resetForm' => $form->createView()
        ]);
    }
}
