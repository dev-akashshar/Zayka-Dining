<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'code', 'expires_at', 'verified_at'])]
class Otp extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Generate a new 6-digit OTP for the given email, active for 15 minutes.
     */
    public static function generateForEmail(string $email): self
    {
        // Expire any existing OTPs for this email
        self::where('email', $email)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        // Create new OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * Verify the OTP for the email.
     */
    public static function verifyCode(string $email, string $code): bool
    {
        $otp = self::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if ($otp) {
            $otp->update(['verified_at' => now()]);

            return true;
        }

        return false;
    }
}
