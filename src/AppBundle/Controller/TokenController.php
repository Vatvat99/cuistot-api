<?php
namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends Controller
{
    /**
     * @Route("/tokens", name="tokens_new")
     * @Method({"POST", "OPTIONS"})
     */
    public function newAction(Request $request)
    {
        // On vérifie que les identifiants sont corrects
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $request->getUser()]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->getPassword());
        if (!$isValid) {
            throw new BadCredentialsException();
        }

        // On génère le JSON Web Token
        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode(['email' => $user->getEmail()]);

        // On le renvoie à l'utilisateur
        return new JsonResponse(['token' => $token]);
    }
}