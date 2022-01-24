<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("products", name="product_list")
     */
    public function productList(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        return $this->render("front/products.html.twig", ['products' => $products]);
    }

    /**
     * @Route("product/{id}", name="show_product")
     */
    public function showProduct($id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        return $this->render("front/product.html.twig", ['product' => $product]);
    }
}