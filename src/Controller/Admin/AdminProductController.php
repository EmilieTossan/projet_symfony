<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminProductController extends AbstractController
{
    /**
     * @Route("admin/products", name="admin_product_list")
     */
    public function adminProductList(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        return $this->render("admin/products.html.twig", ['products' => $products]);
    }

    /**
     * @Route("admin/product/{id}", name="admin_show_product")
     */
    public function adminShowProduct($id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        return $this->render("admin/product.html.twig", ['product' => $product]);
    }

    /**
     * @Route("admin/create/product", name="admin_create_product")
     */
    public function adminCreateProduct(
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ){
        $product = new Product();
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_product_list');
        }
        return $this->render("admin/productform.html.twig", ['productForm' => $productForm->createView()]);
    }

    /**
     * @Route("admin/update/product{id}", name="admin_update_product")
     */
    public function adminUpdateProduct(
        $id,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $product = $productRepository->find($id);
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_product_list');
        }
        return $this->render("admin/productform.html.twig", ['productForm' => $productForm->createView()]);
    }

    /**
     * @Route("admin/delete/product{id}", name="admin_delete_product")
     */
    public function adminDeleteProduct(
        $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $product = $productRepository->find($id);
        $entityManagerInterface->remove($product);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('admin_product_list');
    }
}