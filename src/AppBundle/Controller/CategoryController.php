<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\Type\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{

    /**
     * Retourne une liste de catégories
     *
     * @Route("/categories", name="categories_list")
     * @Method("GET")
     */
    public function listAction()
    {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findAll();

        $data = ['categories' => []];
        foreach ($categories as $category) {
            $data['categories'][] = $this->serializeCategory($category);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Crée une nouvelle catégorie
     *
     * @Route("/categories", name="categories_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $user);
        $form->submit($data);

        $em =$this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $data =$this->serializeUser($category);

        $response = new JsonResponse($data, 201);
        $categoryUrl = $this->generateUrl('categories_show', ['title' => $category->getTitle()]);
        $response->headers->set('Location', $categoryUrl);

        return $response;
    }

    /**
     * Retourne une catégorie
     *
     * @Route("/categories/{title}", name="categories_show")
     * @Method("GET")
     */
    public function showAction($title)
    {
        $category = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findOneBy(['title' => $title]);

        if (!$category) {
            throw $this->createNotFoundException(sprintf(
                'Aucune catégorie nommée "%s"',
                $title
            ));
        }

        $data = $this->serializeUser($category);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Modifie une catégorie
     *
     * @Route("/categories/{title}", name="categories_update")
     * @Method("PUT")
     */
    public function updateAction($title, Request $request)
    {

        $category = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findOneBy(['title' => $title]);

        if (!$category) {
            throw $this->createNotFoundException(sprintf(
                'Aucune catégorie nommée "%s"',
                $title
            ));
        }

        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data);

        $em =$this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        $data =$this->serializeCategory($category);

        $response = new JsonResponse($data, 200);

        return $response;

    }

    /**
     * Transforme une catégorie: object -> array
     */
    private function serializeCategory(Category $category)
    {
        return [
            'title' => $category->getTitle(),
        ];
    }

}
