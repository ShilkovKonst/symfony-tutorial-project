<?php

namespace App\Controller\Admin;

use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/users', name: 'users')]
    public function users(UsersRepository $usersRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $usersRepository->findBy([],
            ['created_at' => 'asc'])
        ]);
    }

    #[Route('/categories', name: 'categories')]
    public function categories(CategoriesRepository $categoriessRepository): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categoriessRepository->findBy([],
            ['parent' => 'asc'])
        ]);
    }

    #[Route('/products', name: 'products')]
    public function products(ProductsRepository $productsRepository): Response
    {
        return $this->render('admin/products/index.html.twig', [
            'products' => $productsRepository->findBy([],
            ['created_at' => 'asc'])
        ]);
    }
}