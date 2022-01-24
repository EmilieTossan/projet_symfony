<?php

namespace App\Controller\Front;

use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @Route("categories", name="category_list")
     */
    public function categoryList(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findAll();
        return $this->render("front/categories.html.twig", ['categories' => $categories]);
    }

    /**
     * @Route("category/{id}", name="show_category")
     */
    public function showProduct($id, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($id);
        return $this->render("front/category.html.twig", ['category' => $category]);
    }
}