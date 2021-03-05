<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use EonX\EasyWebhook\Signers\Rs256Signer;
use Nyholm\Psr7\Uri;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

final class TestAmexAuthCommand extends Command
{
    protected static $defaultName = 'amex:test-auth';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $signer = new Rs256Signer();

        $payload = $this->getPayload();

        \var_dump($payload);

        $payload = (string) \json_encode(\json_decode($payload, true));
        $payload = \str_replace(' ', '', $payload);

        \var_dump($payload);

        $uri = new Uri('https://api.qasb.americanexpress.com/sb/merchant/v1/acquisitions/sellers');
        $clientId = 'vcVpXFrvlObb78qe8clRGG7hi6pwWAJL';
        $clientSecret = 'uSAitmiesy1AkmhSsFLQjNLNzvG3dyjN';
        $ts = 1614819624;
        $nonce = '45dea93b-afaa-4f09-b69e-d561ea3ead49';
        $bodyHash = \base64_encode($signer->sign($payload, $clientSecret));

        $baseString = $ts . '\n'
            . $nonce . '\n'
            . 'POST' . '\n'
            . '/sb/merchant/v1/acquisitions/sellers' . '\n'
            . 'api.qasb.americanexpress.com' . '\n'
            . '443' . '\n'
            . $bodyHash . '\n';

        $signature = \base64_encode($signer->sign($baseString, $clientSecret));

        \var_dump($nonce, $ts, $bodyHash, $baseString, $signature);

        $header = \sprintf(
            'MAC ID="%s",ts="%s",nonce="%s",bodyhash="%s",mac="%s"',
            $clientId,
            $ts,
            $nonce,
            $bodyHash,
            $signature
        );

        \var_dump($header);

        $httpClient = HttpClient::create(['base_uri' => $uri->__toString()]);

        $response = $httpClient->request('POST', '', [
           'headers' => [
               'Authorization' => $header,
               'Content-Type' => 'application/json',
               'x-amex-api-key' => $clientId,
           ],
            'body' => $payload,
        ]);

        $info = $response->getInfo();

        \dump($response->getStatusCode(), $response->getContent(false), $info);

        return 0;
    }

    private function getPayload(): string
    {
        return '{
  "se_setup_request_count": 1,
  "message_id": "egr2bt362",
  "se_setup_requests": [
    {
      "record_number": "0000036500",
      "participant_se": "1021311634",
      "submitter_id": "1030026553",
      "se_detail_status_code": "36500",
      "se_status_code_change_date": "2015/12/25",
      "language_preference_code": "EN",
      "japan_credit_bureau_indicator": "0000036500",
      "marketing_indicator": "Y",
      "ownership_type_indicator": "D",
      "seller_transacting_indicator": "Y",
      "client_defined_code": "36500",
      "seller": {
        "seller_id": "GSMF093019APIX1006",
        "seller_url": "www.gsmfautomationtool.com/acquisition",
        "seller_status": "Success",
        "seller_mcc": "5999",
        "seller_legal_name": "John Doe",
        "seller_dba_name": "John Doe",
        "seller_business_registration_number": "0000036500",
        "seller_business_phone_number": "9914023611",
        "seller_email_address": "john.doe@example.com",
        "seller_currency_code": "USD",
        "seller_start_date": "2015/12/25",
        "seller_term_date": "2015/12/26",
        "seller_charge_volume": "36500",
        "seller_transaction_count": "425",
        "seller_chargeback_count": "425",
        "seller_chargeback_amount": "425",
        "seller_street_address": {
          "address_line_1": "100 Elm Street",
          "address_line_2": "Oak Avenue",
          "address_line_3": "Maple Court",
          "address_line_4": "Third Floor",
          "address_line_5": "Suite A",
          "city_name": "New York",
          "region_code": "NY",
          "postal_code": "85032",
          "country_code": "US"
        }
      },
      "significant_owners": {
        "first_owner": {
          "first_name": "FOFIRSTNM001",
          "last_name": "Smith",
          "identification_number": "0000036500",
          "date_of_birth": "2015/12/27",
          "street_address": {
            "address_line_1": "100 Elm Street",
            "address_line_2": "Oak Avenue",
            "address_line_3": "Maple Court",
            "address_line_4": "Third Floor",
            "address_line_5": "Suite A",
            "city_name": "New York",
            "region_code": "New York",
            "postal_code": "85032",
            "country_code": "US"
          }
        },
        "second_owner": {
          "first_name": "Adam",
          "last_name": "Smith",
          "identification_number": "0000036500",
          "date_of_birth": "2015/12/28",
          "street_address": {
            "address_line_1": "100 Elm Street",
            "address_line_2": "Oak Avenue",
            "address_line_3": "Maple Court",
            "address_line_4": "Third Floor",
            "address_line_5": "Suite A",
            "city_name": "New York",
            "region_code": "New York",
            "postal_code": "85032",
            "country_code": "US"
          }
        },
        "third_owner": {
          "first_name": "Adam",
          "last_name": "Smith",
          "identification_number": "0000036500",
          "date_of_birth": "2015/12/29",
          "street_address": {
            "address_line_1": "100 Elm Street",
            "address_line_2": "Oak Avenue",
            "address_line_3": "Maple Court",
            "address_line_4": "Third Floor",
            "address_line_5": "Suite A",
            "city_name": "New York",
            "region_code": "New York",
            "postal_code": "85032",
            "country_code": "US"
          }
        },
        "fourth_owner": {
          "first_name": "Adam",
          "last_name": "Smith",
          "identification_number": "0000036500",
          "date_of_birth": "2015/12/30",
          "street_address": {
            "address_line_1": "100 Elm Street",
            "address_line_2": "Oak Avenue",
            "address_line_3": "Maple Court",
            "address_line_4": "Third Floor",
            "address_line_5": "Suite A",
            "city_name": "New York",
            "region_code": "New York",
            "postal_code": "85032",
            "country_code": "US"
          }
        }
      },
      "authorized_signer": {
        "first_name": "Adam",
        "last_name": "Smith",
        "identification_number": "0000036500",
        "date_of_birth": "2015/12/31",
        "street_address": {
          "address_line_1": "100 Elm Street",
          "address_line_2": "Oak Avenue",
          "address_line_3": "Maple Court",
          "address_line_4": "Third Floor",
          "address_line_5": "Suite A",
          "city_name": "New York",
          "region_code": "New York",
          "postal_code": "85032",
          "country_code": "US"
        },
        "title": "MR."
      },
      "sale": {
        "channel_indicator_code": "DS",
        "channel_name": "CN",
        "represent_id": "36500",
        "iso_register_number": "0000036500"
      }
    }
  ]
}';
    }
}
