<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'role', type: 'string')]
#[ORM\DiscriminatorMap([
    'ROLE_PATIENT' => Patient::class,
    'ROLE_MEDECIN' => Medecin::class,
    'ROLE_COACH' => Coach::class,
    'ROLE_NUTRITIONIST' => Nutritionist::class,
    'ROLE_ADMIN' => Administrator::class,
])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée')]
#[UniqueEntity(fields: ['licenseNumber'], message: 'Ce numéro de licence est déjà utilisé', groups: ['Professional'])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface, BackupCodeInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, length: 36)]
    private ?string $uuid = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Veuillez entrer une adresse email valide')]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit contenir au moins 2 caractères')]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins 2 caractères')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^[+]?[0-9\s\-()]+$/', message: 'Le numero de telephone doit contenir uniquement des chiffres et les caracteres + - ( )')]
    private ?string $phone = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'avatar doit être une URL valide')]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le numéro de licence est obligatoire', groups: ['Professional'])]
    private ?string $licenseNumber = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column]
    private int $loginAttempts = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lockedUntil = null;

    #[ORM\Column]
    private bool $isEmailVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $emailVerificationExpiresAt = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $lastSessionId = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[UniqueEntity(fields: ['googleId'], message: 'Ce compte Google est déjà lié à un autre utilisateur')]
    private ?string $googleId = null;

    // ============================================
    // Two-Factor Authentication Fields (TOTP)
    // ============================================
    
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isTwoFactorEnabled = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    // ============================================
    // Backup Codes
    // ============================================
    
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $backupCodes = [];
    
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $plainBackupCodes = [];

    // ============================================
    // Trusted Devices (custom implementation)
    // ============================================
    
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $trustedDevices = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->uuid = Uuid::v4()->toRfc4122();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Returns the user identifier (UUID).
     * Alias for getUuid() for compatibility.
     */
    public function getId(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): self
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function getLoginAttempts(): int
    {
        return $this->loginAttempts;
    }

    public function setLoginAttempts(int $loginAttempts): self
    {
        $this->loginAttempts = $loginAttempts;
        return $this;
    }

    public function incrementLoginAttempts(): self
    {
        $this->loginAttempts++;
        return $this;
    }

    public function resetLoginAttempts(): self
    {
        $this->loginAttempts = 0;
        return $this;
    }

    public function getLockedUntil(): ?\DateTimeInterface
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeInterface $lockedUntil): self
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    public function isLocked(): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > new \DateTime();
    }

    public function isEmailVerified(): bool
    {
        return $this->isEmailVerified;
    }

    public function setIsEmailVerified(bool $isEmailVerified): self
    {
        $this->isEmailVerified = $isEmailVerified;
        return $this;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;
        return $this;
    }

    public function getEmailVerificationExpiresAt(): ?\DateTimeInterface
    {
        return $this->emailVerificationExpiresAt;
    }

    public function setEmailVerificationExpiresAt(?\DateTimeInterface $emailVerificationExpiresAt): self
    {
        $this->emailVerificationExpiresAt = $emailVerificationExpiresAt;
        return $this;
    }

    public function getLastSessionId(): ?string
    {
        return $this->lastSessionId;
    }

    public function setLastSessionId(?string $lastSessionId): self
    {
        $this->lastSessionId = $lastSessionId;
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    // ============================================
    // Two-Factor Authentication Getters/Setters
    // ============================================

    public function isTwoFactorEnabled(): bool
    {
        return $this->isTwoFactorEnabled;
    }

    public function setIsTwoFactorEnabled(bool $isTwoFactorEnabled): self
    {
        $this->isTwoFactorEnabled = $isTwoFactorEnabled;
        return $this;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;
        return $this;
    }

    // ============================================
    // Backup Codes Getters/Setters
    // ============================================

    public function getBackupCodesArray(): ?array
    {
        return $this->backupCodes;
    }

    public function setBackupCodesArray(?array $backupCodes): self
    {
        $this->backupCodes = $backupCodes;
        return $this;
    }

    // ============================================
    // Trusted Devices Getters/Setters (Custom)
    // ============================================

    public function getTrustedDevicesArray(): ?array
    {
        return $this->trustedDevices;
    }

    public function setTrustedDevicesArray(?array $trustedDevices): self
    {
        $this->trustedDevices = $trustedDevices;
        return $this;
    }

    // ============================================
    // TwoFactorInterface Implementation (TOTP)
    // ============================================

    /**
     * Return true if the user should do TOTP authentication.
     */
    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->isTwoFactorEnabled;
    }

    /**
     * Return the user name. This is used in QR code generation.
     */
    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    /**
     * Return the configuration for TOTP authentication.
     */
    public function getTotpAuthenticationConfiguration(): ?\Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface
    {
        // Return configuration if secret exists (during setup or after 2FA is enabled)
        if (!$this->totpSecret) {
            return null;
        }
        
        return new TotpConfiguration(
            $this->totpSecret,
            TotpConfiguration::ALGORITHM_SHA1,
            30,
            6
        );
    }

    // ============================================
    // BackupCodeInterface Implementation
    // ============================================

    /**
     * Check if a backup code is valid
     */
    public function isBackupCode(string $code): bool
    {
        $codes = $this->backupCodes ?? [];
        
        // Hash the entered code to compare with stored hashes
        $hashedCode = hash('sha256', $code);
        
        foreach ($codes as $storedCode) {
            if (hash_equals($storedCode, $hashedCode)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Invalidate a backup code after use
     * @param string $code The plain code or already-hashed code
     */
    public function invalidateBackupCode(string $code): void
    {
        $codes = $this->backupCodes ?? [];
        
        // Check if the code looks like a SHA256 hash (64 characters hex)
        $isAlreadyHashed = preg_match('/^[a-f0-9]{64}$/i', $code);
        
        if ($isAlreadyHashed) {
            // Code is already hashed, use it directly
            $hashToRemove = $code;
        } else {
            // Plain code, hash it
            $hashToRemove = hash('sha256', $code);
        }
        
        // Remove the used code from the array
        $codes = array_filter($codes, function($storedCode) use ($hashToRemove) {
            return !hash_equals($storedCode, $hashToRemove);
        });
        
        $this->backupCodes = array_values($codes);
        
        // Also remove from plain codes for display
        $plainCodes = $this->plainBackupCodes ?? [];
        if (!$isAlreadyHashed) {
            $this->plainBackupCodes = array_values(array_filter($plainCodes, function($plainCode) use ($code) {
                return strtoupper($plainCode) !== strtoupper($code);
            }));
        }
    }

    /**
     * Generate new backup codes
     * Stores PLAIN codes for display, hashed for verification
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $plainCodes = [];
        $hashedCodes = [];
        
        for ($i = 0; $i < $count; $i++) {
            // Generate a random 8-character code with format XXXX-XXXX
            $code = strtoupper(sprintf('%04s-%04s', 
                bin2hex(random_bytes(2)), 
                bin2hex(random_bytes(2))
            ));
            
            $plainCodes[] = $code;
            $hashedCodes[] = hash('sha256', $code);
        }
        
        // Store hashed codes for verification
        $this->backupCodes = $hashedCodes;
        // Store plain codes for display
        $this->plainBackupCodes = $plainCodes;
        
        // Return the plain codes for user to save
        return $plainCodes;
    }
    
    /**
     * Get plain backup codes for display
     */
    public function getPlainBackupCodes(): array
    {
        return $this->plainBackupCodes ?? [];
    }

    /**
     * Get the backup codes (for interface compatibility)
     */
    public function getBackupCodes(): array
    {
        return $this->backupCodes ?? [];
    }

    /**
     * Set the backup codes (for interface compatibility)
     */
    public function setBackupCodes(array $codes): void
    {
        $this->backupCodes = $codes;
    }

    // ============================================
    // Custom Trusted Devices Methods
    // ============================================

    /**
     * Get the trusted device identifier
     */
    public function getTrustedDeviceIdentifier(): string
    {
        return $this->uuid;
    }

    /**
     * Get trusted devices list
     */
    public function getTrustedDevices(): array
    {
        return $this->trustedDevices ?? [];
    }

    /**
     * Add a trusted device
     */
    public function addTrustedDevice(string $deviceToken, \DateTimeInterface $expiresAt): void
    {
        $devices = $this->getTrustedDevices();
        $devices[] = [
            'token' => $deviceToken,
            'expiresAt' => $expiresAt->format('Y-m-d H:i:s'),
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        
        $this->trustedDevices = $devices;
    }

    /**
     * Remove a trusted device
     */
    public function removeTrustedDevice(string $deviceToken): void
    {
        $devices = $this->getTrustedDevices();
        $devices = array_filter($devices, function($device) use ($deviceToken) {
            return $device['token'] !== $deviceToken;
        });
        
        $this->trustedDevices = array_values($devices);
    }

    /**
     * Check if a device token is trusted
     */
    public function isTrustedDevice(string $deviceToken): bool
    {
        $devices = $this->getTrustedDevices();
        
        foreach ($devices as $device) {
            if ($device['token'] === $deviceToken) {
                // Check if not expired
                $expiresAt = new \DateTime($device['expiresAt']);
                if ($expiresAt > new \DateTime()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Clean up expired trusted devices
     */
    public function cleanupExpiredTrustedDevices(): void
    {
        $devices = $this->getTrustedDevices();
        $now = new \DateTime();
        
        $devices = array_filter($devices, function($device) use ($now) {
            $expiresAt = new \DateTime($device['expiresAt']);
            return $expiresAt > $now;
        });
        
        $this->trustedDevices = array_values($devices);
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Symfony Security Interface Methods
    public function getRoles(): array
    {
        return [$this->getDiscriminatorValue() ?? 'ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary sensitive data
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    abstract public function getDiscriminatorValue(): string;
}
