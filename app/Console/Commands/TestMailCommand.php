<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test via Mailjet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::to($email)->send(new TestMail());
            $this->info("Email de test envoyé avec succès à : {$email}");
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de l'email : " . $e->getMessage());
        }
    }
}
