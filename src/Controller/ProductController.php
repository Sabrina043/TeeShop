<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
#[Route('/admin/ajouter-un-produit', name: "create_product", methods:['GET', 'Post'])]
public function createProduct(Request $request, ProductRepository $repository):Response 
{
    $product = new Product();
    return $this->render('');
}
}

?>