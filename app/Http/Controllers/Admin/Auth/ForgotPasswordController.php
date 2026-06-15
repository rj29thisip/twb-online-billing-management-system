<?php
namespace App\Http\Controllers\Admin\Auth;
use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct(protected EmailService $emailService) {}

    public function showLinkRequestForm() {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('status', 'If that email is in our system, you will receive a reset link shortly.');
        }
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email, 'token' => Hash::make($token), 'created_at' => Carbon::now(),
        ]);
        $resetUrl = route('admin.password.reset', ['token' => $token, 'email' => $request->email]);
        $sent = $this->emailService->sendPasswordReset($request->email, $user->name, $resetUrl);
        if (!$sent) {
            return back()->withErrors(['email' => 'Email could not be sent. Please contact your administrator — email configuration may not be set up.']);
        }
        return back()->with('status', 'If that email is in our system, you will receive a reset link shortly.');
    }

    public function showResetForm(Request $request, string $token) {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request) {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => 'required|min:8|confirmed']);
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This link has expired. Please request a new one.']);
        }
        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password), 'must_change_password' => false]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        AuditLogger::log('password_reset', 'User', $user->id, [], ['email' => $user->email], "Password reset via email for {$user->email}");
        return redirect()->route('login')->with('status', 'Your password has been reset. You may now log in.');
    }
}
