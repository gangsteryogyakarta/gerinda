<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFile implements ValidationRule
{
    /**
     * Allowed MIME types for images
     */
    protected array $allowedImageMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed MIME types for documents
     */
    protected array $allowedDocMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Dangerous file extensions that should never be allowed
     */
    protected array $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps',
        'phar', 'inc',
        'exe', 'bat', 'cmd', 'sh', 'bash',
        'js', 'jsx', 'ts', 'tsx',
        'htaccess', 'htpasswd',
        'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
        'jar', 'war',
        'svg', // SVG can contain scripts
    ];

    /**
     * Type of file to validate: 'image', 'document', 'any'
     */
    protected string $type;

    /**
     * Maximum file size in kilobytes
     */
    protected int $maxSize;

    public function __construct(string $type = 'image', int $maxSize = 2048)
    {
        $this->type = $type;
        $this->maxSize = $maxSize;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('File tidak valid.');
            return;
        }

        // Check file size
        if ($value->getSize() > ($this->maxSize * 1024)) {
            $fail("Ukuran file maksimal {$this->maxSize} KB.");
            return;
        }

        // Check for dangerous extensions
        $extension = strtolower($value->getClientOriginalExtension());
        if (in_array($extension, $this->dangerousExtensions)) {
            $fail('Tipe file tidak diizinkan.');
            return;
        }

        // Verify MIME type
        $mimeType = $value->getMimeType();
        
        if ($this->type === 'image') {
            if (!in_array($mimeType, $this->allowedImageMimes)) {
                $fail('File harus berupa gambar (JPEG, PNG, GIF, atau WebP).');
                return;
            }

            // Additional check for images: verify it's actually an image
            if (!$this->isValidImage($value)) {
                $fail('File bukan gambar yang valid.');
                return;
            }
        } elseif ($this->type === 'document') {
            if (!in_array($mimeType, $this->allowedDocMimes)) {
                $fail('File harus berupa dokumen (PDF, Word, atau Excel).');
                return;
            }
        }

        // Check for PHP code in file content (double extension attack)
        if ($this->containsPhpCode($value)) {
            $fail('File mengandung konten yang tidak diizinkan.');
            return;
        }
    }

    /**
     * Verify file is actually a valid image
     */
    protected function isValidImage(UploadedFile $file): bool
    {
        try {
            $imageInfo = @getimagesize($file->getRealPath());
            return $imageInfo !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if file contains PHP code
     */
    protected function containsPhpCode(UploadedFile $file): bool
    {
        $content = file_get_contents($file->getRealPath());
        
        // Check for PHP tags
        $phpPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script[^>]+language[^>]*php/i',
        ];

        foreach ($phpPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
