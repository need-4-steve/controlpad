<?php

namespace App\Services;


class EmailService
{
    public function __construct()
    {
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
                return str_replace($var, $request['company_address'], $email);
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

    public function getVariables($title)
    {
      $varables = ['[first_name]', '[last_name]', '[company_name]', '[back_office_logo]', '[company_address]'];
      switch ($title) {
          case 'sponsor_notice':
              $varables = array_merge($varables, ['[sponsor_first_name]', '[sponsor_last_name]', '[email]', '[phone]']);
              return $varables;
          case 'invoice':
              $varables = array_merge($varables, ['[amount]', '[invoice_url]', '[note]']);
                return $varables;
          case 'fulfilled':
              $varables =  array_merge($varables, ['[orderlines]', '[order_receipt_id]', '[order_subtotal]', '[order_tax]', '[order_shipping]', '[order_discount]', '[order_total]']);
                return $varables;
          case 'expire_notice':
              $varables[] = '[billing_date]';
                return $varables;
          case 'new_order_received':
              $varables = array_merge($varables, ['[customer_first_name]', '[customer_last_name]', '[customer_email]', '[orderlines]', '[order_receipt_id]']);
                return $varables;
          case 'order_receipt':
              $varables = array_merge($varables, ['[orderlines]', '[order_receipt_id]', '[order_subtotal]', '[order_tax]', '[order_shipping]', '[order_discount]', '[order_total]']);
                return $varables;
          case 'renew_fail':
              $varables = array_merge($varables, ['[billing_date]', '[reason]']);
              return $varables;
          default:
              return $varables;
      }
    }

    public function getUserInfo($request)
    {
      $info = ['first_name'=> $request['user']['first_name'],
              'last_name' => $request['user']['last_name'],
              'company_name' => $request['company_name'],
              'back_office_logo' => $request['back_office_logo'],
              'company_address' =>$request['company_address']
            ];
      switch ($request['title']) {
          case 'sponsor_notice':
              $info = array_merge($info, ['sponsor_first_name'=>  $request['sponsor']['first_name'],
                                          'sponsor_last_name'=> $request['sponsor']['last_name'],
                                          'email'=> $request['user']['email'],
                                          'phone'=>$request['user']['phone']
                                        ]);
              return $info;
          case 'invoice':
              $info = array_merge($info, ['amount' => $request['invoice']['amount'],
                                          'invoice_url' => $request['invoice']['url'],
                                          'note' => $request['invoice']['note']
                                        ]);
                return $info;
          case 'fulfilled':
              $info =  array_merge($info, ['orderlines' => $request['orderlines'],
                                           'order_receipt_id' => $request['order']['receipt_id'],
                                           'order_subtotal' => $request['order']['subtotal'],
                                           'order_tax' => $request['order']['tax'],
                                           'order_shipping' => $request['order']['shipping'],
                                           'order_discount' => $request['order']['discount'],
                                           'order_total' => $request['order']['total']
                                          ]);
                return $info;
          case 'expire_notice':
              $info = array_merge($info, ['billing_date'=> $request['billing_date']]);
                return $info;
          case 'new_order_received':
              $info = array_merge($info, ['customer_first_name' => $request['buyer']['first_name'],
                                              'customer_last_name' => $request['buyer']['last_name'],
                                              'customer_email' => $request['buyer']['email'],
                                              'orderlines' => $request['orderlines'],
                                              'order_receipt_id' => $request['order']['receipt_id']
                                            ]);
                return $info;
          case 'order_receipt':
              $info = array_merge($info, ['orderlines' => $request['orderlines'],
                                              'order_receipt_id' => $request['order']['receipt_id'],
                                              'order_subtotal' => $request['order']['subtotal'],
                                              'order_tax' => $request['order']['tax'],
                                              'order_shipping' => $request['order']['shipping'],
                                              'order_discount' => $request['order']['discount'],
                                              'order_total' => $request['order']['total']
                                            ]);
                return $info;
          case 'renew_fail':
              $info = array_merge($info, ['billing_date' => $request['billing_date'],
                                          'reason' => $request['reason']
                                        ]);
              return $info;
          default:
              return $info;
      }
    }

    public function buildExample($title, $request)
    {
      $info = ['first_name'=> 'John',
              'last_name' => 'Smith',
              'company_name' => $request['companyName'],
              'back_office_logo' => $request['backOfficeLogo'],
              'company_address' =>$request['companyAddress']
            ];
      switch ($title) {
          case 'sponsor_notice':
              $info = array_merge($info, ['sponsor_first_name'=>  'Mary',
                                          'sponsor_last_name'=> 'Jones',
                                          'email'=> 'john_smith@mail.com',
                                          'phone'=>'555-555-5555'
                                        ]);
              return $info;
          case 'invoice':
              $info = array_merge($info, ['amount' => '48.37',
                                          'invoice_url' =>'controlpad.com/orders/invoice/1517329741zS2GAPk6tQvfZirTXJG5',
                                          'note' => 'Thank you'
                                        ]);
                return $info;
          case 'fulfilled':
              $info =  array_merge($info, ['orderlines' => [['name'=>'Product Name Here',
                                                        'variant' => 'Variant Name Here',
                                                        'option' => 'Option Here',
                                                        'price' => 50,
                                                        'quantity'=> 2],
                                                        ['name'=>'Product Name Here',
                                                        'variant' => 'Variant Name Here',
                                                        'option' => 'Option Here',
                                                        'price' => 25,
                                                        'quantity'=> 1]
                                                    ],
                                           'order_receipt_id' => 'SPKPX9-39494',
                                           'order_subtotal' => '55.36',
                                           'order_tax' => '5.73',
                                           'order_shipping' => '5.00',
                                           'order_discount' => '5.00',
                                           'order_total' => '61.09'
                                          ]);
                return $info;
          case 'expire_notice':
              $info = array_merge($info, ['billing_date'=> '12/05/2018']);
                return $info;
          case 'new_order_received':
              $info = array_merge($info, ['customer_first_name' => 'Sue',
                                              'customer_last_name' => 'Brown',
                                              'customer_email' => 'sue_Brown@mail.com',
                                              'orderlines' => [['name'=>'Product Name Here',
                                                                'variant' => 'Variant Name Here',
                                                                'option' => 'Option Here',
                                                                'price' => 50,
                                                                'quantity'=> 2],
                                                                ['name'=>'Product Name Here',
                                                                'variant' => 'Variant Name Here',
                                                                'option' => 'Option Here',
                                                                'price' => 25,
                                                                'quantity'=> 1]
                                                            ],
                                              'order_receipt_id' => 'SPKPX9-39495'
                                            ]);
                return $info;
          case 'order_receipt':
              $info = array_merge($info, ['orderlines' => [['name'=>'Product Name Here',
                                              'variant' => 'Variant Name Here',
                                              'option' => 'Option Here',
                                              'price' => 50,
                                              'quantity'=> 2],
                                              ['name'=>'Product Name Here',
                                              'variant' => 'Variant Name Here',
                                              'option' => 'Option Here',
                                              'price' => 25,
                                              'quantity'=> 1]
                                              ],
                                              'order_subtotal' => '55.36',
                                              'order_tax' => '5.73',
                                              'order_shipping' => '5.00',
                                              'order_discount' => '5.00',
                                              'order_total' => '61.09',
                                              'order_receipt_id' => 'SPKPX9-39495'
                                            ]);
                return $info;
          case 'renew_fail':
              $info = array_merge($info, ['billing_date' => '12/05/2018',
                                          'reason' => 'Damaged product'
                                        ]);
              return $info;
          default:
              return $info;
      }
    }
}
