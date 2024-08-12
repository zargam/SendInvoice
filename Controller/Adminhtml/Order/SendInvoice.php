<?php

namespace Create\InvoiceAttachement\Controller\Adminhtml\Order;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Pdf\Invoice;

/**
 * Index controller class
 */
class SendInvoice extends \Magento\Backend\App\Action
{

    /**
     * @var Context
     */
    private    $context;
    protected  $messageManager;
    protected  $invoiceRepositoryInterface;
    protected  $invoice;
    protected  $invoiceNotifier;
    protected  $customerFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InvoiceRepositoryInterface   $invoiceRepositoryInterface,
        Invoice $invoice,
        \Magento\Sales\Model\Order\InvoiceNotifier $invoiceNotifier,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->context = $context;
        $this->invoiceRepositoryInterface = $invoiceRepositoryInterface;
        $this->messageManager  = $messageManager;
        $this->invoice         = $invoice;
        $this->invoiceNotifier = $invoiceNotifier;
        $this->customerFactory = $customerFactory;


        parent::__construct($context);
    }

    /**
     * Post user question
     * @return Redirect
     */
    public function execute()
    {
        $invoice_id = $this->getRequest()->getParam('invoice_id');
        $customer_id = $this->getRequest()->getParam('customer_id');
        if(!$invoice_id || !$customer_id){
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order');
            return $resultRedirect;
        }
        $customerFactory = $this->customerFactory->create();
        $customer = $customerFactory->load($customer_id);
        $customer_email = $customer->getEmail();
        $invoice = $this->invoiceRepositoryInterface->get($invoice_id);
        if ($invoice) {
            $invoice->setCustomerEmail($customer_email);
            try {
                $this->invoiceNotifier->notify($invoice);
                $this->messageManager->addSuccessMessage(__('Invoice Sent successfully to '.$customer_email));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addSuccessMessage(__($e->getMessage()));
            } catch (\Exception $e) {
                $this->messageManager->addSuccessMessage(__($e->getMessage()));
            }
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
	}

    protected function _isAllowed()
    {
        return true;
    }


}
