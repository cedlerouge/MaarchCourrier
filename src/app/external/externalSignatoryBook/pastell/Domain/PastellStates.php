<?php

namespace ExternalSignatoryBook\pastell\Domain;

class PastellStates
{

    private string $errorCode;
    private string $visaState;
    private string $signState;
    private string $refusedVisa;
    private string $refusedSign;

    public function __construct(string $errorCode, string $visaState, string $signState, string $refusedVisa, string $refusedSign)
    {
        $this->errorCode = $errorCode;
        $this->visaState = $visaState;
        $this->signState = $signState;
        $this->refusedVisa = $refusedVisa;
        $this->refusedSign = $refusedSign;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getVisaState(): string
    {
        return $this->visaState;
    }

    public function getSignState(): string
    {
        return $this->signState;
    }

    public function getRefusedVisa(): string
    {
        return $this->refusedVisa;
    }

    public function getRefusedSign(): string
    {
        return $this->refusedSign;
    }

}
