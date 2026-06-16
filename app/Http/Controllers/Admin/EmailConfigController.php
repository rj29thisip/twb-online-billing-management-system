<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailConfig;
use App\Services\AuditLogger;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class EmailConfigController extends Controller
{
    public function __construct(protected EmailService $emailService) {}

    public function index()
    {
        $configs = EmailConfig::orderByDesc('is_active')->orderBy('id')->paginate(10);
        return view('admin.email-config.index', compact('configs'));
    }

    public function create() { return view('admin.email-config.create'); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (!empty($data['is_active'])) {
            EmailConfig::query()->update(['is_active' => false]);
        }
        $config = EmailConfig::create($data);

        AuditLogger::log('email_config_created', 'EmailConfig', $config->id, [], [
            'from_address' => $config->from_address,
            'host'         => $config->host,
            'mailer'       => $config->mailer,
            'is_active'    => $config->is_active,
        ], "Email config created: {$config->from_address}");

        return redirect()->route('admin.email-config.index')
            ->with('success', 'Email configuration created.');
    }

    public function edit(EmailConfig $emailConfig)
    {
        return view('admin.email-config.edit', compact('emailConfig'));
    }

    public function update(Request $request, EmailConfig $emailConfig)
    {
        $data = $this->validated($request, $emailConfig->id);
        if (empty($data['password'])) unset($data['password']);
        if (!empty($data['is_active'])) {
            EmailConfig::where('id', '!=', $emailConfig->id)->update(['is_active' => false]);
        }

        $old = $emailConfig->only(['from_address', 'host', 'is_active']);
        $emailConfig->update($data);

        AuditLogger::log('email_config_updated', 'EmailConfig', $emailConfig->id, $old, [
            'from_address' => $emailConfig->from_address,
            'is_active'    => $emailConfig->is_active,
        ], "Email config updated: {$emailConfig->from_address}");

        return redirect()->route('admin.email-config.index')
            ->with('success', 'Email configuration updated.');
    }

    public function destroy(Request $request, EmailConfig $emailConfig)
    {
        if ($emailConfig->is_active) {
            return back()->withErrors(['error' => 'Cannot delete the active config. Activate another first.']);
        }
        $id      = $emailConfig->id;
        $address = $emailConfig->from_address;
        $emailConfig->delete();

        AuditLogger::log('email_config_deleted', 'EmailConfig', $id, [
            'from_address' => $address,
        ], [], "Email config deleted: {$address}");

        return redirect()->route('admin.email-config.index')
            ->with('success', 'Configuration deleted.');
    }

    public function sendTest(Request $request, EmailConfig $emailConfig)
    {
        $request->validate(['test_email' => 'required|email']);

        Config::set('mail.mailers.smtp.host',       $emailConfig->host);
        Config::set('mail.mailers.smtp.port',       $emailConfig->port);
        Config::set('mail.mailers.smtp.username',   $emailConfig->username);
        Config::set('mail.mailers.smtp.password',   $emailConfig->password);
        Config::set('mail.mailers.smtp.encryption', $emailConfig->encryption === 'none' ? null : $emailConfig->encryption);
        Config::set('mail.from.address',            $emailConfig->from_address);
        Config::set('mail.from.name',               $emailConfig->from_name);

        $sent = $this->emailService->sendTestEmail($request->test_email);

        AuditLogger::log('email_config_test', 'EmailConfig', $emailConfig->id, [], [
            'test_to' => $request->test_email,
            'success' => $sent,
        ], "Test email via config #{$emailConfig->id} to {$request->test_email}: ".($sent ? 'success' : 'failed'));

        return $sent
            ? back()->with('success', "Test email sent to {$request->test_email}!")
            : back()->withErrors(['error' => 'Failed to send. Check your SMTP credentials.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'mailer'       => 'required|in:smtp,sendmail,mailgun,ses,postmark',
            'host'         => 'required_if:mailer,smtp|nullable|string|max:255',
            'port'         => 'required_if:mailer,smtp|nullable|integer|min:1|max:65535',
            'username'     => 'nullable|string|max:255',
            'password'     => 'nullable|string|max:255',
            'encryption'   => 'required|in:tls,ssl,none',
            'from_address' => 'required|email|max:255',
            'from_name'    => 'required|string|max:255',
            'is_active'    => 'boolean',
            'notes'        => 'nullable|string|max:1000',
        ]);
    }
}
