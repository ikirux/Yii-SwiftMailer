<?php

/**
 * Swift Mailer wrapper class.
 *
 * @author Carlos Pinto 'ikirux' ikirux@gmail.com
 */
class SwiftMailer extends CApplicationComponent
{
	/** Constants using by Throttler Plugin */

    /**
     * Flag for throttling in bytes per minute 
     */
    const BYTES_PER_MINUTE = 0x01;

    /**
     * Flag for throttling in emails per second (Amazon SES)
     */
    const MESSAGES_PER_SECOND = 0x11;

    /**
     * Flag for throttling in emails per minute
     */
    const MESSAGES_PER_MINUTE = 0x10;

	const MAILER_FILE = 'file';
	const MAILER_MAIL = 'mail';
	const MAILER_SMTP = 'smtp';
	const MAILER_SENDMAIL = 'sendmail';
	/**
	 * @var string smtp, sendmail or mail
	 */
	public $mailer = self::MAILER_SENDMAIL;

	/**
	 * @var string SMTP outgoing mail server host
	 */
	public $host;

	/**
	 * @var int Outgoing SMTP server port
	 */
	public $port = 25;

	/**
	 * @var string SMTP Relay account username
	 */
	public $username;

	/**
	 * @var string SMTP Relay account password
	 */
	public $password;

	/**
	 * @var string SMTP security (ssl or tls)
	 */
	public $security;

	/**
	 * @var mixed Email address messages are going to be sent "from"
	 */
	public $from;

	/**
	 * @var string The character set of the message
	 */
	public $charset;	

	/**
	 * @var string sendmailCommand (default '/usr/bin/sendmail -t')
	 * @example '/usr/sbin/exim -bs'
	 * @example '/usr/bin/sendmail -t'
	 */
	public $sendmailCommand = '/usr/bin/sendmail -t';

	/**
	 * @var string HTML Message Body
	 */
	protected $_body = null;

	/**
	 * @var string Alternative message body (plain text)
	 */	
	protected $_altBody = null;

	/**
	 * @var string Message Subject
	 */
	protected $_subject = null;

	/**
	 * @var array Set the To addresses with an associative array
	 */
	protected $_addresses = [];

	/**
	 * @var array Specifies the addresses of recipients who will be copied in on the message
	 */
	protected $_ccAddresses = [];

	/**
	 * @var array Specifies the addresses of recipients who the message will be blind-copied to
	 */
	protected $_bccAddresses = [];

	/**
	 * @var array Attachments are downloadable parts of a message
	 */	
	protected $_attachments = [];

	/**
	 * @var array Files that are generated at runtime and that are parts of a message
	 */	
	protected $_dinamicAttachments = [];

	/**
	 * @var array Files that be embedded in a message
	 */	
	protected $_embedFiles = [];

	/**
	 * @var array Files that are generated at runtime and that be embedded in a message
	 */	
	protected $_embedDinamicFiles = [];

	/**
	 * @var array Collection of recipients that were rejected
	 */	
	protected $_failures = [];

	/**
	 * @var bool Enable AntiFlood plugin
	 */
	public $activateAntiFloodPlugin = false;

	/**
	 * @var array AntiFlood plugin parameters
	 */	
	public $setFloodPluginParams = ['threshold' => 100, 'sleep' => 30];

	/**
	 * @var bool Enable Throtter plugin
	 */
	public $activateThrotterPlugin = false;	

	/**
	 * @var array Throtter plugin parameters
	 */	
	public $setThrotterPluginParams = ['rate' => 100, 'mode' => self::BYTES_PER_MINUTE];

	/**
	 * @var bool Enable Logger plugin
	 */
	public $activateLoggerPlugin = false;		

	/**
	 * @var string Enable Engine Plugin
	 */
	public $engine = '';

	/**
	 * @var string theme used to send emails
	 */
	public $theme = '';

	/**
	 * @var array vars used in a mail theme
	 */	
	protected $_vars = [];	

	public function init()
	{
		if (!class_exists('Swift', false)) {
			$this->registerAutoloader();
		}	
	}

