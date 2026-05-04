<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use UnexpectedValueException;

final class EasySecurityLoginController extends AbstractController
{
    public function __construct(
        private readonly DefaultAuthenticationSuccessHandler $authenticationSuccessHandler,
    ) {
    }

    #[Route('/easy-security/login', name: 'easy_security.login')]
    public function login(AuthenticationUtils $authUtils, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($this->getUser() !== null) {
            $defaultTargetPath = $this->authenticationSuccessHandler->getOptions()['default_target_path']
                ?? throw new UnexpectedValueException(
                    'Default target path not set in authentication success handler options.'
                );

            return $this->redirectToRoute($defaultTargetPath);
        }

        $csrfToken = $tokenGenerator->generateToken();

        $response = $this->render('@EasySecurity/controller/login.html.twig', [
            'csrf_token' => $csrfToken,
            'error' => $authUtils->getLastAuthenticationError(),
            'last_username' => $authUtils->getLastUsername(),
        ]);

        $response->headers->setCookie(
            new Cookie('csrf-token', $csrfToken, 0, '/', null, true, false, false, 'strict')
        );

        return $response;
    }
}
