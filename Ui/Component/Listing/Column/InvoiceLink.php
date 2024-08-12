<?php
namespace Create\InvoiceAttachement\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class InvoiceLink extends Column
{
    protected $_orderRepository;
    protected $helper;

    public function __construct( ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order       = $this->_orderRepository->get($item["entity_id"]);
                $invoiceData = $order->getInvoiceCollection();
                $html ='';
                $message =__("Are you sure you want to send the invoice?");
                if(!empty($invoiceData->getData())){
                    $invoice_id   = $invoiceData->getdata()[0]['entity_id'];
                    $customer_id  = $order->getCustomerId();
                    $invoiceUrl  = $this->context->getUrl('sales/order/sendinvoice', ['invoice_id' => $invoice_id,'customer_id' => $customer_id]);
                    $buttonActionInoice = "confirmSetLocation('{$message}', '{$invoiceUrl}')";
                    $html        =  '<button  class ="primary"  onclick="'.$buttonActionInoice.'">';
                    $html       .=  __('Send Invoice');
                    $html       .=  "</button>";
                }else{
                    $html  =  "<p style ='color:red'>".__('Invoice has not been generated')."</p>";
                }

                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}
