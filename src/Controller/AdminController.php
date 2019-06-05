<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Form\StatusType;
use App\Form\FilterType;
use App\Service\ArticleService;
use App\Service\AdminService;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $articleService;
    private $adminService;

    public function __construct(ArticleService $articleService, AdminService $adminService)
    {
        $this->articleService = $articleService;
        $this->adminService = $adminService;
    }

    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/articles", name="admin_articles", methods={"GET", "POST"})
     */
    public function adminArticles(Request $request, ArticleRepository $articleRepository)
    {
        $page = $request->query->get('page') ? : 1;
        $email = null;
        $dateFrom = null;
        $dateTo = null;
        $statuses = null;

        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];
            $email = $form->getData()['email'];
            $dateFrom = $form->getData()['dateFrom'];
            $dateTo = $form->getData()['dateTo'];

            $statuses = $status == null ? null : [$status];
        }

        $articles = $articleRepository->findLatest($page, null, $statuses, $email, $dateFrom, $dateTo);

        $maxPages = ceil(count($articles) / Article::COUNT_ON_PAGE);

        return $this->render('admin/article/index.html.twig', [
            'form' => $form->createView(),
            'thisPage' => $page,
            'maxPages' => $maxPages,
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}/edit", name="admin_article_edit", methods={"GET", "POST"})
     */
    public function adminArticleChangeStatus(Request $request, Article $article)
    {
        $form = $this->createForm(StatusType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];
            $this->articleService->setArticleStatus($article, $status);
            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'form'=> $form->createView()
        ]);
    }

    /**
     * @Route("/users", name="admin_users", methods={"GET"})
     */
    public function adminUsers(Request $request, UserRepository $userRepository)
    {
        $page = $request->get('page') ? : 1;
        $query =  $request->get('query');
        $sortBy = $request->get('sortBy');
        $sortType = $request->get('sortType');

        $users = $userRepository->searchByEmail($query, $page, $sortBy, $sortType);
        $maxPages = ceil(count($users) / User::COUNT_ON_PAGE);

        return $this->render('admin/user/index.html.twig', [
            'thisPage' => $page,
            'maxPages' => $maxPages,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/emails", name="admin_users_emails", methods={"GET"})
     */
    public function adminUsersEmails(Request $request): JsonResponse
    {
        $data = $this->adminService->searchUsersEmailsAsArray($request->get('query'));
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/user/{id}/block", name="admin_user_block", methods={"GET", "POST"})
     */
    public function adminUserBlock(User $user)
    {
        $this->adminService->blockUser($user);
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user/{id}/activate", name="admin_user_activate", methods={"GET", "POST"})
     */
    public function adminUserActivate(User $user)
    {
        $this->adminService->activateUser($user);
        return $this->redirectToRoute('admin_users');
    }
}
