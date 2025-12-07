<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .reset-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .reset-button:hover {
            background-color: #0056b3;
        }
        .footer {
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .url-fallback {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <h2>Bonjour {{ $user->firstname }} {{ $user->lastname }},</h2>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte {{ config('app.name') }}.</p>
            
            <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Réinitialiser mon mot de passe
                </a>
            </div>

            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul>
                    <li>Ce lien est valide pendant <strong>24 heures</strong> seulement</li>
                    <li>Pour votre sécurité, ce lien ne peut être utilisé qu'une seule fois</li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                </ul>
            </div>

            <p>Si le bouton ne fonctionne pas, vous pouvez copier et coller le lien suivant dans votre navigateur :</p>
            
            <div class="url-fallback">
                {{ $resetUrl }}
            </div>

            <p>Si vous avez des questions ou des problèmes, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br>L'équipe {{ config('app.name') }}</p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>