<?php

namespace App\Controller\Front;

use App\Entity\Like;
use App\Entity\Comment;
use App\Entity\Dislike;
use App\Form\CommentType;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use App\Repository\DislikeRepository;
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
        $commentForm = $this->createForm(CommentType::class, $comment);
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

    /**
     * @Route("search", name="front_search")
     */
    public function frontSearch(Request $request, ProductRepository $productRepository)
    {
        $term = $request->query->get('term');
        $products = $productRepository->searchByTerm($term);
        return $this->render('front/search.html.twig', ['products' => $products, 'term' => $term]);
    }

    /**
     * @Route("like/product/{id}", name="product_like")
     */
    public function likeProduct(
        $id,
        ProductRepository $productRepository,
        LikeRepository $likeRepository,
        DislikeRepository $dislikeRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $product = $productRepository->find($id);
        $user = $this->getUser();

        if ($product->isLikedByUser($user)) {
            $like = $likeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);
            $entityManagerInterface->remove($like);
            $entityManagerInterface->flush();
        }

        if ($product->isDislikedByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);
            $entityManagerInterface->remove($dislike);

            $like = new Like();

            $like->setProduct($product);
            $like->setUser($user);

            $entityManagerInterface->persist($like);
            $entityManagerInterface->flush();
        }

        $like = new Like();
        $like->setProduct($product);
        $like->setUser($user);

        $entityManagerInterface->persist($like);
        $entityManagerInterface->flush();
    }

    /**
     * @Route("dislike/product/{id}", name="product_dislike")
     */
    public function dislikeProduct(
        $id,
        ProductRepository $productRepository,
        LikeRepository $likeRepository,
        DislikeRepository $dislikeRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $product = $productRepository->find($id);
        $user = $this->getUser();

        if ($product->isDislikedByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);
            $entityManagerInterface->remove($dislike);
            $entityManagerInterface->flush();
        }

        if ($product->isLikedByUser($user)) {
            $like = $likeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);
            $entityManagerInterface->remove($like);

            $dislike = new Dislike();

            $dislike->setProduct($product);
            $dislike->setUser($user);

            $entityManagerInterface->persist($dislike);
            $entityManagerInterface->flush();
        }

        $dislike = new Dislike();
        $dislike->setProduct($product);
        $dislike->setUser($user);

        $entityManagerInterface->persist($dislike);
        $entityManagerInterface->flush();
    }
}