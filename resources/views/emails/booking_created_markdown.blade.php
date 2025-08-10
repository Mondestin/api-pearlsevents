@component('mail::message')
<div style="text-align: center; margin-bottom: 16px;">
    <img src="{{ config('app.brand_logo', 'https://via.placeholder.com/180x50?text=Pearl+Events') }}" alt="Pearl Events" style="max-width: 200px;">
</div>

# Bonjour {{ $user->name }},

Merci d'avoir choisi **Pearl Events**. Votre réservation a été confirmée.

@component('mail::panel')
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-size:14px;">
  <tr>
    <td style="padding:4px 0;"><strong>Événement</strong></td>
    <td style="padding:4px 0;" align="right">{{ $event->name }}</td>
  </tr>
  <tr>
    <td style="padding:4px 0;"><strong>Date</strong></td>
    <td style="padding:4px 0;" align="right">{{ optional($event->date)->timezone(config('app.timezone'))->translatedFormat('d F Y H:i') }}</td>
  </tr>
  <tr>
    <td style="padding:4px 0;"><strong>Lieu</strong></td>
    <td style="padding:4px 0;" align="right">{{ $event->location }}</td>
  </tr>
  <tr>
    <td style="padding:4px 0;"><strong>Billet</strong></td>
    <td style="padding:4px 0;" align="right">{{ $ticket->type }} — {{ number_format((float) $ticket->price, 2, ',', ' ') }} €</td>
  </tr>
  <tr>
    <td style="padding:4px 0;"><strong>Quantité</strong></td>
    <td style="padding:4px 0;" align="right">{{ $booking->quantity }}</td>
  </tr>
  <tr>
    <td style="padding:4px 0;"><strong>Total</strong></td>
    <td style="padding:4px 0;" align="right">{{ number_format((float) ($booking->quantity * $ticket->price), 2, ',', ' ') }} €</td>
  </tr>
</table>
@endcomponent

@if(!empty($qrUrl))
@component('mail::panel')
<div style="text-align:center">
  <div style="font-weight:600;margin-bottom:8px">Présentez ce QR Code à l'entrée</div>
  <a href="{{ $bookingUrl }}" target="_blank" rel="noopener">
    <img src="{{ $qrUrl }}" alt="QR Code Réservation" width="240" height="240" style="display:block;margin:0 auto;border:8px solid #f1f5f9;border-radius:12px" />
  </a>
  <div style="font-size:12px;color:#64748b;margin-top:8px">Référence réservation: {{ $booking->id }}</div>
</div>
@endcomponent
@endif

@component('mail::button', ['url' => $bookingUrl])
Voir ma réservation en ligne
@endcomponent

Pour toute question, répondez directement à cet e-mail. Nous sommes là pour vous aider.

Merci,
**Pearl's Events**
@endcomponent

