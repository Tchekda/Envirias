<?php


namespace App\Controller;


use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swift_Mailer;
use Swift_SendmailTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {

    /**
     * @Route("/", name="homepage", options={"sitemap" = true})
     */
    public function index(PostRepository $postRepository, TagRepository $tagRepository, UserRepository $userRepository, PaginatorInterface $paginator, Request $request) {
        $pagination = $paginator->paginate(
            $postRepository->findAllValidatedQuery(), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return $this->render('home/index.html.twig', [
            //'posts' => $postRepository->findAllValidated(),
            'pagination' => $pagination,
            'tags' => $tagRepository->findMostPopular(),
            'best_users_total' => $userRepository->findBestUsersTotal(),
            'best_users_month' => $userRepository->findBestUsersMonth()
        ]);
    }

    /**
     * @Route("/ajax/getalltags", name="ajax_get_all_tags", methods={"POST"})
     */
    public function getAllTags(TagRepository $tagRepository) {
        return new JsonResponse([
            'tags' => $tagRepository->findAllWithNames()
        ]);
    }


    /**
     * @Route("/rules", name="rules", options={"sitemap" = true})
     */
    public function rules(){
        return $this->render('home/rules.html.twig', [
        ]);
    }

}