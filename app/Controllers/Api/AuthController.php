<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class AuthController extends BaseApiController
{
    public function splash()
    {
        return $this->success([
            'app' => [
                'name'        => 'ITDeskGo',
                'description' => 'IT Helpdesk, Knowledge Base, and Asset Tracker',
                'version'     => '1.0.0',
            ],
            'auth_stack' => [
                'Splash Screen',
                'Login Screen',
                'Forgot Password Screen',
            ],
            'tabs' => [
                SchemaModel::ROLE_EMPLOYEE => ['Home', 'Tickets', 'Knowledge Base', 'Assets', 'Profile'],
                SchemaModel::ROLE_IT_STAFF => ['Dashboard', 'Tickets', 'Assets', 'Knowledge Base', 'Profile'],
                SchemaModel::ROLE_ADMIN    => ['Dashboard', 'Users', 'Tickets', 'Assets', 'Settings'],
            ],
            'roles' => SchemaModel::roles(),
        ]);
    }

    public function login()
    {
        $payload = $this->payload(['email', 'password']);
        $errors  = $this->requireFields($payload, ['email', 'password']);

        if ($errors !== []) {
            return $this->badRequest('Please provide your email and password.', $errors);
        }

        $user = $this->db->table(SchemaModel::TABLE_USERS)
            ->where('email', strtolower(trim((string) $payload['email'])))
            ->get()
            ->getRowArray();

        if (! is_array($user)) {
            return $this->badRequest('Invalid email or password.');
        }

        if (($user['status'] ?? SchemaModel::USER_STATUS_ACTIVE) !== SchemaModel::USER_STATUS_ACTIVE) {
            return $this->forbidden('Your account is not active.');
        }

        $passwordHash = $user['password_hash'] ?? $user['password'] ?? '';

        if (! is_string($passwordHash) || ! password_verify((string) $payload['password'], $passwordHash)) {
            return $this->badRequest('Invalid email or password.');
        }

        $role = null;

        if (! empty($user['role_id'])) {
            $role = $this->rowById(SchemaModel::TABLE_ROLES, (int) $user['role_id']);
        }

        $roleSlug = (string) ($role['slug'] ?? $role['name'] ?? SchemaModel::ROLE_EMPLOYEE);
        $roleSlug = strtolower(str_replace(' ', '_', $roleSlug));

        return $this->success([
            'user'  => $this->publicUser($user),
            'role'  => $role,
            'tabs'  => $this->tabsForRole($roleSlug),
            'token' => bin2hex(random_bytes(32)),
        ], 'Login successful.');
    }

    public function forgotPassword()
    {
        $payload = $this->payload(['email']);
        $errors  = $this->requireFields($payload, ['email']);

        if ($errors !== []) {
            return $this->badRequest('Please provide your email address.', $errors);
        }

        $email = strtolower(trim((string) $payload['email']));
        $user  = $this->db->table(SchemaModel::TABLE_USERS)->where('email', $email)->get()->getRowArray();

        if (is_array($user)) {
            if (! $this->tableHasColumn(SchemaModel::TABLE_USERS, 'reset_token')) {
                log_message('error', 'Password reset failed because the users table has no reset_token column.');

                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Password reset storage is not configured.',
                ], 500);
            }

            $resetToken = bin2hex(random_bytes(32));
            $resetData  = [
                'reset_token' => password_hash($resetToken, PASSWORD_DEFAULT),
            ];

            if ($this->tableHasColumn(SchemaModel::TABLE_USERS, 'reset_token_expires_at')) {
                $resetData['reset_token_expires_at'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
            }

            $this->db->table(SchemaModel::TABLE_USERS)
                ->where('id', (int) $user['id'])
                ->update($this->withTimestamps(SchemaModel::TABLE_USERS, $resetData));

            $resetUrl = $this->passwordResetUrl($email, $resetToken);

            if (! $this->sendPasswordResetEmail($email, $resetUrl)) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Password reset email is not configured or could not be sent.',
                ], 500);
            }
        }

        return $this->success([
            'email' => $email,
        ], 'If the email exists, password reset instructions will be sent to your email.');
    }

    public function profile(int $id)
    {
        $user = $this->rowById(SchemaModel::TABLE_USERS, $id);

        if ($user === null) {
            return $this->notFound('User not found.');
        }

        return $this->success([
            'user' => $this->publicUser($user),
        ]);
    }

    public function updateProfile(int $id)
    {
        $payload = $this->payload([
            'name',
            'first_name',
            'last_name',
            'phone',
            'avatar',
            'department_id',
        ]);

        $payload = $this->onlyExistingColumns(SchemaModel::TABLE_USERS, $payload);
        $payload = $this->withTimestamps(SchemaModel::TABLE_USERS, $payload);

        if ($payload === []) {
            return $this->badRequest('No valid profile fields were provided.');
        }

        if ($this->rowById(SchemaModel::TABLE_USERS, $id) === null) {
            return $this->notFound('User not found.');
        }

        $this->db->table(SchemaModel::TABLE_USERS)->where('id', $id)->update($payload);

        return $this->success([
            'user' => $this->publicUser($this->rowById(SchemaModel::TABLE_USERS, $id) ?? []),
        ], 'Profile updated successfully.');
    }

    public function changePassword(int $id)
    {
        $payload = $this->payload(['current_password', 'new_password']);
        $errors  = $this->requireFields($payload, ['current_password', 'new_password']);

        if ($errors !== []) {
            return $this->badRequest('Please complete the password fields.', $errors);
        }

        if (strlen((string) $payload['new_password']) < 8) {
            return $this->badRequest('New password must be at least 8 characters long.');
        }

        $user = $this->rowById(SchemaModel::TABLE_USERS, $id);

        if ($user === null) {
            return $this->notFound('User not found.');
        }

        $passwordHash = $user['password_hash'] ?? $user['password'] ?? '';

        if (! is_string($passwordHash) || ! password_verify((string) $payload['current_password'], $passwordHash)) {
            return $this->badRequest('Current password is incorrect.');
        }

        $passwordColumn = $this->tableHasColumn(SchemaModel::TABLE_USERS, 'password_hash') ? 'password_hash' : 'password';

        $this->db->table(SchemaModel::TABLE_USERS)
            ->where('id', $id)
            ->update($this->withTimestamps(SchemaModel::TABLE_USERS, [
                $passwordColumn => password_hash((string) $payload['new_password'], PASSWORD_DEFAULT),
            ]));

        return $this->success([], 'Password changed successfully.');
    }

    private function sendPasswordResetEmail(string $email, string $resetUrl): bool
    {
        $gmailUser        = trim((string) (env('GMAIL_USER') ?: ''));
        $gmailAppPassword = trim((string) (env('GMAIL_APP_PASSWORD') ?: ''));

        if ($gmailUser === '' || $gmailAppPassword === '') {
            log_message('error', 'Password reset email failed because GMAIL_USER or GMAIL_APP_PASSWORD is missing.');

            return false;
        }

        $mailer = service('email');
        $mailer->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => 'smtp.gmail.com',
            'SMTPUser'   => $gmailUser,
            'SMTPPass'   => $gmailAppPassword,
            'SMTPPort'   => 587,
            'SMTPCrypto' => 'tls',
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",
            'wordWrap'   => true,
        ]);

        $mailer->setFrom($gmailUser, $this->emailSenderName());
        $mailer->setTo($email);
        $mailer->setSubject('ITDeskGo Password Reset');
        $mailer->setMessage($this->passwordResetEmailHtml($resetUrl));
        $mailer->setAltMessage($this->passwordResetEmailText($resetUrl));

        if ($mailer->send()) {
            return true;
        }

        log_message('error', 'Password reset email failed: ' . print_r($mailer->printDebugger(['headers', 'subject']), true));

        return false;
    }

    private function passwordResetUrl(string $email, string $token): string
    {
        $frontendUrl = trim((string) (env('EXPO_FRONTEND_URL') ?: env('FRONTEND_URL') ?: env('app.baseURL') ?: base_url()));
        $query       = http_build_query([
            'email' => $email,
            'token' => $token,
        ]);

        return rtrim($frontendUrl, '/') . '/reset-password?' . $query;
    }

    private function passwordResetEmailHtml(string $resetUrl): string
    {
        $safeUrl = esc($resetUrl, 'html');

        return <<<HTML
            <div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
                <h2 style="color: #1455D9;">Reset your ITDeskGo password</h2>
                <p>We received a request to reset your ITDeskGo account password.</p>
                <p>
                    <a href="{$safeUrl}" style="background: #1455D9; color: #FFFFFF; padding: 12px 18px; border-radius: 999px; display: inline-block; text-decoration: none; font-weight: 700;">
                        Reset Password
                    </a>
                </p>
                <p>If the button does not work, copy and paste this link into your browser:</p>
                <p><a href="{$safeUrl}">{$safeUrl}</a></p>
                <p>This link expires in 1 hour. If you did not request this, you can ignore this email.</p>
            </div>
            HTML;
    }

    private function passwordResetEmailText(string $resetUrl): string
    {
        return "Reset your ITDeskGo password using this link: {$resetUrl}\n\nThis link expires in 1 hour. If you did not request this, you can ignore this email.";
    }

    private function emailSenderName(): string
    {
        $senderName = trim((string) (env('GMAIL_FROM_NAME') ?: env('MAIL_FROM_NAME') ?: 'ITDeskGo'));

        return $senderName !== '' ? $senderName : 'ITDeskGo';
    }

    private function tabsForRole(string $role): array
    {
        return match ($role) {
            SchemaModel::ROLE_ADMIN, SchemaModel::ROLE_SUPERADMIN => ['Dashboard', 'Users', 'Tickets', 'Assets', 'Settings'],
            SchemaModel::ROLE_IT_STAFF => ['Dashboard', 'Tickets', 'Assets', 'Knowledge Base', 'Profile'],
            default => ['Home', 'Tickets', 'Knowledge Base', 'Assets', 'Profile'],
        };
    }
}
