<?php

namespace App\Twig;

use App\Service\CaptchaService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CaptchaExtension extends AbstractExtension
{
    private CaptchaService $captchaService;
    private bool $initialized = false;
    private string $lastImage = '';

    public function __construct(CaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_captcha', [$this, 'generateCaptcha'], ['is_safe' => ['html']]),
            new TwigFunction('generate_captcha_image', [$this, 'generateCaptchaImage'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate and store captcha code in session
     */
    public function generateCaptcha(): string
    {
        // Generate new code
        $code = $this->captchaService->generateCode();
        
        // Generate and store image
        $this->lastImage = $this->captchaService->generateImage($code);
        
        return $code;
    }

    /**
     * Get the last generated captcha image
     */
    public function generateCaptchaImage(string $code = null): string
    {
        // If code provided, generate new image; otherwise use last one
        if ($code !== null) {
            return $this->captchaService->generateImage($code);
        }
        
        return $this->lastImage;
    }
}
