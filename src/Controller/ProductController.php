<?php

namespace App\Controller;

use DateTime;
use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin')]
class ProductController extends AbstractController
{
    #[Route('/ajouter-un-produit', name: "create_product", methods: ['GET', 'POST'])]
    public function createProduct(Request $request, ProductRepository $repository, SluggerInterface $slugger): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductFormType::class, $product)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setCreatedAt(new DateTime());
            $product->setUpdatedAt(new DateTime());

            #on variabilise le fichier de la photo en récupérant les données du formulaire photo
            #On obtient un objet de type uploadedFile()

            $photo = $form->get('photo')->getData();

            if ($photo) {

                # 1 - déconstruire le nom du fichier
                # a - variabiliser l'extension du fichier
                $extension = '.' . $photo->guessExtension();
                # 2 - assainir le nom du fichier (c-a-d retirer les accents et les espaces blancs)
                $safeFilename = $slugger->slug(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));

                # 3 - rendre le nom du fichier unique
                # a - reconstruire le nom du fichier
                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                # 4 - Déplacer le fichier (upload dans notre application Symfony)
                # On utilise un try/catch lorsqu'une méthode lance (trow) une excéption (erreur) 
                try {
                    # On a défini un paramètre dans config/service.yaml qui est le chemin (absolu) du dossier 'uploads'
                    # On récupère la valeur (le paramètre) avec getParameter() et le nom du param défini dans le fichier service.yaml.
                    $photo->move($this->getParameter('uploads_dir'), $newFilename);
                    # Si tout s'est bien passé (aucune Exception lancée) alors on doit set le nom de la photo en BDD
                    $product->setPhoto($newFilename);
                } catch (FileException $exception) {
                    $this->addFlash("warning", "le fichier ne s'est pas importer corréctement veuillez réessayer" . $exception->getMessage());
                } //end catch()

            } //endif($photo)

            $repository->save($product, true);

            $this->addFlash('success', "Le produit est en ligne avec succès !");
            return $this->redirectToRoute('show_dashboard');
        } //endForm

        return $this->render('/admin/product/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/modifier-un-produit/{id}', name: 'update_product', methods: ['GET', 'POST'])]
    public function updatedProduct(Product $product, Request $request, ProductRepository $repository, SluggerInterface $slugger): Response
    {
        $currentPhoto =$product->getPhoto();
        $form = $this->createForm(ProductFormType::class, $product, [
            'photo'=> $currentPhoto
        ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new Datetime());
            $newPhoto = $form->get('photo')->getData();

            if ($newPhoto) {
                $this->handleFile($product, $newPhoto, $slugger);
            } else {
                #si pas de nouvelle photo, alors on resset la photo courante (actuelle)
                $product->setPhoto($currentPhoto);
            } //end if($newPhoto)

            $repository->save($product, true);

            $this->addFlash('success', "la modification à bien été enregistré.");
            return $this->redirectToRoute('show_dashboard');

        } //end if ($form)


        return $this->render('/product/form.html.twig', [
            'form' => $form->createView()
        ]);
    } //en updateProduct()

    #///////////////////////////Private Function////////////////////////////#}

    private function handleFile(Product $product, UploadedFile $photo, SluggerInterface $slugger)
    {

        # 1 - déconstruire le nom du fichier
        # a - variabiliser l'extension du fichier
        $extension = '.' . $photo->guessExtension();
        # 2 - assainir le nom du fichier (c-a-d retirer les accents et les espaces blancs)
        $safeFilename = $slugger->slug(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));

        # 3 - rendre le nom du fichier unique
        # a - reconstruire le nom du fichier
        $newFilename = $safeFilename . '_' . uniqid() . $extension;

        # 4 - Déplacer le fichier (upload dans notre application Symfony)
        # On utilise un try/catch lorsqu'une méthode lance (trow) une excéption (erreur) 
        try {
            # On a défini un paramètre dans config/service.yaml qui est le chemin (absolu) du dossier 'uploads'
            # On récupère la valeur (le paramètre) avec getParameter() et le nom du param défini dans le fichier service.yaml.
            $photo->move($this->getParameter('uploads_dir'), $newFilename);
            # Si tout s'est bien passé (aucune Exception lancée) alors on doit set le nom de la photo en BDD
            $product->setPhoto($newFilename);
        } catch (FileException $exception) {
            $this->addFlash("warning", "le fichier ne s'est pas importer corréctement veuillez réessayer" . $exception->getMessage());
        } //end catch()
    } //end function handleFile()

    #[Route('/archiver-un-produit/{id}', name:'soft_delete_product', methods:['GET'])]
    public function softDeleteProduct(Product $product, ProductRepository $repository): Response
    {
        $product->setDeletedAt(new DateTime());
        $repository->save($product, true);

        $this->addFlash('success', "le produit a bien été archivé");
        return $this->redirectToRoute('show_dashboard');

    }

}// end class
