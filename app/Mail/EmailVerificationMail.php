<?php
// chạy lệnh để tạo: php artisan make:mail EmailVerificationMail
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SentMailLog;

class EmailVerificationMail extends Mailable
{
	use Queueable, SerializesModels;

	public $user;
	public $verificationUrl;
	public string $type;

	public function __construct($user, $verificationUrl, string $type = 'verify_email')
	{
		$this->user = $user;
		$this->verificationUrl = $verificationUrl;
		$this->type = $type;
	}

	public function build()
	{
		$subject = match ($this->type) {
			'reset_password' => 'Đặt lại mật khẩu - Casumina',
			default => 'Kích hoạt tài khoản - Casumina',
		};

		$view = match ($this->type) {
			'reset_password' => 'emails.password-reset',
			default => 'emails.email-verification',
		};

		$log = SentMailLog::where('user_id', $this->user->id)
			->where('type', $this->type)
			->latest('id')
			->first();

		return $this->subject($subject)
			->view($view)
			->with([
				'user' => $this->user,
				'verificationUrl' => $this->verificationUrl,
				'newPassword' => $log->token,
			]);
	}
}
