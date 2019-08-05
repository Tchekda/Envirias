<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\Post;
use App\Entity\User;
use App\Form\BadgeFormType;
use App\Form\UserEditFormType;
use App\Repository\BadgeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController {
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index() {
        return $this->render('admin/index.html.twig', []);
    }


    /**
     * @Route("/admin/posts/validation", name="admin_posts_to_validate")
     */
    public function validation(PostRepository $postRepository) {
        return $this->render('admin/validate.html.twig', [
            'posts' => $postRepository->findAllNotValidated()
        ]);
    }


    /**
     * @Route("/admin/post/delete/{id}", name="admin_post_delete", requirements={"id"="\d+"})
     */
    public function delete(Post $post, EntityManagerInterface $entityManager, Request $request) {
        foreach ($post->getTags() as $tag) {
            $post->removeTag($tag);
            if (count($tag->getPosts()) == 0) {
                $entityManager->remove($tag);
            }
        }
        foreach ($post->getLikes() as $like) {
            $entityManager->remove($like);
        }
        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('green', 'Le post a bien été supprimé');
        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/admin/post/validate/{id}", name="admin_post_validate", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function validatePost(Post $post, EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository, BadgeRepository $badgeRepository) {
        $post->setValidated(true);
        $score = intval($request->request->get('value'));
        $post->getUser()->addScore($score);
        $count = intval($userRepository->countUsersValidatedPosts($post->getUser())[1]);
        if ($count % 100 == 0) {
            $count /= 100;
            if ($count > 0) {
                $post->getUser()->removeBadge($badgeRepository->findOneBy(['icon' => 'filter_' . $count]));
            }
            $post->getUser()->addBadge($badgeRepository->findOneBy(['icon' => 'filter_' . ($count + 1)]));

        }
        $entityManager->flush();
        $this->addFlash('green', 'Le post a bien été validé et ' . $score . ' points ont bien été crédité');
        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/admin/users/", name="admin_users_list")
     */
    public function listUsers(UserRepository $userRepository){
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }


    /**
     * @Route("/admin/user/edit/{id}", name="admin_user_edit",  requirements={"id"="\d+"})
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager){
        $form = $this->createForm(UserEditFormType::class, $user, ['roles' => $this->getUser()->getRoles()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            /** @var UploadedFile $picture */
            if ($picture = $form['picture']->getData()){
                if ($user->getPicture() != 'default.png'){
                    unlink($this->getParameter('upload_directory') . '/users/' . $user->getPicture());
                }
                $fileName = $user->getId() . '.' . $picture->guessExtension();
                $picture->move($this->getParameter('upload_directory') . '/users', $fileName);
                $user->setPicture($fileName);
            }
            $entityManager->flush();
            $this->addFlash('green', 'Le compte a bien été édité');
            return $this->redirectToRoute('admin_users_list');
        }
        return $this->render('admin/user_edit.html.twig', [
            'userEditForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/admin/posts", name="admin_posts_list")
     */
    public function posts (PostRepository $postRepository) {
        return $this->render("admin/posts.html.twig", [
            'posts' => $postRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/reset", name="admin_reset")
     */
    public function reset(PostRepository $postRepository, EntityManagerInterface $entityManager) {
        foreach ($postRepository->findAll() as $post) {
            foreach ($post->getTags() as $tag) {
                $post->removeTag($tag);
                if (count($tag->getPosts()) == 0) {
                    $entityManager->remove($tag);
                }
            }
            foreach ($post->getLikes() as $like) {
                $entityManager->remove($like);
            }
            $entityManager->remove($post);
        }
        $entityManager->flush();
        $this->addFlash('red', 'Vous venez de remettre le site à zéro!');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/admin/badges", name="admin_badges_list")
     */
    public function badges(BadgeRepository $badgeRepository) {
        return $this->render("admin/badges.html.twig", [
            'badges' => $badgeRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/badge/edit/{id}", name="admin_badge_edit", requirements={"id"="\d+"})
     */
    public function editBadge(Badge $badge,Request $request, EntityManagerInterface $entityManager){
        $form = $this->createForm(BadgeFormType::class, $badge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Badge $badge */
            $badge = $form->getData();

            $entityManager->flush();
            $this->addFlash('green', 'Le badge a bien été édité');
            return $this->redirectToRoute('admin_badges_list');
        }
        return $this->render('admin/badge_edit.html.twig', [
            'badgeEditForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/badge/new", name="admin_badge_new")
     */
    public function newBadge(Request $request, EntityManagerInterface $entityManager){
        $form = $this->createForm(BadgeFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Badge $badge */
            $badge = $form->getData();
            $entityManager->persist($badge);
            $entityManager->flush();
            $this->addFlash('green', 'Le badge a bien été édité');
            return $this->redirectToRoute('admin_badges_list');
        }
        return $this->render('admin/badge_edit.html.twig', [
            'badgeEditForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/badge/delete/{id}", name="admin_badge_delete", requirements={"id"="\d+"})
     */
    public function deleteBadge(Badge $badge, EntityManagerInterface $entityManager) {
        foreach ($badge->getUsers() as $user){
            $user->removeBadge($badge);
        }
        $entityManager->remove($badge);
        $entityManager->flush();
        $this->addFlash("green", "Ce badge à bien été supprimé");
        return $this->redirectToRoute('admin_badges_list');
    }

    /**
     * @Route("/admin/user/delete/{id}", name="admin_user_delete", requirements={"id"="\d+"})
     */
    public function deleteUser(User $user, EntityManagerInterface $entityManager) {
        foreach ($user->getLikes() as $like ){
            $user->removeLike($like);
            $entityManager->remove($like);
        }

        foreach ($user->getPosts() as $post ){
            foreach ($post->getLikes() as $like ){
                $like->getPost()->getUser()->removeLike($like);
                $entityManager->remove($like);
            }
            $user->removePost($post);
            $entityManager->remove($post);
        }

        foreach ($user->getBadges() as $badge ){
            $user->removePost($badge);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash("green", "L'utilisateur a bien été supprimé");
        return $this->redirectToRoute('admin_users_list');

    }


}
