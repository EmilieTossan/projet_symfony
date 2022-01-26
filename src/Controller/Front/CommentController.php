<?php

namespace App\Controller\Front;

use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("update/comment/{id}", name="update_comment")
     */
    public function updateComment(
        $id,
        CommentRepository $commentRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ){
        $comment = $commentRepository->find($id);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        
        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $entityManagerInterface->persist($comment);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render("front/commentform.html.twig", ['commentForm' => $commentForm->createView()]);
    }

    /**
     * @Route("delete/comment/{id}", name="delete_comment")
     */
    public function deleteComment(
        $id,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $comment = $commentRepository->find($id);
        $entityManagerInterface->remove($comment);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('app_login');
    }
}