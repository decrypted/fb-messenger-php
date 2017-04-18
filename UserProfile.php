<?php

namespace pimax;

class UserProfile
{
    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getFirstName()
    {
        return $this->data['first_name'];
    }

    public function getLastName()
    {
        return $this->data['last_name'];
    }

    public function getPicture()
    {
        return $this->data['profile_pic'];
    }

    public function getLocale()
    {
        return $this->data['locale'];
    }

    public function getTimezone()
    {
        return $this->data['timezone'];
    }

    public function getGender()
    {
        return $this->data['gender'];
    }

    public function getLastAdReferral()
    {
        return $this->data['last_ad_referral'];
    }

    public function getPaymentEnabled()
    {
        return $this->data['is_payment_enabled'];
    }

    public function getData() {
        return $this->data;
    }
}