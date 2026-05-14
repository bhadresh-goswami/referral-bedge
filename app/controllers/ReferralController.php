<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Referral;

class ReferralController extends Controller
{
    private const ALLOWED_UPLOAD_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function index(): void
    {
        $this->view('referral/index');
    }

    public function submit(): string
    {
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

        $errors = $this->validate($payload, $_FILES['resume_file'] ?? null);
        if ($errors !== []) {
            http_response_code(422);
            return json_encode(['success' => false, 'errors' => $errors], JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
        }

        $payload['resume_file'] = $this->storeUpload($_FILES['resume_file'] ?? null);

        $saved = (new Referral())->insert($payload);
        if (!$saved) {
            http_response_code(500);
            return json_encode(['success' => false, 'message' => 'Unable to save referral right now.'], JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
        }

        return json_encode(['success' => true, 'message' => 'Referral submitted successfully.'], JSON_UNESCAPED_SLASHES) ?: '{"success":true}';
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
            if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $errors['resume_file'] = 'Upload failed.';
                return $errors;
            }

            if (($upload['size'] ?? 0) > UPLOAD_MAX_SIZE) {
                $errors['resume_file'] = 'File exceeds maximum allowed size.';
            }

            $type = mime_content_type($upload['tmp_name'] ?? '') ?: '';
            if (!in_array($type, self::ALLOWED_UPLOAD_TYPES, true)) {
                $errors['resume_file'] = 'Invalid file type. Allowed: PDF, DOC, DOCX.';
            }
        }

        return $errors;
    }

    private function storeUpload(?array $upload): ?string
    {
        if ($upload === null || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0775, true);
        }

        $extension = pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION);
        $filename = uniqid('resume_', true) . ($extension !== '' ? '.' . strtolower($extension) : '');
        $destination = rtrim(UPLOAD_PATH, '/') . '/' . $filename;

        if (move_uploaded_file($upload['tmp_name'], $destination)) {
            return $filename;
        }

        return null;
    }
}
