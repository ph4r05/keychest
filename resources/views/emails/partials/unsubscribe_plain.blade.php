Weekly status emails are now being sent only if we find a certificate, which needs to be renewed in the next 28 days.
You can use the following link to completely disable weekly status emails: {{ url('unsubscribe/' . $user->weekly_unsubscribe_token) }}

If you change your mind later, you can adjust this and other account options in your KeyChest dashboard: {{ url('home/license') }}
