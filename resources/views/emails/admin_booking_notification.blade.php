@component('mail::message')
# Nouvelle Réservation En Ligne - Pearl's Event

Bonjour {{ $adminName }},

Une nouvelle réservation en ligne a été créée pour un événement.

## Détails de la Réservation

**ID de Réservation :** {{ $booking->id }}  
**Référence de Réservation :** BK-{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}  
**Date de Réservation :** {{ $booking->created_at->format('d/m/Y H:i') }}

## Informations sur l'Événement

**Nom de l'Événement :** {{ $booking->event->name }}  
**Date de l'Événement :** {{ $booking->event->date->format('d/m/Y H:i') }}  
**Lieu :** {{ $booking->event->location }}

## Informations sur le Billet

**Type de Billet :** {{ $booking->ticket->type }}  
**Prix par Billet :** {{ number_format($booking->ticket->price, 2) }}€  
**Quantité Réservée :** {{ $booking->quantity }}  
**Montant Total :** {{ number_format($booking->total_price, 2) }}€

## Informations sur le Client

**Nom :** {{ $booking->user->name }}  
**Email :** {{ $booking->user->email }}  
**Téléphone :** {{ $booking->user->phone ?? 'Non fourni' }}  
**Rôle :** {{ ucfirst($booking->user->role) }}

## Actions Requises

Veuillez examiner cette réservation et prendre les mesures nécessaires :
- Confirmer les détails de la réservation
- Vérifier la disponibilité des billets
- Contacter le client si nécessaire
- Traiter le paiement si requis

@component('mail::button', ['url' => config('app.url') . '/admin/bookings/' . $booking->id])
Voir les Détails de la Réservation
@endcomponent

---

**Ceci est une notification automatique du système de réservation Pearl's Event.**

*Si vous avez des questions, veuillez contacter l'administrateur du système.*

@endcomponent 