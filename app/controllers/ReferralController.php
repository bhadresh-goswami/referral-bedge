<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Referral;
use RuntimeException;
use Throwable;

class ReferralController extends Controller
{
    private const ALLOWED_UPLOAD_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private const ALLOWED_UPLOAD_EXTENSIONS = ['pdf', 'doc', 'docx'];

    public function index(): void
    {
        $this->view('referral/index');
    }

    public function submit(): string
    {
        try {
            $payload = [
                'your_name' => $this->sanitize($_POST['your_name'] ?? ''),
                'your_email' => $this->sanitize($_POST['your_email'] ?? ''),
                'your_mobile' => $this->sanitize($_POST['your_mobile'] ?? ''),
                'enrolled_candidate' => $this->sanitize($_POST['enrolled_candidate'] ?? ''),
                'candidate_reference_name' => $this->sanitize($_POST['candidate_reference_name'] ?? ''),
                'know_about_us' => $this->sanitize($_POST['know_about_us'] ?? ''),
                'referral_name' => $this->sanitize($_POST['referral_name'] ?? ''),
                'referral_email' => $this->sanitize($_POST['referral_email'] ?? ''),
                'referral_mobile' => $this->sanitize($_POST['referral_mobile'] ?? ''),
                'referral_whatsapp' => $this->sanitize($_POST['referral_whatsapp'] ?? ''),
                'linkedin_url' => $this->sanitize($_POST['linkedin_url'] ?? ''),
                'visa_status' => $this->sanitize($_POST['visa_status'] ?? ''),
            ];

            $upload = $_FILES['resume_file'] ?? null;
            $errors = $this->validate($payload, $upload);
            if ($errors !== []) {
                http_response_code(422);
                return json_encode(['success' => false, 'errors' => $errors], JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
            }

            $payload['resume_file'] = $this->storeUpload($upload);

            $saved = (new Referral())->insert($payload);
            if (!$saved) {
                $this->logError('Referral DB insert failed', ['payload' => $payload]);
                http_response_code(500);
                return json_encode(['success' => false, 'message' => 'Unable to save referral right now. Please try again.'], JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
            }

            return json_encode(['success' => true, 'message' => 'Referral submitted successfully.'], JSON_UNESCAPED_SLASHES) ?: '{"success":true}';
        } catch (Throwable $exception) {
            $this->logError('Referral submit exception: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);
            http_response_code(500);
            return json_encode(['success' => false, 'message' => 'Unexpected server error. Please try again later.'], JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
        }
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    private function validate(array $data, ?array $upload): array
    {
        $errors = [];

        foreach (['your_name', 'your_email', 'referral_name', 'referral_email', 'referral_mobile'] as $required) {
            if (($data[$required] ?? '') === '') {
                $errors[$required] = 'This field is required.';
            }
        }

        if (($data['your_email'] ?? '') !== '' && !filter_var($data['your_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['your_email'] = 'Invalid email format.';
        }

        if (($data['referral_email'] ?? '') !== '' && !filter_var($data['referral_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['referral_email'] = 'Invalid email format.';
        }

        foreach (['your_mobile', 'referral_mobile', 'referral_whatsapp'] as $phoneField) {
            $value = $data[$phoneField] ?? '';
            if ($value !== '' && !preg_match('/^[0-9+()\-\s]{7,20}$/', $value)) {
                $errors[$phoneField] = 'Invalid phone number format.';
            }
        }

        if ($upload !== null && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploadError = $upload['error'] ?? UPLOAD_ERR_OK;
            if ($uploadError !== UPLOAD_ERR_OK) {
                $errors['resume_file'] = $this->uploadErrorMessage($uploadError);
                return $errors;
            }

            if (!is_uploaded_file((string) ($upload['tmp_name'] ?? ''))) {
                $errors['resume_file'] = 'Invalid upload source.';
                return $errors;
            }

            if (($upload['size'] ?? 0) > UPLOAD_MAX_SIZE) {
                $errors['resume_file'] = 'File exceeds maximum allowed size (5MB).';
            }

            $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_UPLOAD_EXTENSIONS, true)) {
                $errors['resume_file'] = 'Invalid file extension. Allowed: PDF, DOC, DOCX.';
            }

            $type = mime_content_type((string) ($upload['tmp_name'] ?? '')) ?: '';
            if (!in_array($type, self::ALLOWED_UPLOAD_TYPES, true)) {
                $errors['resume_file'] = 'Invalid file type. Allowed: PDF, DOC, DOCX.';
            }

            $content = file_get_contents((string) ($upload['tmp_name'] ?? ''), false, null, 0, 4096);
            if ($content !== false && preg_match('/<\?php|<script|MZ/i', $content)) {
                $errors['resume_file'] = 'Executable content is not allowed.';
            }
        }

        return $errors;
    }

    private function storeUpload(?array $upload): string|false|null
    {
        if ($upload === null || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (!is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0775, true) && !is_dir(UPLOAD_PATH)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
        $destination = rtrim(UPLOAD_PATH, '/') . '/' . $filename;

        if (!move_uploaded_file((string) ($upload['tmp_name'] ?? ''), $destination)) {
            $this->logError('Failed to move uploaded file', ['filename' => $filename]);
            throw new RuntimeException('Failed to store uploaded file.');
        }

        chmod($destination, 0644);

        return $filename;
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded file is too large (max 5MB).',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write uploaded file.',
            UPLOAD_ERR_EXTENSION => 'A server extension blocked the upload.',
            default => 'Upload failed. Please try again.',
        };
    }

    private function logError(string $message, array $context = []): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        file_put_contents($logDir . '/referral.log', $line, FILE_APPEND);
    }
}

