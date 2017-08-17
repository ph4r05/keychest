# Further extensions

## DB templating for e-mails

For DB based templates we can use [Twig]. 
It is a templating engine similar to Blade. The differences are:

- Twig is secure and sandboxed
- Twig allows you to specify which functions are available to user
- Twig does not allow user to do everything
- Blade is very permissive, no sandbox
- Blade compiles template to PHP, then calls `eval()` on it.

Blade cannot be used for DB based templates because in case of an error / attack simple DB write 
is immediately Remote Code Execution


[Twig]: https://github.com/rcrowe/TwigBridge

## Eventing

For faster eventing we can employ Laravel Echo and SocketIO web-socket server.
Event will then faster propagate to the end-client via web-socket.

