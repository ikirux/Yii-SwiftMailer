<?php

class Swift_FileTransport implements Swift_Transport
{
	/**
	 * Not used.
	 */
	public function isStarted()
	{
		return false;
	}

	/**
	 * Not used.
	 */
	public function start()
	{}

	/**
	 * Not used.
	 */
	public function stop()
	{}

	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		Yii::log($message->toString(),CLogger::LEVEL_WARNING,'yii\swiftmailer');
		return (
			count((array) $message->getTo())
			+ count((array) $message->getCc())
			+ count((array) $message->getBcc())
		);
	}

	/**
	 * Not used.
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{}
}