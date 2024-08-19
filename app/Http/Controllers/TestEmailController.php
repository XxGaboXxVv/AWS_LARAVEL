<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        $email = 'tu_email@gmail.com'; // Cambia esto por tu dirección de correo electrónico

        try {
            Mail::raw('This is a test email.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });

            return 'Test email sent successfully.';
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return 'Failed to send test email.';
        }
    }
}
