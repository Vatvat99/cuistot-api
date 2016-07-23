<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;
use AppBundle\Helpers\ApiError;
use AppBundle\Helpers\ApiException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class UserController extends Controller
{

    /**
     * Retourne une liste d'utilisateur
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/users", name="users_list")
     * @Method("GET")
     */
    public function listAction()
    {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();

        $data = ['users' => []];
        foreach ($users as $user) {
            $data['users'][] = $this->serializeUser($user);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Crée un nouvel utilisateur
     *
     * @Route("/users", name="users_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiException($form);
        }

        // Encode le mot de passe
        $password = $this->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $data = $this->serializeUser($user);

        $response = new JsonResponse($data, 201);
        $userUrl = $this->generateUrl('users_show', ['email' => $user->getEmail()]);
        $response->headers->set('Location', $userUrl);

        return $response;
    }

    /**
     * Retourne un utilisateur
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/users/{email}", name="users_show")
     * @Method("GET")
     */
    public function showAction($email)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw $this->createNotFoundException(sprintf(
                'Aucun utilisateur pour l\'email "%s"',
                $email
            ));
        }

        $data = $this->serializeUser($user);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * Modifie un utilisateur
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/users/{email}", name="users_update")
     * @Method({"PUT", "PATCH"})
     */
    public function updateAction($email, Request $request)
    {

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw $this->createNotFoundException(sprintf(
                'Aucun utilisateur pour l\'email "%s"',
                $email
            ));
        }

        $form = $this->createForm(UserType::class, $user);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->throwApiException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $data = $this->serializeUser($user);

        $response = new JsonResponse($data, 200);

        return $response;

    }

    /**
     * Supprime un utilisateur
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/users/{email}", name="users_delete")
     * @Method("DELETE")
     */
    public function deleteAction($email)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if ($user) {
            // debated point: should we 404 on an unknown nickname?
            // or should we just return a nice 204 in all cases?
            // we're doing the latter
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return new Response(null, 204);
    }

    /**
     * Transforme un utilisateur: object -> array
     */
    private function serializeUser(User $user)
    {
        return [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
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
            $errors[] = $error->getMessage();
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
