Notifier
==========
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

A library for handling and processing notifications.   

* [Installation](#installation)
* [Introduction](#introduction)
* [Process Flow](#process-flow)
* [Message](#message)
* [Recipient Collection](#recipient-collection)    
* [Handlers](#handlers)
* [Usage](#usage)

## Installation
To install the package, you simply run:

    composer require emmanix2002/notifier

## Introduction
The notifier works using the the following concepts:   

- **Channels**: channels are named groupings of handlers which receive a _message_ and a list of _recipients_    
- **Handlers**: handlers are instances of `Emmanix2002\Notifier\Handler\HandlerInterface`, which 
are tied to _channels_, and are passed the _message_ and _recipients_ for delivery.     
- **Processors**: processors are _callable_ that are passed the _message_ and _recipients_ for processing, 
before they're passed to the relevant _channels_ or _handlers_. Processors are used to filter or make 
adjustments to the _message_ or _recipients_ before they are passed down to the _channel_. Processors can 
be added to the main `Notifier` class, or to individual _Channels_.   
    - _Processors_ added to the `Notifier` class are called before the message and recipients are passed 
    to the _Channels_; while    
    - _Processors_ added to a `Channel` are passed the message and recipients before they're passed to the 
     _Handlers_    
- **Message**: a message, is an instance of `Emmanix2002\Notifier\Message\MessageInterface` is the 
information that requires sending to _recipients_.
- **Recipients**: by default, the `notify()` methods expect a list of recipients; even if you only need to 
send a message to a single recipient, you still need to provide an `Collection` of one item. This 
collection should be an instance of `Emmanix2002\Notifier\Recipient\RecipientCollection`.    

By default, 2 handlers are provided to get you started:    
- InfobipSmsHandler: for sending SMS messages using the Infobip API
- SendgridEmailHandler: for sending email messages using the Sendgrid API 

## Process Flow
This is the expected flow for using the notifier:

1. Create an instance of `Notifier` (you can use the `Notifier::instance()` to have a shared instance through the application)
2. **(optional)** Add one or more _Processors_ to the `Notifier`
3. Create your `Message` (for instance, you could create an `SmsMessage`)
4. Create your `Recipient` (e.g. for SMS, we need a phone number, we could say `$recipients = new RecipientCollection(['2348123456789'], PhoneRecipient::class);`)
5. Call your `Notifier::notify($message, $recipients)`    

So it flows like:

    Notifier -> (Processors) -> Channels -> (Processors) -> Handlers
    
You see `(Processors)` appear twice, here's why:
- The first is triggered for _processors_ added to the `Notifier`; while
- The second is triggered for _processors_ added to the `Channel`   

## Message
A message is an instance of `Emmanix2002\Notifier\Message\MessageInterface`. A `MessageInterface` implementation 
encapsulates the data that is to be sent out to the recipients.    

A few message types are provided by default:    
- `Emmanix2002\Notifier\Message\EmailMessage`: this is a basic email message implementation, providing this such as: 
`Bcc`, `Cc`, `Subject`, `ReplyTo`, `From` and `Body` fields. Any handler processing emails should be able to get all 
information required for creating the email from an instance of this class.    
- `Emmanix2002\Notifier\Message\SmsMessage`: just like the `EmailMessage` class, this class is the basic implementation 
for an **sms** text message. It has only one field: `message`, which is the `string` containing the message.
- `Emmanix2002\Notifier\Message\InfobipSmsMessage`: this implementation is a more advanced version of the `SmsMessage` 
class, providing special properties (or fields) unique to the Infobip system. It _extends_ `SmsMessage` providing many 
other fields like: `notifyUrl` (for delivery reports), `notifyContentType` (the content type used to represent the 
delivery report), and `scheduleFor` (a `\DateTime` instance representing when the message should be sent - scheduling SMS)
- `Emmanix2002\Notifier\Message\SendgridEmailMessage`: this also is an advanced implementation of the `EmailMessage` 
class. It _extends_ it and also provides a few additional properties unique to Sendgrid like: `templateId`, `sections`, 
and even `category` (for tagging the message(s)).

## Recipient Collection
The `RecipientCollection` represents a list/collection of `destinations` that the notification should be sent to; it 
also tries to understand how to interpret each of these addresses (it does so from the second `__construct()` parameter).    

A `recipient` is an instance of `Emmanix2002\Notifier\Recipient\RecipientInterface`; all recipients are expected to 
`implement` this interface.    

Some default recipient classes are provided with the package:    
- `Emmanix2002\Notifier\Recipient\PhoneRecipient`: this `recipient` is useful for addressing phone numbers; a simple 
array of strings can be passed to the `RecipientCollection` to create this. For example: 
    
        $phones = ['2348123456789', '23481223456789'];
        $collection = new RecipientCollection($phones, PhoneRecipient::class);
        // see the examples/sms-notify.php file for the full example
        
    As you can see with this, the destination address is simply an array of `strings`.
    
- `Emmanix2002\Notifier\Recipient\EmailRecipient`
- `Emmanix2002\Notifier\Recipient\SendgridEmailRecipient`
    
The _first parameter_ of the `RecipientCollection` constructor supports 3 forms:    

1. An array of strings e.g. `$collection = new RecipientCollection(['email@domain.com', ...], EmailRecipient::class);`    
when looping through (or when accessing an index - `$collection[0]` - it returns an instance of the _second argument_ 
which is a class that implements `RecipientInterface`).      
It does so by passing each `string` element of the array as the first argument of the class recipient constructor.    
 
2. An array of `RecipientInterface` instances. You can see this from the `examples/email-notify.php` example file.    
Even though it's an array of `RecipientInterface` instances, you still need to pass the _second parameter_.    
Like before, when looping or accessing an offset of the collection, you'll get a `RecipientInterface` instance.    

3. An array with a form representing the constructor form of the desired `RecipientInterface` implementation. Take a 
look at the `examples/email-notify-using-address-array.php` example.    
 This form is the _most flexible_ because it allows you to keep your code simple and clean, without having to create 
 too many objects until you really need to.    
  It splits the array and passes the indexes as the arguments to the `__construct()` method of the instance. For instance, 
  it passes **index 0** as the **first parameter**, **index 1** as the **second parameter**, and so forth.    
  
Each of the files inside the `examples` directory highlight these 2 forms. So, feel free to create your own 
`RecipientInterface` implementations.    

## Handlers
**Handlers** are instances of `HandlerInterface` (see [Introduction](#introduction)). When you create a channel, you 
either pass the handlers in the constructor, or you add them one at a time on the created `Channel`.     

Handlers are the actual mechanism that send out the notifications; without handlers, a channel basically does nothing.    
Handlers added to a channel are stored in a stack (i.e. Last-In, First-Out - **LIFO**); meaning, the last handler added 
to a channel gets executed first.     

After a handler is called, and it completes execution, it returns a `boolean` value representing whether or not the 
request (i.e. `Message` and `RecipientCollection`) should be passed to the next handler for processing:    

- `true`: the request should be forwarded to the next handler
- `false`: the request should not be forwarded anymore (probably because the handler has completed successfully)

Handlers **must** define a `propagate(): bool` method on themselves to describe the propagate preference for the handler.    
By default, all handlers that `extend` the `AbstractHandler` class **return** `false`.     

## Usage
See the `examples` directory for more.

[ico-version]: https://img.shields.io/packagist/v/emmanix2002/notifier.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/emmanix2002/notifier/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/emmanix2002/notifier.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/emmanix2002/notifier
[link-travis]: https://travis-ci.org/emmanix2002/notifier
[link-downloads]: https://packagist.org/packages/emmanix2002/notifier
[link-author]: https://github.com/emmanix2002