	protected function registerAutoloader()
	{
		require_once Yii::getPathOfAlias('application.vendor.swiftmailer.swiftmailer.lib') . DIRECTORY_SEPARATOR . 'swift_required.php';
		Swift::registerAutoLoad();
        // Register SwiftMailer's autoloader before Yii for correct class loading.
        $autoLoad = array('Swift', 'autoload');
        spl_autoload_unregister($autoLoad);
        Yii::registerAutoloader($autoLoad);
    }	

	public function setSubject($subject)
	{
		$this->_subject = $subject;
		return $this;
	}

	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	public function setCharset($charset)
	{
		$this->charset = $charset;
		return $this;
	}

	public function setTo($address)
	{
		$this->_addresses = $address;
		return $this;
	}
	
	public function addAddress($address)
	{
		if (is_array($address)) {
			$this->_addresses = array_unique(array_merge($this->_addresses, $address));
		} else {
			if (!in_array($address, $this->_addresses)) {
				$this->_addresses[] = $address;
			}
		}

		return $this;
	}

	public function addCcAddress($ccAddress)
	{
		if (is_array($ccAddress)) {
			$this->_ccAddresses = array_unique(array_merge($this->_ccAddresses, $ccAddress));
		} else {
			if (!in_array($ccAddress, $this->_ccAddresses)) {
				$this->_ccAddresses[] = $ccAddress;
			}
		}

		return $this;
	}

	public function addBccAddress($BccAddress)
	{
		if (is_array($BccAddress)) {
			$this->_bccAddresses = array_unique(array_merge($this->_bccAddresses, $BccAddress));
		} else {
			if (!in_array($BccAddress, $this->_bccAddresses)) {
				$this->_bccAddresses[] = $BccAddress;
			}
		}

		return $this;
	}

	public function setVar($var, $value)
	{
		$this->_vars[] = ['var' => $var, 'value' => $value];
		return $this;
	}

	public function setBody($body)
	{
		$this->_body= $body;
		return $this;
	}	

	public function setAltBody($altBody)
	{
		$this->_altBody= $altBody;
		return $this;
	}

	public function getFailures() {
		return $this->_failures;
	}
		
	public function addAttachment($file, $mimeType = '', $fileName = '')
	{
		$attachment = [
			'file' => $file,
			'mimeType' => $mimeType,
			'fileName' => $fileName,
		];

		if (!in_array($attachment, $this->_attachments)) {
			$this->_attachments[] = $attachment;
		}		

		return $this;
	}

	public function addDinamicAttachment($data, $mimeType, $fileName)
	{
		$attachment = [
			'data' => $data,
			'fileName' => $fileName,			
			'mimeType' => $mimeType,
		];

		if (!in_array($attachment, $this->_dinamicAttachments)) {
			$this->_dinamicAttachments[] = $attachment;
		}		

		return $this;
	}

	public function embedFile($wildcard, $file)
	{
		$embedFile = [
			'wildcard' => $wildcard,
			'file' => $file,
		];

		if (!in_array($embedFile, $this->_embedFiles)) {
			$this->_embedFiles[] = $embedFile;
		}		

		return $this;
	}

	public function embedDinamicFile($wildcard, $data, $mimeType, $fileName)
	{
		$embedFile = [
			'wildcard' => $wildcard,		
			'data' => $data,
			'fileName' => $fileName,			
			'mimeType' => $mimeType,
		];

		if (!in_array($embedFile, $this->_embedDinamicFiles)) {
			$this->_embedDinamicFiles[] = $embedFile;
		}		

		return $this;
	}

