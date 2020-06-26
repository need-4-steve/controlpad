<?php

namespace App\Services\Text;

class TextService
{
    public function __construct()
    {
        $this->globalSettings = app('globalSettings');
        $this->basicParams = [
            '[first_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['first_name'], $email);
            },
            '[last_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['last_name'], $email);
            },
            '[sponsor_first_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['sponsor_first_name'], $email);
            },
            '[sponsor_last_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['sponsor_last_name'], $email);
            },
            '[customer_first_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['customer_first_name'], $email);
            },
            '[customer_last_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['customer_last_name'], $email);
            },
            '[customer_email]' => function ($var, $request, $email) {
                return str_replace($var, $request['customer_email'], $email);
            },
            '[company_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['company_name'], $email);
            },
            '[back_office_logo]' => function ($var, $request, $email) {
                $logo = '<img width="150px" src="'. $request['back_office_logo'] . '">';
                return str_replace($var, $logo, $email);
            },
            '[customer_first_name]' => function ($var, $request, $email) {
                return str_replace($var, $request['customer_first_name'], $email);
            },
            '[orderlines]' => function ($var, $request, $email) {
                $orderlines = $this->orderlinesToStringTable($request['orderlines']);
                return str_replace($var, $orderlines, $email);
            },
            '[subscription_lines]' => function ($var, $request, $email) {
                return str_replace($var, $this->subscriptionLinesToStringTable($request['subscription_lines']), $email);
            },
            '[order_subtotal]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $subtotal = money_format('%.2n', $request['order_subtotal']);
                return str_replace($var, $subtotal, $email);
            },
            '[order_tax]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $tax = money_format('%.2n', $request['order_tax']);
                return str_replace($var, $tax, $email);
            },
            '[order_shipping]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $shipping = money_format('%.2n', $request['order_shipping']);
                return str_replace($var, $shipping, $email);
            },
            '[order_total]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $total = money_format('%.2n', $request['order_total']);
                return str_replace($var, $total, $email);
            },
            '[order_receipt_id]' => function ($var, $request, $email) {
                return str_replace($var, $request['order_receipt_id'], $email);
            },
            '[order_discount]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $discount = money_format('%.2n', $request['order_discount']);
                return str_replace($var, $discount, $email);
            },
            '[billing_date]' => function ($var, $request, $email) {
                return str_replace($var, $request['billing_date'], $email);
            },
            '[reason]' => function ($var, $request, $email) {
                return str_replace($var, $request['reason'], $email);
            },
            '[amount]' => function ($var, $request, $email) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $amount = money_format('%.2n', $request['amount']);
                return str_replace($var, $amount, $email);
            },
            '[invoice_url]'=> function ($var, $request, $email) {
                return str_replace($var, $request['invoice_url'], $email);
            },
            '[note]'=> function ($var, $request, $email) {
                return str_replace($var, $request['note'], $email);
            },
            '[email]'=> function ($var, $request, $email) {
                return str_replace($var, $request['email'], $email);
            },
            '[phone]'=> function ($var, $request, $email) {
                return str_replace($var, $request['phone'], $email);
            },
            '[company_address]' => function ($var, $request, $email) {
                $address = $this->globalSettings->getGlobal('address', 'value');
                return str_replace($var, $address, $email);
            },
            '[coupon_code]' => function ($var, $request, $email) {
                return str_replace($var, $request['coupon_code'], $email);
            },
            '[tracking_number]' => function ($var, $request, $email) {
                return str_replace($var, $request['tracking_number'], $email);
            },
            '[tracking_url]' => function ($var, $request, $email) {
                return str_replace($var, $request['tracking_url'], $email);
            },
            '[tracking_link]' => function ($var, $request, $email) {
                return str_replace($var, $request['tracking_link'], $email);
            },
            '[buyer_first_name]' => function ($var, $request, $content) {
                return str_replace($var, $request['buyer_first_name'], $content);
            },
            '[buyer_last_name]' => function ($var, $request, $content) {
                return str_replace($var, $request['buyer_last_name'], $content);
            },
            '[buyer_full_name]' => function ($var, $request, $content) {
                return str_replace($var, $request['buyer_full_name'], $content);
            },
            '[buyer_email]' => function ($var, $request, $content) {
                return str_replace($var, $request['buyer_email'], $content);
            },
            '[seller_full_name]' => function ($var, $request, $content) {
                return str_replace($var, $request['seller_full_name'], $content);
            },
            '[seller_email]' => function ($var, $request, $content) {
                return str_replace($var, $request['seller_email'], $content);
            },
            '[subscription_subtotal]' => function ($var, $request, $content) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                return str_replace($var, money_format('%.2n', $request['subscription_subtotal']), $content);
            },
            '[subscription_discount]' => function ($var, $request, $content) {
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                return str_replace($var, money_format('%.2n', $request['subscription_discount']), $content);
            },
            '[subscription_schedule]' => function ($var, $request, $content) {
                return str_replace($var, $request['subscription_schedule'], $content);
            },
            '[subscription_next_billing_date]' => function ($var, $request, $content) {
                return str_replace($var, $request['subscription_next_billing_date'], $content);
            },
            '[subscription_id]' => function ($var, $request, $content) {
                return str_replace($var, $request['subscription_id'], $content);
            },
            '[backoffice_login_link]' => function ($var, $request, $content) {
                return str_replace($var, '<a href="//'.$request['backoffice_login_link'].'">'.$request['backoffice_login_link'].'</a>', $content);
            }
        ];
    }
    public function parseText($request, $variables, $content)
    {
        foreach ($variables as $var) {
            $content = $this->basicParams[$var]($var, $request, $content);
        }
        return $content;
    }

    public function orderlinesToStringTable($orderlines)
    {
        $header = '
        <table style="display: block; margin: 0 auto">
        <tr>
          <th style="padding-right:20px; text-align:center;">Quantity</th>
          <th style="padding-right:20px; text-align:center;">Product Name</th>
          <th style="padding-right:20px; text-align:left;">Variant</th>
          <th style="padding-right:20px; text-align:left;">Option</th>
          <th style="padding-right:20px; text-align:center;">Price Each</th>
          <th text-align:left;>Line Total</th>
        </tr>';
        $string = '';
        foreach ($orderlines as $line) {
            $string2 = '<tr>
            <td style="padding-right:20px;">'. $line['quantity']. '</td>
            <td style="padding-right:20px;"> '.$line['name'] . '</td>
            <td style="padding-right:20px;"> '.$line['variant'] . '</td>
            <td style="padding-right:20px;"> '.$line['option'] . '</td>
            <td style="padding-right:20px;"> $' . money_format('%!n', $line['price']) . '</td>
            <td> $' . money_format('%!n', $line['price'] * $line['quantity']) . '</td>';
            $string = $string2 . $string;
        }
        $string = $string . '</tr></table>';
        $order = $header . $string;
        return $order;
    }

    public function subscriptionLinesToStringTable($sublines)
    {
        $header = '
        <table style="display: block; margin: 0 auto">
        <tr>
          <th style="padding-right:20px; text-align:center;">Quantity</th>
          <th style="padding-right:20px; text-align:center;">Product Name</th>
          <th style="padding-right:20px; text-align:left;">Variant</th>
          <th style="padding-right:20px; text-align:left;">Option</th>
          <th style="padding-right:20px; text-align:center;">Price Each</th>
          <th text-align:left;>Line Total</th>
        </tr>';
        $string = '';
        foreach ($sublines as $line) {
            $string2 = '<tr>
            <td style="padding-right:20px;">'. $line['quantity']. '</td>
            <td style="padding-right:20px;"> '. ($line['item_id'] != null ? $line['items'][0]['product_name'] : $line['bundle_name']) . '</td>
            <td style="padding-right:20px;"> '. ($line['item_id'] != null ? $line['items'][0]['variant_name'] : '') . '</td>
            <td style="padding-right:20px;"> '. ($line['item_id'] != null ? $line['items'][0]['option'] : '') . '</td>
            <td style="padding-right:20px;"> $' . money_format('%!n', $line['price']) . '</td>
            <td> $' . money_format('%!n', $line['price'] * $line['quantity']) . '</td>';
            $string = $string2 . $string;
        }
        $string = $string . '</tr></table>';
        return $header . $string;
    }
}
