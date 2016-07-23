<?php

namespace AppBundle\Security;

use AppBundle\Helpers\ApiError;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Helpers\ResponseFactory;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{

    private $jwtEncoder;
    private $em;
    private $responseFactory;

    public function __construct(JWTEncoderInterface $jwtEncoder, EntityManager $em, ResponseFactory $responseFactory)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
        $this->responseFactory = $responseFactory;
    }

    /**
     * 1) Un utilisateur envoie une requête à l'api accompagnée de ses identifiants (le token)
     * Cette méthode récupère le token de l'utilisateur
     */
    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);

        if (!$token) {
            return;
        }

        return $token;

    }

    /**
     * 2) L'api recherche l'utilisateur correspondant au token (passé en arguement à la méthode dans $credentials)
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // On décode le token
        $data = $this->jwtEncoder->decode($credentials);
        // On renvoie une exception en cas de problème avec le token (expiré, ou hacké...)
        if ($data === false) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }
        // Si ça va, on récupère l'email
        $email = $data['email'];
        // Et on récupère l'utilisateur correspondant
        return $this->em
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);
    }

    /**
     * 3) Méthode exécutée lorsqu'un utilisateur a été trouvé
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Aucun mot de passe à vérifier dans note cas, on retourne true tout simplement
        return true;
    }

    /**
     * 4) Méthode exécutée lorsqu'aucun utilisateur n'a été trouvé
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $apiError = new ApiError(401);
        $apiError->set('detail', $exception->getMessageKey());

        return $this->responseFactory->createResponse($apiError);
    }

    /**
     * Méthode exécutée lorsque l'authentification à réussi.
     * On laisse vide pour que le controlleur correspondant à la requête qui a été faite soit appellé (processus normal
     * de la requête)
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    /**
     * Méthode permettant de conserver la connexion de l'utilisateur sur plusieurs sessions
     * Ca ne s'applique pas dans le cas de l'api
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Méthode appellée lorque l'authentification est requise, mais est manquante
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $apiError = new ApiError(401);
        $message = $authException ? $authException->getMessageKey() : 'Missing credentials';
        $apiError->set('detail', $message);

        return $this->responseFactory->createResponse($apiError);
    }
}
