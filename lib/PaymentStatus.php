<?php
namespace AlgorithmicCash;

class PaymentStatus {
    const ProcessingNotAvailable = -2;
    const InvalidRequest = -1;
    const PaymentPending = 0;
    const PaymentSuccess = 1;
    const PaymentSettled = 2;
}
