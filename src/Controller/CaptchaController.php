<?php

namespace App\Controller;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaptchaController extends AbstractController
{
    private CaptchaService $captchaService;

    public function __construct(CaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    /**
     * Generate a new captcha and return as base64 image
     * Accessible via AJAX without page reload
     */
    #[Route('/api/captcha/refresh', name: 'app_captcha_refresh', methods: ['GET', 'POST'])]
    public function refresh(): JsonResponse
    {
        try {
            // Generate new code
            $code = $this->captchaService->generateCode();
            
            // Generate image as base64
            $imageData = $this->captchaService->generateImage($code);
            
            return new JsonResponse([
                'success' => true,
                'captcha_image' => $imageData,
                'message' => 'Captcha généré avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération du captcha: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate captcha (optional endpoint for AJAX validation)
     * This does NOT clear the code - only checks if it's valid
     */
    #[Route('/api/captcha/validate', name: 'app_captcha_validate', methods: ['POST'])]
    public function validateAjax(\Symfony\Component\HttpFoundation\Request $request): JsonResponse
    {
        $code = $request->request->get('captcha_code', '');
        
        if (empty($code)) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Veuillez entrer le code captcha'
            ]);
        }
        
        // Use checkCode() which does NOT clear the session code
        $isValid = $this->captchaService->checkCode($code);
        
        return new JsonResponse([
            'valid' => $isValid,
            'message' => $isValid ? 'Code correct' : 'Code incorrect'
        ]);
    }
}
