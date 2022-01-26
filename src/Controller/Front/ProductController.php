<?php

namespace App\Controller\Front;

use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
    public function showProduct(
        $id, 
        ProductRepository $productRepository,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface)
    {
        $product = $productRepository->find($id);
        $comment = new Comment();
        $commentForm = $$this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        
        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $user = $this->getUser();
            $user_email = $user->getUserIdentifier();
            $user = $userRepository->findOneBy(['email' => $user_email]);
            $comment->setUser($user);
            $comment->setProduct($product);
            $comment->setDate(new \DateTime("NOW"));
            $entityManagerInterface->persist($comment);
            $entityManagerInterface->flush();
        }
        
        return $this->render("front/product.html.twig", [
            'product' => $product,
            'commentForm' => $commentForm->createView()
        ]);
    }
}