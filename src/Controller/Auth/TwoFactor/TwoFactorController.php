<?php

namespace App\Controller\Auth\TwoFactor;

use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry as DoctrineManagerRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;

class TwoFactorController extends AbstractController
{
    #[Route('/profile/2fa/setup', name: 'app_2fa_setup')]
    public function setup(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // If 2FA is already enabled, redirect to manage page
        if ($user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('app_2fa_manage');
        }

        // Generate a new secret key for the user
        $secret = $totpAuthenticator->generateSecret();
        $user->setTotpSecret($secret);
        
        // Save the secret temporarily (not enabled yet)
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Generate otpauth URI for Google Authenticator
        $otpAuthUri = $totpAuthenticator->getQRContent($user);

        // Generate QR code using alternative API (Google Charts might be blocked)
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpAuthUri);

        return $this->render('auth/2fa/setup.html.twig', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    #[Route('/profile/2fa/enable', name: 'app_2fa_enable', methods: ['POST'])]
    public function enable(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $code = $request->request->get('code');
        
        // Debug: Check if secret exists
        $secret = $user->getTotpSecret();
        error_log('2FA Enable - Secret: ' . ($secret ? 'exists' : 'null'));
        error_log('2FA Enable - Code received: ' . $code);
        
        // Verify the code
        if ($totpAuthenticator->checkCode($user, $code)) {
            // Enable 2FA
            $user->setIsTwoFactorEnabled(true);
            
            // Generate backup codes
            $user->generateBackupCodes(10);
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'authentification à deux facteurs est maintenant activée.');
            
            return $this->redirectToRoute('app_2fa_backup_codes');
        }

        $this->addFlash('error', 'Code invalide. Veuillez réessayer.');
        
        return $this->redirectToRoute('app_2fa_setup');
    }

    #[Route('/profile/2fa/backup-codes', name: 'app_2fa_backup_codes')]
    public function backupCodes(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('app_2fa_setup');
        }

        return $this->render('auth/2fa/backup-codes.html.twig', [
            'backupCodes' => $user->getPlainBackupCodes(),
        ]);
    }

    #[Route('/profile/2fa/manage', name: 'app_2fa_manage')]
    public function manage(): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/2fa/manage.html.twig', [
            'is2FAEnabled' => $user->isTotpAuthenticationEnabled(),
            'backupCodesCount' => count($user->getBackupCodes()),
            'trustedDevicesCount' => count($user->getTrustedDevicesArray() ?? []),
        ]);
    }

    #[Route('/profile/2fa/disable', name: 'app_2fa_disable')]
    public function disable(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('app_2fa_manage');
        }

        return $this->render('auth/2fa/disable.html.twig');
    }

    #[Route('/profile/2fa/disable/confirm', name: 'app_2fa_disable_confirm', methods: ['POST'])]
    public function disableConfirm(
        Request $request,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Disable 2FA
        $user->setIsTwoFactorEnabled(false);
        $user->setTotpSecret(null);
        $user->setBackupCodes([]);
        $user->setTrustedDevicesArray([]);
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'L\'authentification à deux facteurs a été désactivée.');
        
        return $this->redirectToRoute('app_2fa_manage');
    }

    #[Route('/profile/2fa/regenerate-backup-codes', name: 'app_2fa_regenerate_backup_codes', methods: ['POST'])]
    public function regenerateBackupCodes(
        Request $request,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('app_2fa_manage');
        }

        // Generate new backup codes
        $user->generateBackupCodes(10);
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Nouveaux codes de sauvegarde générés. Conservez-les en lieu sûr.');

        return $this->redirectToRoute('app_2fa_backup_codes');
    }

    #[Route('/profile/2fa/trusted-devices', name: 'app_2fa_trusted_devices')]
    public function trustedDevices(): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('app_2fa_manage');
        }

        return $this->render('auth/2fa/trusted-devices.html.twig', [
            'trustedDevices' => $user->getTrustedDevicesArray() ?? [],
        ]);
    }

    #[Route('/profile/2fa/trusted-devices/remove/{token}', name: 'app_2fa_trusted_device_remove')]
    public function removeTrustedDevice(
        string $token,
        Request $request,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $user->removeTrustedDevice($token);
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Appareil de confiance supprimé.');

        return $this->redirectToRoute('app_2fa_trusted_devices');
    }

    #[Route('/profile/2fa/trusted-devices/clear-all', name: 'app_2fa_trusted_devices_clear_all', methods: ['POST'])]
    public function clearAllTrustedDevices(
        Request $request,
        DoctrineManagerRegistry $doctrine
    ): Response {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $user->setTrustedDevicesArray([]);
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Tous les appareils de confiance ont été supprimés.');

        return $this->redirectToRoute('app_2fa_trusted_devices');
    }

    #[Route('/2fa/verify', name: 'app_2fa_verify')]
    public function verify(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the user
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Get the last authentication error
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Get the last username entered
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/2fa/verify.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }
    
    #[Route('/2fa/check', name: 'app_2fa_check', methods: ['POST'])]
    public function check(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        LoggerInterface $logger,
        DoctrineManagerRegistry $doctrine
    ): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        
        // Validate CSRF token
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('2fa_verify', $submittedToken)) {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_2fa_verify');
        }
        
        // Check if it's a backup code (from backup_code field)
        $backupCode = $request->request->get('backup_code');
        
        if ($backupCode) {
            // Clean the backup code - convert to uppercase and trim
            $cleanCode = strtoupper(trim($backupCode));
            $noHyphenCode = str_replace('-', '', $cleanCode);
            
            // Get stored codes
            $storedCodes = $user->getBackupCodes() ?? [];
            
            // Calculate hashes for both formats
            $hashWithHyphen = hash('sha256', $cleanCode);
            $hashNoHyphen = hash('sha256', $noHyphenCode);
            
            // Direct comparison against stored hashes
            foreach ($storedCodes as $storedHash) {
                if (hash_equals($storedHash, $hashWithHyphen) || hash_equals($storedHash, $hashNoHyphen)) {
                    // Found match - invalidate this code
                    $user->invalidateBackupCode($storedHash);
                    
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Connexion établie avec un code de sauvegarde.');
                    return $this->redirectToRoute('appointment_patient_dashboard');
                }
            }
            
            $this->addFlash('error', 'Code de sauvegarde invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_2fa_verify');
        }
        
        // Regular TOTP code
        $code = $request->request->get('code');
        
        // Check TOTP code - make sure code is not empty
        if ($code && $totpAuthenticator->checkCode($user, $code)) {
            $this->addFlash('success', 'Authentification à deux facteurs réussie.');
            
            // Redirect to dashboard
            return $this->redirectToRoute('appointment_patient_dashboard');
        }
        
        $this->addFlash('error', 'Code invalide. Veuillez réessayer.');
        
        return $this->redirectToRoute('app_2fa_verify');
    }
}
