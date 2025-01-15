<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class OpenIdConnectController extends AbstractController
{
    #[\Symfony\Component\Routing\Attribute\Route('/openid-connect/generic', name: 'app_openid-connect_generic')]
    public function generic(): RedirectResponse
    {
        return $this->redirectToRoute('admin');
    }

    #[\Symfony\Component\Routing\Attribute\Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
