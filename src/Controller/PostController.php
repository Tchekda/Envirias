<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\Tag;
use App\Form\PostFormType;
use App\Repository\LikeRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 * @package App\Controller
 */
class PostController extends AbstractController {


    /**
     * @Route("/post/show/{id}", name="post_show", requirements={"id"="\d+"})
     */
    public function show(Post $post, LikeRepository $likeRepository) {
        $hasLiked = false;
        if ($this->getUser() != null and $likeRepository->findOneBy(['post' => $post, 'user' => $this->getUser()])) {
            $hasLiked = true;
        }
        return $this->render('post/post.html.twig', [
            'post' => $post,
            'hasLiked' => $hasLiked
        ]);
    }

    /**
     * @Route("/post/edit/{id}", name="post_edit", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Post $post, EntityManagerInterface $entityManager, Request $request, TagRepository $tagRepository) {
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles()) and $post->getUser() !== $this->getUser()) {
            $this->createAccessDeniedException("Vous n'avez pas la permission pour éditer ce post");
        }
        if ($post->getValidated() and !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $this->addFlash("red", "Vous ne pouvez plus éditer ce post car il a été validé. Contactez nous si besoin.");
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPicture = $post->getPicture();
            /** @var Post $post */
            $post = $form->getData();
            foreach ($post->getTags() as $tag){
                $post->removeTag($tag);
                if (count($tag->getPosts()) == 0) {
                    //dd($tag);
                    $entityManager->remove($tag);
                }
            }
            $entityManager->flush();
            $newTags = json_decode($request->request->get('post_form')['newTags'], true);
            foreach ($newTags as $newTag) {
                if (!$tag = $tagRepository->findOneBy(['canonical' => strtolower(trim($newTag['tag']))])){
                    $tag = new Tag();
                    $tag
                        ->setCanonical(strtolower(trim($newTag['tag'])))
                        ->setName(trim($newTag['tag']));
                    $entityManager->persist($tag);
                    //dd($tag);
                }
                $post->addTag($tag);
            }

            $entityManager->persist($post);

            /** @var UploadedFile $picture */
            if ($picture = $form['image']->getData()) {
                if ($oldPicture != null) {
                    unlink($this->getParameter('upload_directory') . '/posts/' . $oldPicture);
                }
                $fileName = $post->getId() . '.' . $picture->guessExtension();
                $picture->move($this->getParameter('upload_directory') . '/posts', $fileName);
                $post->setPicture($fileName);
            }

            $entityManager->flush();
            $this->addFlash('green', (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'Le' : 'Votre') . ' post a bien été édité!');
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                if ($post->getValidated()){
                    return $this->redirectToRoute('admin_posts_list');
                }else{
                    return $this->redirectToRoute('admin_posts_to_validate');

                }
            }
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/edit.html.twig', [
            'postForm' => $form->createView(),
            'tags' => $tagRepository->findAll()
        ]);
    }

    /**
     * @Route("/post/new", name="post_new", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request, TagRepository $tagRepository, EntityManagerInterface $em) {
        $post = new Post();
        $postcontent = trim($request->get('postcontent'));
        if ($postcontent == '' || strlen($postcontent) < 25) {
            $this->addFlash('red', 'Votre contenu doit avoir au moins 25 caractères');
            return $this->redirectToRoute('homepage');
        }
        $post->setContent($request->get('postcontent'));
        $post->setUser($this->getUser());
        foreach (json_decode($request->request->get('tags_value'), true) as $tag) {
            $tagString = $tag['tag'];
            $tagString = preg_replace('/([^a-zA-Z0-9])/', '', $tagString);
            if (!$tagEntity = $tagRepository->findOneBy(['canonical' => strtolower($tagString)])) {
                $tagEntity = new Tag();
                $tagEntity->setName($tagString);
                $tagEntity->setCanonical(strtolower($tagString));
                $em->persist($tagEntity);
            }
            $post->addTag($tagEntity);
        }
        $em->persist($post);
        $em->flush();

        /** @var UploadedFile $picture */
        if ($picture = $request->files->get('picture')) {
            if (!in_array($picture->getClientOriginalExtension(), array("jpeg", "jpg", "png", "gif"))) {
                $this->addFlash('red', 'Votre image ne peut avoir comme extension jpeg, jpg, png ou gif');
                return $this->redirectToRoute('homepage');
            } elseif (($picture->getClientSize() / 1000000) > 2) { // Over than 2MB
                $this->addFlash('red', 'Votre image ne peut dépasser les 2MB, essayer de la compresser');
                return $this->redirectToRoute('homepage');
            }
            $fileName = $post->getId() . '.' . $picture->guessExtension();
            $picture->move($this->getParameter('upload_directory') . '/posts', $fileName);
            $post->setPicture($fileName);
        }
        $em->flush();
        $this->addFlash("green", "Votre post a bien été créer, vous pouvez l'éditer avant qu'il soit validé");
        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }


    /**
     * @Route("/post/delete/{id}", name="post_delete", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Post $post, EntityManagerInterface $entityManager) {
        if ($post->getUser() == $this->getUser()) {
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
            return $this->redirectToRoute('homepage');
        }
        $this->addFlash('red', 'Vous ne pouvez pas supprimer ce post');
        return $this->redirectToRoute('homepage');
    }


    /**
     * @Route("/ajax/post/{id}/like", name="ajax_post_like", methods={"POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function like(Post $post, LikeRepository $likeRepository, EntityManagerInterface $entityManager) {

        $add = true;
        $error = null;
        if ($post->getValidated()) {
            if ($post->getUser() != $this->getUser()) {
                if ($like = $likeRepository->findOneBy(['user' => $this->getUser(), 'post' => $post])) {
                    $post->removeLike($like);
                    $entityManager->remove($like);
                    $add = false;
                    if ($this->getUser() != $post->getUser()) {
                        $this->getUser()->addScore(-1);
                        $post->getUser()->addScore(-1);
                    }
                } else {
                    $like = new Like();
                    $like->setUser($this->getUser());
                    $post->addLike($like);
                    $entityManager->persist($like);
                    if ($this->getUser() != $post->getUser()) {
                        $this->getUser()->addScore(1);
                        $post->getUser()->addScore(1);
                    }
                }
                $entityManager->flush();
            } else {
                $error = "Vous ne pouvez pas likez votre post ;(";
            }
        } else {
            $error = "Ce post n'a pas encore été validé";
        }
        return new JsonResponse([
            'error' => $error,
            'status' => $add,
            'count' => count($post->getLikes())
        ]);
    }

    /**
     * @Route("/tag/{id}", name="tag_show", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function postsByTag(Tag $tag, TagRepository $tagRepository) {
        return $this->render('post/tag.html.twig', [
            'tag' => $tagRepository->findAllPostByTag($tag),
        ]);
    }

}
