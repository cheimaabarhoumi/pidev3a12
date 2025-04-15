<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private $httpClient;
    private $recaptchaSecret;

    public function __construct(HttpClientInterface $httpClient, string $recaptchaSecret)
    {
        $this->httpClient = $httpClient;
        $this->recaptchaSecret = $recaptchaSecret;
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->attributes->get('_route') === 'app_login';
    }

    public function authenticate(Request $request): Passport
    {
        $recaptchaToken = $request->request->get('recaptcha_token');
        
        if (!$recaptchaToken) {
            throw new AuthenticationException('Veuillez compléter le reCAPTCHA pour vous connecter.');
        }

        // Verify reCAPTCHA token
        $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->recaptchaSecret,
                'response' => $recaptchaToken,
            ],
        ]);

        $result = json_decode($response->getContent(), true);

        if (!$result['success']) {
            throw new AuthenticationException('La vérification reCAPTCHA a échoué. Veuillez réessayer.');
        }

        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if (!$email || !$password) {
            throw new AuthenticationException('Email et mot de passe sont requis.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Authentication Required', Response::HTTP_UNAUTHORIZED);
    }
} 