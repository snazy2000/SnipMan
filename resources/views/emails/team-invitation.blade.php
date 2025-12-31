@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
<svg style="width: 64px; height: 64px; display: inline-block;" fill="none" stroke="#4F46E5" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
</svg>
</div>

@if($isNewUser)
# Welcome to {{ config('app.name') }}!

Hello!

You have been invited to join the team **{{ $teamName }}** on **{{ config('app.name') }}**, a powerful snippet management platform where you can store, organize, and share your code snippets efficiently.

## Your Team Invitation

**Team:** {{ $teamName }}
**Your Role:** {{ ucfirst($role) }}

As this is your first time using {{ config('app.name') }}, you'll need to set up your account and create a password.

## What's {{ config('app.name') }}?

{{ config('app.name') }} helps developers:
- 📝 Store and organize code snippets
- 🗂️ Create folders and categories
- 👥 Collaborate with teams
- 🔗 Share snippets easily

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Accept Invitation & Set Up Account
@endcomponent

@else
# Team Invitation

Hello **{{ $userName }}**!

You have been invited to join the team **{{ $teamName }}** on {{ config('app.name') }}.

## Invitation Details

**Team:** {{ $teamName }}
**Your Role:** {{ ucfirst($role) }}

@component('mail::panel')
### Role Permissions

@if($role === 'owner')
**Owner** - Full access including team settings and member management
@elseif($role === 'editor')
**Editor** - Can create, edit, and delete team content
@else
**Viewer** - Can view team snippets and folders
@endif
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
Accept Team Invitation
@endcomponent

@endif

This invitation link will expire in **7 days**.

If you did not expect this invitation or believe you received this email in error, you can safely ignore this message.

Thanks,<br>
The {{ config('app.name') }} Team

---

<small style="color: #6B7280;">
If you're having trouble clicking the button, copy and paste the URL below into your web browser:
<br>
{{ $url }}
</small>
@endcomponent
