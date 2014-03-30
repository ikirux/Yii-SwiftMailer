Yii-SwiftMailer
===============

Wrapper of Swiftmailer for Yii framework version 1.x

Current swiftmailer version supported  5.1.0

It supports this features:

1. smtp, sendmail or mail transport
2. ssl or tls security
3. setTo, CC, BCC
4. Attachment of dinamic (generate at runtime) and static files
5. Embed files (statics and dinamics)
6. Swiftmailer's plugin (AntiFlood, Throtter, Logger)

You can review the official documentation at http://swiftmailer.org/docs/introduction.html

Installation
============

Using Composer:

Simply add a dependency on "ikirux/yii-swift-mailer" to your project's composer.json file if you use Composer to manage the dependencies of your project. Here is a minimal example of a composer.json

```
{
    "require": {
        "ikirux/yii-swift-mailer": "dev-master"
    }
}
```
for more information visits https://getcomposer.org/

Using Git:

Just clone the project inside your extension directory

```
git clone git@github.com:ikirux/Yii-SwiftMailer.git`

```

Configuration:

Set up yii component via config:

```
'mailer' => [
	'class' => 'path.to.swiftMailer.SwiftMailer',

	// Using SMTP:
	'mailer' => 'smtp',
	// 'ssl' for "SSL/TLS" or 'tls' for 'STARTTLS'
	'security' => 'ssl', 
	'host' => 'localhost',
	'from' => 'admin@localhost',
	'username' => 'smptusername',
	'password' => '123456',

	// Activate the AntiFlood plugin
	// more information http://swiftmailer.org/docs/plugins.html#using-the-logger-plugin
	//'activateLoggerPlugin' => true,

	// Activate the AntiFlood plugin
	// more information http://swiftmailer.org/docs/plugins.html#antiflood-plugin
	//'activateAntiFloodPlugin' => true,		    
	//'setFloodPluginParams' => ['threshold' => 100, 'sleep' => 30],

	// Activate the Throtter plugin
	// more information http://swiftmailer.org/docs/plugins.html#throttler-plugin
	// Modes support 1 => SwiftMailer::BYTES_PER_MINUTE, 
	//               2 => SwiftMailer::MESSAGES_PER_SECOND 
	//               3 => SwiftMailer::MESSAGES_PER_MINUTE
	//'activateThrotterPlugin' => true,		    
	//'setThrotterPluginParams' => ['rate' => 10, 'mode' => 3],
],	
```

Usage
=====

Creating a Message

```
Yii::app()->mailer->setSubject('A great subject')
	->addAddress('mail@domain.com')
	->setBody('Nice HTML message')
	->setAltBody('Message plain text alternative')
	->send();
```

Creating a Message With Several Recipients

```
Yii::app()->mailer->setSubject('A great subject')
	->addAddress(['mail@domain.com', 'mail2@domain.com'])
	->addCcAddress('mail3@domain.com')
	->addBccAddress(['mail4@domain.com', 'mail5@domain.com'])
	->setBody('Nice HTML message')
	->setAltBody('Message plain text alternative')
	->send();
```

Attaching Files

```
Yii::app()->mailer->setSubject('A great subject')
	->addAddress('mail@domain.com')
	->setBody('Nice HTML message')
	->setAltBody('Message plain text alternative')
	->addAttachment('/path/to/file.pdf', 'application/pdf', 'Nickname File.pdf')
	->addAttachment('/path/to/file.jpg')
	->send();
```

Attaching Dinamic Files

```
// Create your file contents in the normal way, but don't write them to disk
$data = create_my_pdf_data();

Yii::app()->mailer->setSubject('A great subject')
	->addAddress('mail@domain.com')
	->setBody('Nice HTML message')
	->setAltBody('Message plain text alternative')
	->addDinamicAttachment($data, 'application/pdf', 'FileName.pdf')
	->send();
```

Embedding Existing Files

```
Yii::app()->mailer->setSubject('A great subject')
	->addAddress('mail@domain.com')
	->setBody(
	'<html>' .
	' <head></head>' .
	' <body>' .
	'  Here is an image {{image}}' .
	'  Rest of message' .
	' </body>' .
	'</html>')
	->setAltBody('Message plain text alternative')
	->embedFile('{{image}}', '/path/to/file.jpg')
	->send();
```

Embedding Dinamic Files

```
// Create your file contents in the normal way, but don't write them to disk
$img_data = create_my_image_data();

Yii::app()->mailer->setSubject('A great subject')
	->addAddress('mail@domain.com')
	->setBody(
	'<html>' .
	' <head></head>' .
	' <body>' .
	'  Here is an image {{image}}' .
	'  Rest of message' .
	' </body>' .
	'</html>')
	->setAltBody('Message plain text alternative')
	->embedDinamicFile('{{image}}', $img_data, 'image/jpeg', 'image.jpg')
	->send();
```

Your feedback is very welcome!

Have Fun!