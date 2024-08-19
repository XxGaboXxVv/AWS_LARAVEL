<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nuevaContraseña;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nuevaContraseña,$details)
    {
        $this->nuevaContraseña = $nuevaContraseña;
        $this->details = $details;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tu nueva contraseña')
                    ->view('emails.newpassword')
                    ->with([
                        'nuevaContraseña' => $this->nuevaContraseña,
                        'details' => $this->details,
                    ]);
                   
    }
}
