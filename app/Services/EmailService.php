<?php
namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Mail\CustomerPortalCredentialsMail;
use App\Models\EmailConfig;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailService
{
    protected function boot(): bool
    {
        $config = EmailConfig::getActive();
        if (!$config) {
            Log::warning('EmailService: No active email config found.');
            return false;
        }
        EmailConfig::applyActive();
        return true;
    }

    public function sendPasswordReset(string $toEmail, string $userName, string $resetUrl): bool
    {
        if (!$this->boot()) return false;
        try {
            Mail::to($toEmail)->send(new PasswordResetMail($resetUrl, $userName));
            $this->audit('password_reset_email_sent', "Password reset email sent to {$toEmail}");
            return true;
        } catch (Throwable $e) {
            Log::error("EmailService: Failed password reset to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    public function sendCustomerPortalCredentials(string $toEmail, string $customerName, string $temporaryPassword): bool
    {
        if (!$this->boot()) return false;
        try {
            $loginUrl = route('login');
            Mail::to($toEmail)->send(new CustomerPortalCredentialsMail($customerName, $toEmail, $temporaryPassword, $loginUrl));
            $this->audit('customer_portal_credentials_sent', "Portal credentials sent to {$toEmail} for {$customerName}");
            return true;
        } catch (Throwable $e) {
            Log::error("EmailService: Failed portal credentials to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    public function sendTestEmail(string $toEmail): bool
    {
        if (!$this->boot()) return false;
        try {
            Mail::raw('This is a test email from the TWB Water Billing System. Your email configuration is working correctly.',
                fn($m) => $m->to($toEmail)->subject('TWB Email Config – Test Email'));
            $this->audit('test_email_sent', "Test email sent to {$toEmail}");
            return true;
        } catch (Throwable $e) {
            Log::error("EmailService test email failed: " . $e->getMessage());
            return false;
        }
    }

    protected function audit(string $action, string $description): void
    {
        try {
            AuditLogger::log($action, 'Email', null, [], ['note' => $description]);
        } catch (Throwable) {}
    }
}
