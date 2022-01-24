<?php

namespace App\Controller\Front;

use App\Repository\LicenceRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LicenceController extends AbstractController
{
    /**
     * @Route("licences", name="licence_list")
     */
    public function licenceList(LicenceRepository $licenceRepository)
    {
        $licences = $licenceRepository->findAll();
        return $this->render("front/licences.html.twig", ['licences' => $licences]);
    }

    /**
     * @Route("licence/{id}", name="show_licence")
     */
    public function showLicence($id, LicenceRepository $licenceRepository)
    {
        $licence = $licenceRepository->find($id);
        return $this->render("front/licence.html.twig", ['licence' => $licence]);
    }
}