	public function send()
	{
		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($this->loadTransport());

		if ($this->activateAntiFloodPlugin) {
			$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->setFloodPluginParams['threshold'], $this->setFloodPluginParams['sleep']));
		}

		if ($this->activateThrotterPlugin) {
			if ($this->setThrotterPluginParams['mode'] == 3) {
				$mode = self::MESSAGES_PER_MINUTE;
			} elseif ($this->setThrotterPluginParams['mode'] == 2) {
				$mode = self::MESSAGES_PER_SECOND;
			} else {
				$mode = self::BYTES_PER_MINUTE;
			}
			
			$mailer->registerPlugin(new Swift_Plugins_ThrottlerPlugin($this->setThrotterPluginParams['rate'], $mode));
		}

		if ($this->activateLoggerPlugin) {
			$logger = new Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
		}

		// Create a message with basic settings
		$message = (new Swift_Message($this->_subject))
			->setFrom($this->from)
			->setTo($this->_addresses);

		// Add alternative plain text body if exists
		if (!is_null($this->_altBody)) {
			$message->addPart($this->_altBody, 'text/plain');		
		}
		
		// Set a custom charset if exists
		if (!is_null($this->charset)) {
			$message->setCharset($this->charset);		
		}

		// Set CC recipents if exists
		if (!empty($this->_ccAddresses)) {
			$message->setCc($this->_ccAddresses);		
		}			

		// Set CC recipents if exists
		if (!empty($this->_bccAddresses)) {
			$message->setBcc($this->_bccAddresses);		
		}

		// Attach files from disk or url
		if (!empty($this->_attachments)) {
			foreach ($this->_attachments as $attachment) {
				if (!empty($attachment['mimeType'])) {
					$objAttachment = Swift_Attachment::fromPath($attachment['file'], $attachment['mimeType']);	
				} else {
					$objAttachment = Swift_Attachment::fromPath($attachment['file']);
				} 

				if (!empty($attachment['fileName'])) {
					$message->attach($objAttachment->setFilename($attachment['fileName']));
				} elseif ($objAttachment) {
					$message->attach($objAttachment);
				}				
			}
		}		

		// Attach files that are generated at runtime
		if (!empty($this->_dinamicAttachments)) {
			foreach ($this->_dinamicAttachments as $attachment) {
				$message->attach(new Swift_Attachment($attachment['data'], $attachment['fileName'], $attachment['mimeType']));
			}
		}	

		// Embed files from disk or url
		if (!empty($this->_embedFiles)) {
			foreach ($this->_embedFiles as $embedFile) {
				$this->_body = str_replace(
					$embedFile['wildcard'], 
					'<img src="' . $message->embed(Swift_Image::fromPath($embedFile['file'])) . '" alt="Image" />', 
					$this->_body
				);
			}
		}		

		// Embed files that are generated at runtime
		if (!empty($this->_embedDinamicFiles)) {
			foreach ($this->_embedDinamicFiles as $embedFile) {
				$this->_body = str_replace(
					$embedFile['wildcard'], 
					'<img src="' . $message->embed(new Swift_Image($embedFile['data'], $embedFile['fileName'], $embedFile['mimeType'])) . '" alt="Image" />',
					$this->_body
				);
			}
		}

		// we use a engine template
		if (!empty($this->engine)) {
			// we try get the mail theme, if not we use setBody
			if ($themeContent = @file_get_contents(YiiBase::getPathOfAlias('webroot.themes.mail.' . $this->engine . '.' . $this->theme) . DIRECTORY_SEPARATOR . 'theme.html')) {
				// We apply the vars
				foreach ($this->_vars as $var) {
					$themeContent = str_replace('{{' . $var['var'] . '}}', $var['value'], $themeContent);
				}

				$message->setBody($themeContent, 'text/html');
			} else {
				$message->setBody($this->_body, 'text/html');
			}
		} else {
			$message->setBody($this->_body, 'text/html');	
		}
		
		$result = $mailer->send($message, $this->_failures);

		if ($this->activateLoggerPlugin) {
			Yii::log($logger->dump(), 'info', 'appMailer');
		}	

		return $result;
	}

	protected function loadTransport()
	{
		switch ($this->mailer) {
			case self::MAILER_FILE:
				$transport = new Swift_FileTransport();
				break;
			case self::MAILER_MAIL:
				$transport = new Swift_MailTransport();
				break;
			case self::MAILER_SMTP:
				$transport = new Swift_SmtpTransport($this->host, $this->port, $this->security);

				if ($this->username) {
					$transport->setUsername($this->username);
				}

				if ($this->password) {
					$transport->setPassword($this->password);
				}
				break;
			case self::MAILER_SENDMAIL:
			default:
				$transport = new Swift_SendmailTransport($this->sendmailCommand);
				break;
		}

		return $transport;
	}	
}