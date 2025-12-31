<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4A5568;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f7fafc;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }
        .section {
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
            border-bottom: 2px solid #4A5568;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
        }
        .info-value {
            color: #2d3748;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #4A5568;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #4A5568;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #718096;
            font-size: 12px;
        }
        .highlight {
            background-color: #48bb78;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Réservation Confirmée</h1>
    </div>

    <div class="content">
        <div class="highlight">
            <strong>Merci {{ $user->firstname }} {{ $user->lastname }} !</strong><br>
            Votre paiement a été confirmé avec succès.
        </div>

        <!-- Informations de la séance -->
        <div class="section">
            <div class="section-title">📽️ Informations de la séance</div>
            <div class="info-row">
                <span class="info-label">Film :</span>
                <span class="info-value">{{ $movie->title ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cinéma :</span>
                <span class="info-value">{{ $cinema->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Salle :</span>
                <span class="info-value">{{ $session->room->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date et heure :</span>
                <span class="info-value">{{ $session->startTime->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        <!-- Détails de la réservation -->
        <div class="section">
            <div class="section-title">🎫 Détails de votre réservation</div>
            <div class="info-row">
                <span class="info-label">Numéro de réservation :</span>
                <span class="info-value">#{{ $booking->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de réservation :</span>
                <span class="info-value">{{ $booking->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nombre de tickets :</span>
                <span class="info-value">{{ $booking->total_tickets }}</span>
            </div>
        </div>

        <!-- Détails des tickets -->
        <div class="section">
            <div class="section-title">📋 Détails des tickets</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Type de ticket</th>
                        <th style="text-align: center;">Quantité</th>
                        <th style="text-align: right;">Prix unitaire</th>
                        <th style="text-align: right;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->price_name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->price_amount, 2, ',', ' ') }} €</td>
                        <td style="text-align: right;">{{ number_format($item->subtotal, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="total">
                Total : {{ number_format($booking->total_amount, 2, ',', ' ') }} €
            </div>
        </div>

        <!-- Informations de paiement -->
        <div class="section">
            <div class="section-title">💳 Informations de paiement</div>
            <div class="info-row">
                <span class="info-label">ID de transaction :</span>
                <span class="info-value">{{ $booking->payment_intent_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut :</span>
                <span class="info-value" style="color: #48bb78; font-weight: bold;">✅ Payé</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date de paiement :</span>
                <span class="info-value">{{ $booking->paid_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

        <!-- Instructions -->
        <div class="section">
            <div class="section-title">ℹ️ Informations importantes</div>
            <p>• Veuillez vous présenter au cinéma au moins 15 minutes avant le début de la séance.</p>
            <p>• Conservez cet email comme preuve de votre réservation.</p>
            <p>• Vous pouvez présenter cet email à l'accueil du cinéma.</p>
            <p>• En cas de problème, contactez-nous avec votre numéro de réservation : <strong>#{{ $booking->id }}</strong></p>
        </div>
    </div>

    <div class="footer">
        <p>Merci d'avoir choisi {{ $cinema->name ?? 'notre cinéma' }} !</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>
