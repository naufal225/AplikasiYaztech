<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $namaPengaju;
    public $pesan;
    public $namaApprover;
    public $linkTanggapan;
    public $emailPengaju;

    public function __construct($namaPengaju, $pesan, $namaApprover, $linkTanggapan, $emailPengaju = null)
    {
        $this->namaPengaju = $namaPengaju;
        $this->pesan = $pesan;
        $this->namaApprover = $namaApprover;
        $this->linkTanggapan = $linkTanggapan;
        $this->emailPengaju = $emailPengaju;
    }

    public function build()
    {
        return $this->subject('Hai ' . $this->namaApprover . ', ada request baru dari ' . $this->namaPengaju . '!')
            ->view('emails.message');
    }
}
