<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

class Referral
{
    public function insert(array $data): bool
    {
        $sql = 'INSERT INTO referral_entries (
                    your_name,
                    your_email,
                    your_mobile,
                    enrolled_candidate,
                    candidate_reference_name,
                    know_about_us,
                    referral_name,
                    referral_email,
                    referral_mobile,
                    referral_whatsapp,
                    linkedin_url,
                    visa_status,
                    resume_file,
                    created_at
                ) VALUES (
                    :your_name,
                    :your_email,
                    :your_mobile,
                    :enrolled_candidate,
                    :candidate_reference_name,
                    :know_about_us,
                    :referral_name,
                    :referral_email,
                    :referral_mobile,
                    :referral_whatsapp,
                    :linkedin_url,
                    :visa_status,
                    :resume_file,
                    NOW()
                )';

        try {
            Database::getInstance()->query($sql, [
                'your_name' => $data['your_name'] ?? '',
                'your_email' => $data['your_email'] ?? '',
                'your_mobile' => $data['your_mobile'] ?? '',
                'enrolled_candidate' => $data['enrolled_candidate'] ?? '',
                'candidate_reference_name' => $data['candidate_reference_name'] ?? '',
                'know_about_us' => $data['know_about_us'] ?? '',
                'referral_name' => $data['referral_name'] ?? '',
                'referral_email' => $data['referral_email'] ?? '',
                'referral_mobile' => $data['referral_mobile'] ?? '',
                'referral_whatsapp' => $data['referral_whatsapp'] ?? '',
                'linkedin_url' => $data['linkedin_url'] ?? '',
                'visa_status' => $data['visa_status'] ?? '',
                'resume_file' => $data['resume_file'] ?? null,
            ]);

            return true;
        } catch (PDOException) {
            return false;
        }
    }
}
