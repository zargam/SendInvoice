<?php

namespace Create\InvoiceAttachement\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Email sender builder for attachments
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    private $renderInvoice;

     /**
     * @var \Magento\Framework\App\Request\Http
     */
     private $request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

     /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */

    private $reader;


    /**
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     * @param \Magento\Framework\Filesystem\Driver\File $reader
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\App\Request\Http $request,
        Invoice $renderInvoice,
        DateTime $dateTime
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder
        );
        $this->reader = $reader;
        $this->renderInvoice = $renderInvoice;
        $this->dateTime = $dateTime;
        $this->request = $request;

    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {

        $this->configureEmailTemplate();
        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );
        $copyTo = $this->identityContainer->getEmailCopyTo();
        if (
            !empty($copyTo) &&
            $this->identityContainer->getCopyMethod() == "bcc"
        ) {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

       if($this->request->getFullActionName() == "sales_order_invoice_save" || $this->request->getFullActionName() == "sendinvoice_index_index"){
        $invoice = $this->_getDataTemplate();
        if(!empty($invoice)){
            $pdfContent = $this->renderInvoice->getPdf($invoice)->render();
            //$date = $this->dateTime->date("Y-m-d_H-i-s");
            $this->transportBuilder->addAttachment(
                $pdfContent,
                sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                "application/pdf"
             );
           }
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    private function _getDataTemplate()
    {
        $data = $this->templateContainer->getTemplateVars();
        if (array_key_exists("invoice_id", $data)) {
            return [$data["invoice"]];
        }
        if (isset($data["order"]) && $data["order"]->hasInvoices()) {
            return $data["order"]->getInvoiceCollection()->getItems();
        }
        return [$data["invoice"]] ?? "";
    }
}
