<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostLogoutController extends AbstractController
{
    #[\Symfony\Component\Routing\Attribute\Route('/post/logout', name: 'app_post_logout')]
    public function index(): Response
    {
        return $this->render('post_logout/index.html.twig', []);
    }
}
