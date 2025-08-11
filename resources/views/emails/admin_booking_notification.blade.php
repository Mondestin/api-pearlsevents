@component('mail::message')
# 🎫 Nouvelle Réservation En Ligne

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; color: white;">
    <h2 style="margin: 0; color: white; font-size: 24px;">Pearl's Event</h2>
    <p style="margin: 10px 0 0 0; opacity: 0.9;">Système de Réservation Automatique</p>
</div>

Bonjour **{{ $adminName }}**,

Une nouvelle réservation en ligne a été créée et nécessite votre attention immédiate.

---

## 📋 Détails de la Réservation

<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff; margin: 15px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #495057; width: 40%;">ID de Réservation :</td>
            <td style="padding: 8px 0; color: #6c757d;"><code style="background: #e9ecef; padding: 2px 6px; border-radius: 4px;">{{ $booking->id }}</code></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #495057;">Référence :</td>
            <td style="padding: 8px 0; color: #6c757d;"><strong style="color: #007bff;">BK-{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #495057;">Date de Réservation :</td>
            <td style="padding: 8px 0; color: #6c757d;">{{ $booking->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
    </table>
</div>

## 🎭 Informations sur l'Événement

<div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 15px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #856404; width: 40%;">Nom de l'Événement :</td>
            <td style="padding: 8px 0; color: #856404;"><strong>{{ $booking->event->name }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #856404;">Date de l'Événement :</td>
            <td style="padding: 8px 0; color: #856404;">{{ $booking->event->date->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #856404;">Lieu :</td>
            <td style="padding: 8px 0; color: #856404;">📍 {{ $booking->event->location }}</td>
        </tr>
    </table>
</div>

## 🎟️ Informations sur le Billet

<div style="background: #d1ecf1; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; margin: 15px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #0c5460; width: 40%;">Type de Billet :</td>
            <td style="padding: 8px 0; color: #0c5460;"><span style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">{{ $booking->ticket->type }}</span></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #0c5460;">Prix par Billet :</td>
            <td style="padding: 8px 0; color: #0c5460;"><strong style="color: #17a2b8;">{{ number_format($booking->ticket->price, 2) }}€</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #0c5460;">Quantité Réservée :</td>
            <td style="padding: 8px 0; color: #0c5460;">{{ $booking->quantity }} billet(s)</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #0c5460;">Montant Total :</td>
            <td style="padding: 8px 0; color: #0c5460;"><strong style="font-size: 18px; color: #dc3545;">{{ number_format($booking->total_price, 2) }}€</strong></td>
        </tr>
    </table>
</div>

## 👤 Informations sur le Client

<div style="background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; margin: 15px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #155724; width: 40%;">Nom :</td>
            <td style="padding: 8px 0; color: #155724;"><strong>{{ $booking->user->name }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #155724;">Email :</td>
            <td style="padding: 8px 0; color: #155724;"><a href="mailto:{{ $booking->user->email }}" style="color: #28a745;">{{ $booking->user->email }}</a></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #155724;">Téléphone :</td>
            <td style="padding: 8px 0; color: #155724;">{{ $booking->user->phone ?? 'Non fourni' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: 600; color: #155724;">Rôle :</td>
            <td style="padding: 8px 0; color: #155724;"><span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">{{ ucfirst($booking->user->role) }}</span></td>
        </tr>
    </table>
</div>

## ⚡ Actions Requises

<div style="background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545; margin: 20px 0;">
    <h4 style="margin: 0 0 15px 0; color: #721c24;">Actions Prioritaires :</h4>
    <ul style="margin: 0; padding-left: 20px; color: #721c24;">
        <li style="margin: 8px 0;">✅ <strong>Confirmer</strong> les détails de la réservation</li>
        <li style="margin: 8px 0;">🔍 <strong>Vérifier</strong> la disponibilité des billets</li>
        <li style="margin: 8px 0;">📞 <strong>Contacter</strong> le client si nécessaire</li>
        <li style="margin: 8px 0;">💳 <strong>Traiter</strong> le paiement si requis</li>
    </ul>
</div>

---

<div style="text-align: center; margin: 30px 0;">
    @component('mail::button', ['url' => config('app.url') . '/admin/bookings/' . $booking->id, 'color' => 'primary'])
    📋 Voir les Détails Complets
    @endcomponent
</div>

---

<div style="background: #e9ecef; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; font-size: 12px; color: #6c757d;">
    <p style="margin: 0;"><strong>🔔 Notification Automatique</strong></p>
    <p style="margin: 5px 0;">Système de réservation Pearl's Event</p>
    <p style="margin: 5px 0;">Si vous avez des questions, contactez l'administrateur du système</p>
</div>

@endcomponent 