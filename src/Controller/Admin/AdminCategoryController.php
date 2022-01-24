<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminCategoryController extends AbstractController
{
    /**
     * @Route("admin/categories", name="admin_category_list")
     */
    public function adminCategoryList(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findAll();
        return $this->render("admin/categories.html.twig", ['categories' => $categories]);
    }

    /**
     * @Route("admin/category/{id}", name="admin_show_category")
     */
    public function adminShowCategory($id, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($id);
        return $this->render("admin/category.html.twig", ['category' => $category]);
    }

    /**
     * @Route("admin/create/category", name="admin_create_category")
     */
    public function adminCreateCategory(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SluggerInterface $sluggerInterface
    ){
        $category = new Category();
        $categoryForm = $this->createForm(CategoryType::class, $category);
        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            $imageFile = $categoryForm->get('image')->getData();

            if($imageFile){
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $sluggerInterface->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $category->setImage($newFilename);
            }

            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_category_list');
        }
        return $this->render("admin/categoryform.html.twig", ['categoryForm' => $categoryForm->createView()]);
    }

    /**
     * @Route("admin/update/category{id}", name="admin_update_category")
     */
    public function adminUpdateCategory(
        $id,
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $category = $categoryRepository->find($id);
        $categoryForm = $this->createForm(CategoryType::class, $category);
        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_category_list');
        }
        return $this->render("admin/categoryform.html.twig", ['categoryForm' => $categoryForm->createView()]);
    }

    /**
     * @Route("admin/delete/category{id}", name="admin_delete_category")
     */
    public function adminDeleteCategory(
        $id,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $category = $categoryRepository->find($id);
        $entityManagerInterface->remove($category);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('admin_category_list');
    }
}