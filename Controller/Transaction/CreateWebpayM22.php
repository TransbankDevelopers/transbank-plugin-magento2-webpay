<?php
namespace Transbank\Webpay\Controller\Transaction;

use Transbank\Webpay\Model\TransbankSdkWebpay;
use Transbank\Webpay\Model\LogHandler;
use \Transbank\Webpay\Model\Webpay;

use \Magento\Sales\Model\Order;

/**
 * Controller for create transaction Webpay
 */
class CreateWebpayM22 extends \Magento\Framework\App\Action\Action {

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                                \Magento\Quote\Model\QuoteManagement $quoteManagement,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {

        parent::__construct($context);

        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteManagement = $quoteManagement;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->log = new LogHandler();
    }

    public function execute() {

        $response = null;
        $order = null;
        $config = $this->configProvider->getPluginConfig();
        $orderStatusCanceled = $config['error_pay'];

        try {

            $guestEmail = isset($_GET['guestEmail']) ? $_GET['guestEmail'] : null;

            $config = $this->configProvider->getPluginConfig();
            $orderStatusPendingPayment = $config['order_status'];

            $tmpOrder = $this->getOrder();
            $this->checkoutSession->restoreQuote();

            $quote = $this->cart->getQuote();

            if ($guestEmail != null) {
                $quote->getBillingAddress()->setEmail($guestEmail);
                $quote->setData('customer_email', $quote->getBillingAddress()->getEmail());
                $quote->setData('customer_firstname', $quote->getBillingAddress()->getFirstName());
                $quote->setData('customer_lastname', $quote->getBillingAddress()->getLastName());
                $quote->setData('customer_is_guest', 1);
            }

            $quoteData = $quote->getData();

            $quote->getPayment()->importData(['method' => Webpay::CODE]);
            $quote->collectTotals()->save();
            $order = $tmpOrder;
            if ($tmpOrder != null && $tmpOrder->getStatus() == $orderStatusCanceled) {
                $order = $this->quoteManagement->submit($quote);
            }

            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());
            $this->checkoutSession->setGrandTotal(round($quote->getGrandTotal()));

            $baseUrl = $this->storeManager->getStore()->getBaseUrl();

            $returnUrl = $baseUrl . $config['URL_RETURN'];
            $finalUrl = $baseUrl . $config['URL_FINAL'];
            $grandTotal = $this->checkoutSession->getGrandTotal();
            $quoteId = $this->checkoutSession->getLastQuoteId();
            $orderId = $this->checkoutSession->getLastOrderId();

            $transbankSdkWebpay = new TransbankSdkWebpay($config);
            $response = $transbankSdkWebpay->initTransaction($grandTotal, $quoteId, $orderId, $returnUrl, $finalUrl);

            $dataLog = array('grandTotal' => $grandTotal, 'quoteId' => $quoteId, 'orderId' => $orderId);
            $message = "<h3>Esperando pago con Webpay</h3><br>" . json_encode($dataLog);

            if (isset($response['token_ws'])) {
                $this->checkoutSession->setTokenWs($response['token_ws']);
                $this->checkoutSession->setPaymentOk('WAITING');
            } else {
                $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                $this->checkoutSession->setPaymentOk('ERROR');
                $message = "<h3>Error en pago con Webpay</h3><br>" . json_encode($response);
            }

            $order->addStatusToHistory($order->getStatus(), $message);
            $order->save();

            $this->checkoutSession->getQuote()->setIsActive(true)->save();
            $this->cart->getQuote()->setIsActive(true)->save();

        } catch (\Exception $e) {
            $message = 'Error al crear transacción: ' . $e->getMessage();
            $this->log->logError($message);
            $response = array('error' => $message);
            if ($order != null) {
                $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                $order->addStatusToHistory($order->getStatus(), $message);
                $order->save();
            }
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($response);
        return $result;
    }

    private function getOrder() {
        try {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId == null) {
                return null;
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            return $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        } catch (Exception $e) {
            return null;
        }
    }
}
