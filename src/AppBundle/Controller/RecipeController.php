<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Recipe;
use AppBundle\Form\Type\RecipeType;
use AppBundle\Helpers\Helpers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Helpers\ApiError;
use AppBundle\Helpers\ApiException;
use Symfony\Component\Form\FormInterface;

class RecipeController extends Controller
{

    /**
     * Retourne une liste de recettes
     *
     * @Route("/receipts", name="receipts_list")
     * @Method("GET")
     */
    public function listAction()
    {
        $receipts = $this->getDoctrine()
            ->getRepository('AppBundle:Recipe')
            ->findAll();

        $data = ['receipts' => []];
        foreach ($receipts as $recipe) {
            $data['receipts'][] = $this->serializeRecipe($recipe);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Crée une nouvelle recette
     *
     * @Route("/receipts", name="receipts_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On récupère la catégorie liée
        if (!$data['category']) {
            throw $this->createNotFoundException('Aucune catégorie renseignée');
        }
        $category = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findOneBy(['id' => $data['category']]);
        if (!$category) {
            throw $this->createNotFoundException('Aucune catégorie renseignée');
        }

        // On présente les données
        $data['steps'] = array_values($data['steps']);
        $data['ingredients'] = array_values($data['ingredients']);

        $recipe = new Recipe();

        $form = $this->createForm(RecipeType::class, $recipe);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiException($form);
        }

        $recipe->setCategory($category);
        $recipe->setUser($this->getUser());
        $recipe->setSlug(Helpers::slugify($recipe->getTitle()));

        $em = $this->getDoctrine()->getManager();
        $em->persist($recipe);
        $em->flush();

        $data = $this->serializeRecipe($recipe);

        $response = new JsonResponse($data, 201);
        $recipeUrl = $this->generateUrl('receipts_show', ['slug' => $recipe->getSlug()]);
        $response->headers->set('Location', $recipeUrl);

        return $response;
    }

    /**
     * Retourne une recette
     *
     * @Route("/receipts/{slug}", name="receipts_show")
     * @Method("GET")
     */
    public function showAction($slug)
    {
        $recipe = $this->getDoctrine()
            ->getRepository('AppBundle:Recipe')
            ->findOneBy(['slug' => $slug]);

        if (!$recipe) {
            throw $this->createNotFoundException(sprintf(
                'Aucune recette nommée "%s"',
                $slug
            ));
        }

        $data = $this->serializeRecipe($recipe);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Modifie une recette
     *
     * @Route("/receipts/{title}", name="receipts_update")
     * @Method("PUT")
     */
    public function updateAction($title, Request $request)
    {

        $recipe = $this->getDoctrine()
            ->getRepository('AppBundle:Recipe')
            ->findOneBy(['title' => $title]);

        if (!$recipe) {
            throw $this->createNotFoundException(sprintf(
                'Aucune recette nommée "%s"',
                $title
            ));
        }

        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->submit($data);

        $em =$this->getDoctrine()->getManager();
        $em->persist($recipe);
        $em->flush();

        $data =$this->serializeRecipe($recipe);

        $response = new JsonResponse($data, 200);

        return $response;

    }

    /**
     * Transforme une recette: object -> array
     */
    private function serializeRecipe(Recipe $recipe)
    {
        $ingredients = [];
        $i = 0;
        $ingredients_list = (array) $recipe->getIngredients();
        foreach ($ingredients_list as $ingredient) {
            $ingredients['ingredient_' . $i] = $ingredient;
            $i++;
        }

        return [
            'title' => $recipe->getTitle(),
            'category' => $recipe->getCategory()->getId(),
            'slug' => $recipe->getSlug(),
            'time' => $recipe->getTime(),
            'picture' => $recipe->getPicture(),
            'ingredients' => $ingredients,
            'steps' => $recipe->getSteps(),
        ];
    }

    /**
     * Traite les données soumises via le formulaire lié à l'entité
     */
    private function processForm(Request $request, FormInterface $form)
    {
        // On récupère les données de la requête
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $apiError = new ApiError(400, ApiError::TYPE_INVALID_REQUEST_BODY_FORMAT);
            throw new ApiException($apiError);
        }
        // Supprime les champs traité séparément du formulaire
        unset($data['category']);
        // Dit au formulaire d'ignorer les champs qui ne sont pas passés dans le cas d'un update via PATCH
        $clearMissing = $request->getMethod() != 'PATCH';
        // On soumet le formulaire
        $form->submit($data, $clearMissing);
    }

    /**
     * Retourne une réponse contenant les erreurs de validation d'un formulaire
     */
    private function throwApiException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);

        $apiError = new ApiError(400, ApiError::TYPE_VALIDATION_ERROR);
        $apiError->set('errors', $errors);

        throw new ApiException($apiError);
    }

    /**
     * Récupère les erreurs d'un formulaire
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = var_dump($error);
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

}
