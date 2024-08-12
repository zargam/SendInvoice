<?php
declare (strict_types=1);
namespace Create\InvoiceAttachement\Mail\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Laminas\Mime\Mime;
use Laminas\Mime\Message;
use Laminas\Mime\PartFactory;

/**
* Class TransportBuilder
* @package Tigren\SendPdf\Mail\Template
*/
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
   /**
	* @var Message
	*/
   protected $messageMime;

   /**
	* @var
	*/
   protected $message;

   /**
	* @var array
	*/
   protected $attachments = [];

   /**
	* @var PartFactory|mixed
	*/
   protected $partFactory;

   /**
	* @param Message $messageMime
	* @param PartFactory $partFactory
	* @param FactoryInterface $templateFactory
	* @param MessageInterface $message
	* @param SenderResolverInterface $senderResolver
	* @param ObjectManagerInterface $objectManager
	* @param TransportInterfaceFactory $mailTransportFactory
	* @param MessageInterfaceFactory|null $messageFactory
	* @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
	* @param MimeMessageInterfaceFactory|null $mimeMessageInterfaceFactory
	* @param MimePartInterfaceFactory|null $mimePartInterfaceFactory
	* @param AddressConverter|null $addressConverter
	*/
   public function __construct(
   	Message $messageMime,
   	PartFactory $partFactory,
   	FactoryInterface $templateFactory,
   	MessageInterface $message,
   	SenderResolverInterface $senderResolver,
   	ObjectManagerInterface $objectManager,
   	TransportInterfaceFactory $mailTransportFactory,
   	MessageInterfaceFactory $messageFactory = null,
   	EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
   	MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
   	MimePartInterfaceFactory $mimePartInterfaceFactory = null,
   	AddressConverter $addressConverter = null
   ) {
   	$this->templateFactory = $templateFactory;
   	$this->partFactory = $partFactory;
   	$this->messageMime = $messageMime;
   	parent::__construct(
       	$templateFactory,
       	$message,
       	$senderResolver,
       	$objectManager,
       	$mailTransportFactory,
       	$messageFactory,
       	$emailMessageInterfaceFactory,
       	$mimeMessageInterfaceFactory,
       	$mimePartInterfaceFactory,
       	$addressConverter
   	);
   }

   /**
	* @return $this|TransportBuilder
	* @throws LocalizedException
	*/
 protected function prepareMessage()
   {
   	$result = parent::prepareMessage();
   	if (!empty($this->attachments)) {
       	foreach ($this->attachments as $attachment) {
           	$body = $this->message->getBody();
           	if (!$body) {
               	$body = $this->messageMime;
           	}
           	$body->addPart($attachment);
           	$this->message->setBody($body);
       	}
       	$this->attachments = [];
   	}
   	return $result;
   }

   /**
	* @param $content
	* @param $fileName
	* @param $fileType
	* @return $this
	*/
   public function addAttachment($content, $fileName, $fileType)
   {
   	$attachmentPart = $this->partFactory->create();
   	$attachmentPart->setContent($content)
       	->setType($fileType)
       	->setFileName($fileName)
       	->setDisposition(Mime::DISPOSITION_ATTACHMENT)
       	->setEncoding(Mime::ENCODING_BASE64);
   	$this->attachments[] = $attachmentPart;

   	return $this;
   }
}
