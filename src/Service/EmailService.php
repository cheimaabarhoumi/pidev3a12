<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $senderEmail = 'habibathimenn@gmail.com'
    ) {}

    public function sendWelcomeEmail(string $recipient, string $prenom): bool
    {
        try {
            $email = (new Email())
                ->from($this->senderEmail)
                ->to($recipient)
                ->subject('Bienvenue sur notre plateforme!')
                ->text("Bonjour $prenom,\n\nMerci pour votre inscription!")
                ->html("
                    <h1>Bonjour $prenom!</h1>
                    <p>Votre compte a été créé avec succès.</p>
                    <p>Cordialement,<br>L'équipe</p>
                ");

            $this->mailer->send($email);
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}