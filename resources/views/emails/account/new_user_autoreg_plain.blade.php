Hi,

Please log in to your dashboard and add some of your servers or registered domains to enjoy
the power and simplicity of KeyChest.

You will have to visit {{ url('/home') }} and use the recipient
address of this email.

@component('emails.partials.unsubscribe_plain', ['user' => $md->user])
@endcomponent

Kind regards
  {{ config('app.name') }} & Enigma Bridge

@component('emails.partials.news_footer_plain', ['news' => $news])
@endcomponent
