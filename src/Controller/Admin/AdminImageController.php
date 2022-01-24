<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminImageController extends AbstractController
{
    /**
     * @Route("admin/create/image", name="admin_create_image")
     */
    public function adminCreateImage(
        Request $request,
        SluggerInterface $sluggerInterface,
        EntityManagerInterface $entityManagerInterface
    ){
        $image = new Image();
        $imageForm = $this->createForm(ImageType::class, $image);
        $imageForm->handleRequest($request);

        if($imageForm->isSubmitted() && $imageForm->isValid()){
            $imageFile = $imageForm->get('src')->getData();

            if ($imageFile){
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $sluggerInterface->slug($originalFileName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFileName
                );

                $image->setSrc($newFileName);
            }

            $entityManagerInterface->persist($image);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_product_list');
        }
        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    /**
     * @Route("admin/update/image{id}", name="admin_update_image")
     */
    public function adminUpdateImage(
        $id,
        Request $request,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $image = $imageRepository->find($id);
        $imageForm = $this->createForm(ImageType::class, $image);
        $imageForm->handleRequest($request);

        if($imageForm->isSubmitted() && $imageForm->isValid()){
            $entityManagerInterface->persist($image);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_product_list');
        }
        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    /**
     * @Route("admin/delete/image{id}", name="admin_delete_image")
     */
    public function adminDeleteImage(
        $id,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $image = $imageRepository->find($id);
        $entityManagerInterface->remove($image);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('admin_product_list');
    }
}