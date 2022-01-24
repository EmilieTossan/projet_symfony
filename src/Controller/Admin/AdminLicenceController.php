<?php

namespace App\Controller\Admin;

use App\Entity\Licence;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminLicenceController extends AbstractController
{
    /**
     * @Route("admin/licences", name="admin_licence_list")
     */
    public function adminLicenceList(LicenceRepository $licenceRepository)
    {
        $licences = $licenceRepository->findAll();
        return $this->render("admin/licences.html.twig", ['licences' => $licences]);
    }

    /**
     * @Route("admin/licence/{id}", name="admin_show_licence")
     */
    public function adminShowLicence($id, LicenceRepository $licenceRepository)
    {
        $licence = $licenceRepository->find($id);
        return $this->render("admin/licence.html.twig", ['licence' => $licence]);
    }

    /**
     * @Route("admin/create/licence", name="admin_create_licence")
     */
    public function adminCreateLicence(
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ){
        $licence = new Licence();
        $licenceForm = $this->createForm(LicenceType::class, $licence);
        $licenceForm->handleRequest($request);

        if($licenceForm->isSubmitted() && $licenceForm->isValid()){
            $entityManagerInterface->persist($licence);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_licence_list');
        }
        return $this->render("admin/licenceform.html.twig", ['licenceForm' => $licenceForm->createView()]);
    }

    /**
     * @Route("admin/update/licence{id}", name="admin_update_licence")
     */
    public function adminUpdateLicence(
        $id,
        Request $request,
        LicenceRepository $licenceRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $licence = $licenceRepository->find($id);
        $licenceForm = $this->createForm(LicenceType::class, $licence);
        $licenceForm->handleRequest($request);

        if($licenceForm->isSubmitted() && $licenceForm->isValid()){
            $entityManagerInterface->persist($licence);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('admin_licence_list');
        }
        return $this->render("admin/licenceform.html.twig", ['licenceForm' => $licenceForm->createView()]);
    }

    /**
     * @Route("admin/delete/licence{id}", name="admin_delete_licence")
     */
    public function adminDeleteLicence(
        $id,
        LicenceRepository $licenceRepository,
        EntityManagerInterface $entityManagerInterface
    ){
        $licence = $licenceRepository->find($id);
        $entityManagerInterface->remove($licence);
        $entityManagerInterface->flush();
        return $this->redirectToRoute('admin_licence_list');
    }
}