<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CaptchaService
{
    private RequestStack $requestStack;
    
    private const CAPTCHA_LENGTH = 6;
    private const CAPTCHA_SESSION_KEY = 'captcha_code';
    
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }
    
    /**
     * Generate a random captcha code
     */
    public function generateCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < self::CAPTCHA_LENGTH; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }
        
        // Store in session
        $session = $this->requestStack->getSession();
        $session->set(self::CAPTCHA_SESSION_KEY, strtoupper($code));
        
        return $code;
    }
    
    /**
     * Validate the captcha code WITHOUT clearing (for AJAX validation)
     */
    public function checkCode(string $code): bool
    {
        $session = $this->requestStack->getSession();
        $storedCode = $session->get(self::CAPTCHA_SESSION_KEY);
        
        if ($storedCode === null) {
            return false;
        }
        
        return strtoupper($code) === $storedCode;
    }
    
    /**
     * Validate and CLEAR the captcha code (for form submission)
     */
    public function validate(string $code): bool
    {
        $session = $this->requestStack->getSession();
        $storedCode = $session->get(self::CAPTCHA_SESSION_KEY);
        
        // Clear the captcha after validation attempt (one-time use)
        $session->remove(self::CAPTCHA_SESSION_KEY);
        
        if ($storedCode === null) {
            return false;
        }
        
        return strtoupper($code) === $storedCode;
    }
    
    /**
     * Generate captcha as SVG image (no GD required)
     */
    public function generateImage(string $code): string
    {
        $width = 200;
        $height = 70;
        
        // Generate random colors
        $bgColors = ['#f5f5f5', '#e8f4f8', '#f0f8ff', '#fff8dc', '#f0fff0'];
        $bgColor = $bgColors[array_rand($bgColors)];
        
        // Generate SVG
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">
  <defs>
    <filter id="noise" x="0%" y="0%" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="4" result="noise"/>
      <feColorMatrix type="saturate" values="0" result="coloredNoise"/>
      <feBlend in="SourceGraphic" in2="coloredNoise" mode="multiply"/>
    </filter>
  </defs>
  
  <!-- Background -->
  <rect width="'.$width.'" height="'.$height.'" fill="'.$bgColor.'"/>
  
  <!-- Noise pattern -->
  <rect width="'.$width.'" height="'.$height.'" fill="transparent" filter="url(#noise)" opacity="0.1"/>';
        
        // Add random lines
        for ($i = 0; $i < 6; $i++) {
            $x1 = rand(0, $width);
            $y1 = rand(0, $height);
            $x2 = rand(0, $width);
            $y2 = rand(0, $height);
            $color = sprintf('#%06x', rand(0, 0xaaaaaa));
            $opacity = rand(5, 20) / 100;
            $svg .= '
  <line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="'.$color.'" stroke-width="1" opacity="'.$opacity.'"/>';
        }
        
        // Add characters with distortion
        $chars = str_split($code);
        $startX = 25;
        $charWidth = 28;
        
        foreach ($chars as $index => $char) {
            $x = $startX + ($index * $charWidth);
            $y = rand(35, 50);
            $rotation = rand(-20, 20);
            $fontSize = rand(28, 36);
            $fontWeight = rand(400, 700);
            
            // Random color for each character
            $colors = ['#1a5c4a', '#00695c', '#004d40', '#2e7d32', '#1565c0', '#c62828', '#6a1b9a'];
            $color = $colors[array_rand($colors)];
            
            $svg .= '
  <text x="'.$x.'" y="'.$y.'" 
        font-family="Arial, sans-serif" 
        font-size="'.$fontSize.'" 
        font-weight="'.$fontWeight.'" 
        fill="'.$color.'"
        transform="rotate('.$rotation.', '.$x.', '.$y.')"
        style="letter-spacing: 4px;">'.$char.'</text>';
        }
        
        // Add more noise dots
        for ($i = 0; $i < 50; $i++) {
            $x = rand(0, $width);
            $y = rand(0, $height);
            $r = rand(1, 2);
            $color = sprintf('#%06x', rand(0, 0xcccccc));
            $svg .= '
  <circle cx="'.$x.'" cy="'.$y.'" r="'.$r.'" fill="'.$color.'" opacity="0.3"/>';
        }
        
        $svg .= '
</svg>';
        
        // Return as base64 data URL
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
