@component('mail::message')
# 🎉 Bienvenue sur Pearl's Event !

<div style="text-align: center; margin-bottom: 16px;">
    <img src="{{ config('app.brand_logo', 'https://pearlsevents.vercel.app/logo.png') }}" alt="Pearl Events" style="max-width: 200px;">
</div>

Bonjour **{{ $user->name }}**,

{{ $welcomeMessage }}

Nous sommes ravis de vous accueillir dans notre communauté d'événements exceptionnels !

---

## 🎯 Votre Compte a Été Créé avec Succès

<div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 5px solid #007bff; margin: 20px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; font-weight: 600; color: #495057; width: 35%;">Nom d'utilisateur :</td>
            <td style="padding: 10px 0; color: #6c757d;"><strong>{{ $user->email }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; font-weight: 600; color: #495057;">Mot de passe :</td>
            <td style="padding: 10px 0; color: #6c757d;"><code style="background: #e9ecef; padding: 4px 8px; border-radius: 6px;">{{ $user->password }}</code></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; font-weight: 600; color: #495057;">Date d'inscription :</td>
            <td style="padding: 10px 0; color: #6c757d;">{{ $user->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
    </table>
</div>

## 🚀 Que Pouvez-Vous Faire Maintenant ?

<div style="background: #d1ecf1; padding: 20px; border-radius: 10px; border-left: 5px solid #17a2b8; margin: 20px 0;">
    <h4 style="margin: 0 0 15px 0; color: #0c5460;">Fonctionnalités Disponibles :</h4>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🎫</div>
            <strong style="color: #0c5460;">Réserver des Billets</strong>
            <p style="margin: 8px 0 0 0; font-size: 12px; color: #6c757d;">Accédez à nos événements exclusifs</p>
        </div>
        <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">📅</div>
            <strong style="color: #0c5460;">Gérer vos Réservations</strong>
            <p style="margin: 8px 0 0 0; font-size: 12px; color: #6c757d;">Suivez vos événements à venir</p>
        </div>
        <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🔔</div>
            <strong style="color: #0c5460;">Notifications</strong>
            <p style="margin: 8px 0 0 0; font-size: 12px; color: #6c757d;">Restez informé des nouveautés</p>
        </div>
        <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">👤</div>
            <strong style="color: #0c5460;">Profil Personnalisé</strong>
            <p style="margin: 8px 0 0 0; font-size: 12px; color: #6c757d;">Gérez vos informations</p>
        </div>
    </div>
</div>

## 🎭 Découvrez Nos Événements

<div style="background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107; margin: 20px 0;">
    <h4 style="margin: 0 0 15px 0; color: #856404;">Prochains Événements :</h4>
    <p style="margin: 0; color: #856404;">Explorez notre calendrier d'événements et réservez vos places dès maintenant sur notre site web!</p>
</div>

---


## 📞 Besoin d'Aide ?

<div style="background: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin: 20px 0;">
    <h4 style="margin: 0 0 15px 0; color: #155724;">Support et Contact :</h4>
    <ul style="margin: 0; padding-left: 20px; color: #155724;">
        <li style="margin: 8px 0;">📧 <strong>Email :</strong> contact@pearlsevents.fr</li>
        <li style="margin: 8px 0;">📱 <strong>Téléphone :</strong> +33 7 49 33 24 93</li>
        <li style="margin: 8px 0;">🌐 <strong>Site Web :</strong> <a href="https://www.pearlsevents.fr" style="color: #28a745;">www.pearlsevents.fr</a></li>
    </ul>
</div>

---

<div style="background: #e9ecef; padding: 20px; border-radius: 10px; text-align: center; margin: 25px 0; font-size: 12px; color: #6c757d;">
    <p style="margin: 0;"><strong>🎉 Merci de nous faire confiance !</strong></p>
    <p style="margin: 5px 0;">L'équipe Pearl's Events</p>
    <p style="margin: 5px 0;">Créer des moments inoubliables ensemble</p>
</div>

@endcomponent 