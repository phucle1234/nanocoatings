<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SentMailLog;
use App\Models\TblNode;
use App\Models\TblNodeDownlineLogF1;
use App\Providers\TelegramServiceProvider;

class EmailVerificationController extends Controller
{
	// Thêm vào đầu class
	private const LINK_EXPIRY_MINUTES = 5;

	public function verify(Request $request, $token)
	{
		$log = SentMailLog::where('type', 'register')
			->where('token', $token)
			->latest('id')
			->first();

		if (!$log) {
			return redirect()->route('login')->withInput()->with('toast_error', 'The activation link is invalid or expired.');
		}


		// Kiểm tra thời gian hiệu lực (5 phút)
		$expiryTime = $log->created_at->addMinutes(self::LINK_EXPIRY_MINUTES);
		if (now()->gt($expiryTime)) {
			return redirect()->route('login')->withInput()->with('toast_error', 'The activation link has expired. Please request a new activation email.');
		}

		$user = User::find($log->user_id);
		if (!$user) {
			return redirect()->route('login')->withInput()->with('toast_error', 'Account does not exist.');
		}

		// Kiểm tra xem user đã active chưa
		if ($user->is_active == '1' && $user->status == 'active') {
			return redirect()->route('login')->withInput()->with('toast_error', 'Your account has already been activated. You can log in now.');
		}

		$user->email_verified_at = now();
		$user->status = 'active';
		$user->is_active = '1';
		$user->save();

		
		$existingNode = TblNode::where('UserID', $user->id)->first();
		if (!$existingNode) {
			TblNode::create([
				'UserID' => $user->id,
				'DateCreate' => now(),
			]);
		}

		$this->upgrade_f1_indirect($user->id, $user->id, 1, now());
		// Có thể xóa log hoặc giữ lại cho mục đích audit
		// $log->delete();

		$telegram = new TelegramServiceProvider();
        $telegram->sendMessage(" KYC Email : " . $user->user_name . " - " . $user->name . " - Ref code: " . $user->TokenID . "  - F1 Info :" . $user->F1UserID);

		return redirect()->route('login')->withInput()->with('toast_success', 'Account has been activated successfully! You can log in now.');
	}

	/**
	 * Hàm upgrade_f1_indirect - chuyển đổi từ PHP thuần sang Laravel
	 */
	private function upgrade_f1_indirect($UserID, $FUserID, $IndirectID = 1, $DateCreate)
	{
		if (intval($UserID) == 0 || $UserID == '' || $IndirectID > 500) return;

		$NodeInfo = User::where('id', $UserID)->first();
		if (!$NodeInfo) return;

		$log = TblNodeDownlineLogF1::where('FUserID', $FUserID)
			->where('UserID', $NodeInfo->F1UserID)
			->first();

		if (!$log && $NodeInfo->F1UserID > 0) {
			TblNodeDownlineLogF1::create([
				'UserID' => $NodeInfo->F1UserID,
				'FUserID' => $FUserID,
				'IndirectID' => $IndirectID,
				'DateCreate' => $DateCreate,
			]);
		}

		if ($NodeInfo->F1UserID > 0) {
			$this->upgrade_f1_indirect($NodeInfo->F1UserID, $FUserID, $IndirectID + 1, $DateCreate);
		}
	}
}
