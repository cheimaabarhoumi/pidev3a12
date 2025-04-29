<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test:email',
    description: 'Tests the email sending functionality',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private string $senderEmail = 'habibathimenn@gmail.com'
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command tests the email sending configuration')
            ->setDescription('Sends a test email to verify SMTP configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Email Testing Command');
        
        $recipient = $io->ask('Enter recipient email', $this->senderEmail);

        try {
            $email = (new Email())
                ->from($this->senderEmail)
                ->to($recipient)
                ->subject('Test Email from Symfony')
                ->text('This is a test email from your Symfony application')
                ->html('<p>This is a <strong>test email</strong> from your Symfony application</p>');

            $this->mailer->send($email);
            
            $io->success(sprintf('Test email successfully sent to %s', $recipient));
            $io->note('Check your spam folder if you don\'t see it in your inbox');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error([
                'Failed to send test email',
                'Error: ' . $e->getMessage(),
                'Make sure your MAILER_DSN in .env is properly configured'
            ]);
            
            $io->section('Troubleshooting Tips:');
            $io->listing([
                'Verify your SMTP credentials in .env',
                'Check if your email service requires app passwords',
                'Test with MailHog locally first (MAILER_DSN=smtp://localhost:1025)',
                'Ensure ports are not blocked by firewall'
            ]);
            
            return Command::FAILURE;
        }
    }
}