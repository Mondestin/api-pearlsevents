@component('mail::message')
# Nouveau message de contact

**Nom:** {{ $name }}  
**Email:** {{ $email }}

**Sujet:** {{ $subjectLine }}

@component('mail::panel')
{{ $messageBody }}
@endcomponent

@endcomponent

