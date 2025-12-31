@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
<svg style="width: 64px; height: 64px; display: inline-block;" fill="none" stroke="#4F46E5" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
</svg>
</div>

# Welcome to {{ config('app.name') }}!

Hello **{{ $user->name }}**,

You have been invited to join **{{ config('app.name') }}**, a powerful snippet management platform where you can store, organize, and share your code snippets efficiently.

## What's {{ config('app.name') }}?

{{ config('app.name') }} helps developers:
- 📝 Store and organize code snippets
- 🗂️ Create folders and categories
- 👥 Collaborate with teams
- 🔗 Share snippets easily

## Complete Your Account Setup

To get started, click the button below to set your password and confirm your account details.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Complete Account Setup
@endcomponent

This invitation link will expire in **7 days**.

If you did not expect this invitation or believe you received this email in error, no further action is required. Simply disregard this message.

## Need Help?

If you have any questions, feel free to reach out to your team administrator.

Thanks,<br>
The {{ config('app.name') }} Team

---

<small style="color: #6B7280;">
If you're having trouble clicking the "Complete Account Setup" button, copy and paste the URL below into your web browser:
<br>
{{ $url }}
</small>
@endcomponent
