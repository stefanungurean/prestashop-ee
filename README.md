# prestashop-ee
Wirecard Payment Processing Gateway Plugin for Prestashop

The plugin is currently under development and should not be used yet.


##How to add a new payment

1) Add the configuration to the wirecardpaymentgateway config
array.

2) Create a class having the name of the method with only first
letter uppercase for payment method name and camel case for rest of name
example CreditcardPaymentService.

3) Implement PaymentService.inc interface and use PaymentServiceTrait.

4) Create a class having the name of the method with only first
letter uppercase for paymenth method name and camel case for the rest
example CreditcardConfiguration.

5) Implements TransactionConfig interface TransactionConfigTrait

6) Create a form same way if of other classes and implement PaymentForm necessary and set config array with
is_form = true false otherwise

7) Just modify TransactionParams if needed.