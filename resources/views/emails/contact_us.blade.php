@component('mail::message')
# Nouveau message de contact - Pearl's Event

**Nom:** {{ $name }}  
**Email:** {{ $email }}  
**Téléphone:** {{ $phone }}

**Type d'événement:** {{ $eventType }}

@if($eventDate)
**Date de l'événement:** {{ $eventDate }}
@endif

@if($budget)
**Budget:** {{ $budget }}
@endif

**Message:**

@component('mail::panel')
{{ $message }}
@endcomponent

---

*Ce message a été envoyé depuis le formulaire de contact de Pearl's Event.*

@endcomponent

