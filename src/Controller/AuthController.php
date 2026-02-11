<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('auth/login.html.twig');
    }

    #[Route('/register-patient', name: 'app_register_patient')]
    public function registerPatient(): Response
    {
        return $this->render('auth/register-patient.html.twig');
    }

    #[Route('/register-professional', name: 'app_register_professional')]
    public function registerProfessional(): Response
    {
        return $this->render('auth/register-professional.html.twig');
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(): Response
    {
        return $this->render('auth/forgot-password.html.twig');
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(): Response
    {
        return $this->render('auth/reset-password.html.twig');
    }

    #[Route('/verify-email', name: 'app_verify_email')]
    public function verifyEmail(): Response
    {
        return $this->render('auth/verify-email.html.twig');
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('auth/terms-modal.html.twig');
    }
}