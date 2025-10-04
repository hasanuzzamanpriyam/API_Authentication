<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterOtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to Our App!')
            ->html("<h1>Welcome, {$this->user->name}!</h1>
                    <p>Thank you for registering. Your One-Time Password (OTP) is: <strong>{$this->user->otp}</strong></p>
                    <p>This OTP is valid for a short period. Please do not share it with anyone.</p>");
    }
}
