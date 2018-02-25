<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 08:44 AM
 */

class TransactionParams
{

    private $notificationUrl;
    private $redirectUrls;
    private $basket;
    private $orderNumber;
    private $orderDetail;
    private $descriptor;
    private $tokenId;
    private $amount;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }



    /**
     * @return mixed
     */
    public function getNotificationUrl()
    {
        return $this->notificationUrl;
    }

    /**
     * @param mixed $notificationUrl
     */
    public function setNotificationUrl($notificationUrl)
    {
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrls()
    {
        return $this->redirectUrls;
    }

    /**
     * @param mixed $redirectUrls
     */
    public function setRedirectUrls($redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    /**
     * @return mixed
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @param mixed $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param mixed $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return mixed
     */
    public function getOrderDetail()
    {
        return $this->orderDetail;
    }

    /**
     * @param mixed $orderDetail
     */
    public function setOrderDetail($orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    /**
     * @return mixed
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param mixed $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * @return mixed
     */
    public function getTokenId()
    {
        return $this->tokenId;
    }

    /**
     * @param mixed $tokenId
     */
    public function setTokenId($tokenId)
    {
        $this->tokenId = $tokenId;
    }


}