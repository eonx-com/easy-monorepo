<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai;

use EonX\EasyBankFiles\Parsers\Nai\Results\Transactions\Details;

trait TransactionDetailCodes
{
    private static array $transactionCodes = [
        108 => [
            'cdrd' => 'CR',
            'description' => 'Deposit',
            'particulars' => '',
        ],
        175 => [
            'cdrd' => 'CR',
            'description' => 'Cheques',
            'particulars' => 'Cash/Cheques',
        ],
        195 => [
            'cdrd' => 'CR',
            'description' => 'Transfer credits',
            'particulars' => 'Transfer',
        ],
        238 => [
            'cdrd' => 'CR',
            'description' => 'Dividend',
            'particulars' => 'Dividend',
        ],
        252 => [
            'cdrd' => 'CR',
            'description' => 'Reversal Entry',
            'particulars' => 'Reversal',
        ],
        305 => [
            'cdrd' => 'CR',
            'description' => 'Interest Paid',
            'particulars' => '',
        ],
        357 => [
            'cdrd' => 'CR',
            'description' => 'Credit adjustment',
            'particulars' => 'Adjustment',
        ],
        373 => [
            'cdrd' => 'CR',
            'description' => 'Salary',
            'particulars' => '',
        ],
        399 => [
            'cdrd' => 'CR',
            'description' => 'Miscellaneous credits',
            'particulars' => 'Miscellaneous credit',
        ],
        475 => [
            'cdrd' => 'DR',
            'description' => 'Cheques (paid)',
            'particulars' => 'All serial numbers',
        ],
        495 => [
            'cdrd' => 'DR',
            'description' => 'Transfer debits',
            'particulars' => 'Transfer',
        ],
        501 => [
            'cdrd' => 'DR',
            'description' => 'Automatic drawings',
            'particulars' => 'Company’s name (abbreviated)',
        ],
        512 => [
            'cdrd' => 'DR',
            'description' => 'Documentary L/C Drawings/Fees',
            'particulars' => 'Documentary L/C',
        ],
        552 => [
            'cdrd' => 'DR',
            'description' => 'Reversal Debit',
            'particulars' => '',
        ],
        555 => [
            'cdrd' => 'DR',
            'description' => 'Dishonoured cheques ',
            'particulars' => 'Dishonoured cheques',
        ],
        564 => [
            'cdrd' => 'DR',
            'description' => 'Loan fees',
            'particulars' => 'Loan fee',
        ],
        595 => [
            'cdrd' => 'DR',
            'description' => 'FlexiPay',
            'particulars' => 'Merchant name',
        ],
        631 => [
            'cdrd' => 'DR',
            'description' => 'Debit adjustment',
            'particulars' => 'Adjustment',
        ],
        654 => [
            'cdrd' => 'DR',
            'description' => 'Debit Interest',
            'particulars' => 'Interest',
        ],
        677 => [
            'cdrd' => 'DR',
            'description' => 'Transaction Taxes',
            'particulars' => '',
        ],
        698 => [
            'cdrd' => 'DR',
            'description' => 'Fees',
            'particulars' => '',
        ],
        699 => [
            'cdrd' => 'DR',
            'description' => 'Miscellaneous debits',
            'particulars' => 'Miscellaneous debit',
        ],
        905 => [
            'cdrd' => 'CR',
            'description' => 'Credit Interest',
            'particulars' => 'Interest',
        ],
        906 => [
            'cdrd' => 'CR',
            'description' => 'National nominees credits',
            'particulars' => 'National nominees',
        ],
        910 => [
            'cdrd' => 'CR',
            'description' => 'Cash',
            'particulars' => 'Cash',
        ],
        911 => [
            'cdrd' => 'CR',
            'description' => 'Cash/cheques',
            'particulars' => 'Cash/cheques',
        ],
        915 => [
            'cdrd' => 'CR',
            'description' => 'Agent Credits',
            'particulars' => 'Agent number advised',
        ],
        920 => [
            'cdrd' => 'CR',
            'description' => 'Inter-bank credits',
            'particulars' => 'Company’s name (abbreviated)',
        ],
        921 => [
            'cdrd' => 'CR',
            'description' => 'Pension',
            'particulars' => '',
        ],
        922 => [
            'cdrd' => 'CR',
            'description' => 'EFTPOS Transaction',
            'particulars' => '',
        ],
        923 => [
            'cdrd' => 'CR',
            'description' => 'Family Allowance',
            'particulars' => '',
        ],
        924 => [
            'cdrd' => 'CR',
            'description' => 'Agent Credits',
            'particulars' => '',
        ],
        925 => [
            'cdrd' => 'CR',
            'description' => 'Bankcard credits',
            'particulars' => 'Bankcard',
        ],
        926 => [
            'cdrd' => 'CR',
            'description' => 'Credit Card Refund',
            'particulars' => '',
        ],
        930 => [
            'cdrd' => 'CR',
            'description' => 'Credit balance transfer',
            'particulars' => 'Balance transfer',
        ],
        935 => [
            'cdrd' => 'CR',
            'description' => 'Credits summarised',
            'particulars' => 'Not applicable',
        ],
        936 => [
            'cdrd' => 'CR',
            'description' => 'EFTPOS',
            'particulars' => 'Merchant name',
        ],
        938 => [
            'cdrd' => 'CR',
            'description' => 'Coca credit transactions',
            'particulars' => 'Not applicable',
        ],
        950 => [
            'cdrd' => 'DR',
            'description' => 'Loan establishment fees',
            'particulars' => 'Establishment fee',
        ],
        951 => [
            'cdrd' => 'DR',
            'description' => 'Account keeping fees',
            'particulars' => 'Account keeping fee',
        ],
        952 => [
            'cdrd' => 'DR',
            'description' => 'Unused limit fees',
            'particulars' => 'Unused limit fee',
        ],
        953 => [
            'cdrd' => 'DR',
            'description' => 'Security fees',
            'particulars' => 'Security fee',
        ],
        955 => [
            'cdrd' => 'DR',
            'description' => 'Charges',
            'particulars' => 'Charge (or description)',
        ],
        956 => [
            'cdrd' => 'DR',
            'description' => 'National nominee debits',
            'particulars' => 'National nominees',
        ],
        960 => [
            'cdrd' => 'DR',
            'description' => 'Stamp duty-cheque book',
            'particulars' => 'Cheque book',
        ],
        961 => [
            'cdrd' => 'DR',
            'description' => 'Stamp duty',
            'particulars' => 'Stamp duty',
        ],
        962 => [
            'cdrd' => 'DR',
            'description' => 'Stamp duty-security',
            'particulars' => 'Security stamp duty',
        ],
        963 => [
            'cdrd' => 'DR',
            'description' => 'EFTPOS Debit',
            'particulars' => '',
        ],
        964 => [
            'cdrd' => 'DR',
            'description' => 'Credit Card Cash Advance',
            'particulars' => '',
        ],
        970 => [
            'cdrd' => 'DR',
            'description' => 'State government tax',
            'particulars' => 'State government credit tax',
        ],
        971 => [
            'cdrd' => 'DR',
            'description' => 'Federal government tax',
            'particulars' => 'Federal government debit tax',
        ],
        972 => [
            'cdrd' => 'DR',
            'description' => 'Credit Card Purchase',
            'particulars' => '',
        ],
        975 => [
            'cdrd' => 'DR',
            'description' => 'Bankcards',
            'particulars' => 'Bankcard',
        ],
        980 => [
            'cdrd' => 'DR',
            'description' => 'Debit balance transfers',
            'particulars' => 'Balance transfers',
        ],
        985 => [
            'cdrd' => 'DR',
            'description' => 'Debits summarised',
            'particulars' => 'Not applicable',
        ],
        986 => [
            'cdrd' => 'DR',
            'description' => 'Cheques summarised',
            'particulars' => 'Not applicable',
        ],
        987 => [
            'cdrd' => 'DR',
            'description' => 'Non-cheques summarised',
            'particulars' => 'Not applicable',
        ],
        988 => [
            'cdrd' => 'DR',
            'description' => 'Coca debit transaction',
            'particulars' => 'Not applicable',
        ],
    ];

    /**
     * Return Transaction Detail Code.
     */
    public function getTransactionCodeDetails(string $code): ?Details
    {
        if (isset(self::$transactionCodes[(int)$code]) === false) {
            return null;
        }

        $details = self::$transactionCodes[(int)$code];

        return new Details([
            'description' => $details['description'],
            'particulars' => $details['particulars'],
            'type' => $details['cdrd'],
        ]);
    }
}
