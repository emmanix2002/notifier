Notifier
==========
A library for handling and processing notifications.   

* [Installation](#installation)
* [Introduction](#introduction)
* [Process Flow](#process-flow)

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

## Usage
See the `examples` directory for more.