PHP Api Example
===============

This example shows how to use the Zyncro REST API from PHP.

We use [oauth-php](http://code.google.com/p/oauth-php/ "oauth-php") as the OAuth client library.

`ZyncroApiSample.php` shows how to get a validated Access Token and invoke two API services. One to get the microblogging and another to post a new message.

In a real world app you can store the Access Token and reuse it.