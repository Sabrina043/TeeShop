<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
#[Route('/admin/tableau-de-bord', name: "show_dashboard", methods:['GET'])]
public function showDashboard(ProductRepository $productRepository):Response 
{
    $products = $productRepository->findBy(['deletedAt' => null]);
    
    return $this->render('admin/show_dashboard.html.twig', [
        'products'=>$products,
    ]);

    
}
}

