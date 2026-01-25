<?php $color        =   '#333333'; ?>
<!DOCTYPE html>
<html>

<head>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJ+M9w8E6JvDdfLprXNXH4gFQH1K3lrxV5vZIq3Vu7vo+zNmBB2B8hbdxThj" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali&display=swap" rel="stylesheet">

    <title>INV-{{ $order->code }}</title>
    <!-- <style>
        body {
            font-family: '{{ $order->font_name }}';
            font-size: 10pt;
            line-height: 13pt;
            color: #000;
        }

        p {
            margin: 4pt 0 0 0;
        }

        td {
            vertical-align: top;
        }

        .items td {
            border: 0.2mm solid #dadee1;
            background-color: #ffffff;
        }

        .items tr.border-less td {
            border: 0;
            background-color: #ffffff;
        }

        table thead td {
            vertical-align: bottom;
            text-transform: uppercase;
            font-size: 8pt;
            font-weight: bold;
            background-color: #dadee1;
            color: #333;
        }

        table thead td {
            border-bottom: 0.2mm solid #dadee1;
        }

        table .last td {
            border-bottom: 0.2mm solid #dadee1;
        }

        table .first td {
            border-top: 0.2mm solid #dadee1;
        }

        .watermark {
            text-transform: uppercase;
            font-weight: bold;
            position: absolute;
            left: 100px;
            top: 400px;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style> -->

  <style>
      .main_container {
          width: 100%;
          margin: 50px auto;
      }

      .order_details_table {
          width: 100%;
          border-collapse: collapse;
      }

      .order_details_table th, .order_details_table td {
          padding: 10px 15px;
          border: 1px solid #656565;
      }

      .order_details_table th {
          background-color: #656565;
          color: white;
          text-align: left;
      }

      .product_column, .quantity_column, .price_column {
          width: 20%;
      }

      .table_billing_address {
          font-weight: 700;
          text-align: left;
      }

      .table_billing_addresss_details {
          text-align: left;
      }

      .subtotal_section {
          display: flex;
          justify-content: space-between;
      }

      .subtotal_title {
          font-weight: 700;
      }

      .subtotal_amount {
          font-weight: 500;
      }

      .thankyou_container {
          width: 100%;
          text-align: center;
          margin-top: 35px;
          font-weight: bold;
      }

      .fs-1 {
          font-size: 1.5em;
      }

      .opacity-25 {
          opacity: 0.25;
      }

      .subtotal_section {
          padding: 10px 0;
      }

      .subtotal_title {
          font-weight: 700;
          margin-right: 10px;  /* Space between the title and the amount */
      }

      .subtotal_amount {
          font-weight: 500;
      }

      .table_body {
          padding: 10px 15px;
          border-left: 1px dotted black;   /* Left border */
          border-right: 1px dotted black;  /* Right border */
          border-bottom: 1px dotted black; /* Bottom border */
      }

      .product_data,
      .quantity_data,
      .price_data {
          padding: 5px 10px;
          vertical-align: middle;
      }


      .orderno{
          font-weight: bold;
      }

      @font-face {
    font-family: 'SolaimanLipi';
    src: url('/fonts/solaiman_lipi.ttf') format('truetype');
}

body {
    font-family: 'SolaimanLipi', sans-serif;
}


  </style>

</head>

<body>
<div class="container">
    <div class="main_container">

        <div >
            <div style="float: left; font-size: 2rem; font-weight: 600;"> Your Order Complete</div>
{{--            <div style="float: right; font-size: 2rem; font-weight: 600;">Dolbear</div>--}}
        </div>

        <p style="float: left; font-size: 1.3rem; font-weight: 600;"> Order Details</p>
        @auth
            <p> Hi {{ auth()->user()->full_name }}, </p>
        @endauth

        <p class="my-3">Just to let you know - we've received your <span class="orderno">{{ __('order_id') }} {{ $order->code }}</span> and it is now being processed: Pay with cash upon delivery.</p>


            <p >[Order {{ $order->code }}] ({{ $order->date }})</p>

        <table class="order_details_table">



            <tr class="table_header">
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>

            @foreach ($order->orderDetails as $key => $orderDetail)
                <tr class="table_body">
                    <td class="product_data">
                        @if(!blank($orderDetail->product))
                            <div>{{ $orderDetail->product->name }}</div>
                            <div class="ml-1">
                                {{ $orderDetail->product->getTranslation('name', \App::getLocale()) }}
                                @if($orderDetail->variation != null)
                                    ({{ $orderDetail->variation }})
                                @endif
                            </div>
                        @else
                            <div>N/A</div>
                        @endif
                    </td>
                    <td class="quantity_data">{{ $orderDetail->quantity }}</td>
                    <td class="price_data">{{ number_format($orderDetail->price, 2) }}</td>
                    <td class="price_data">{{ number_format($orderDetail->discount * $orderDetail->quantity, 2) }}</td>
                    <td class="price_data"> <svg fill="#000000" width="15px" height="15px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                        {{ format_price_without_symbol($orderDetail->price * $orderDetail->quantity) }}</td>
                </tr>
            @endforeach


            <tr class="table_body" style="border-bottom: none; ">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Subtotal: </span>
                        <span class="subtotal_amount">
                            <svg fill="#000000" width="13px" height="13px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                            {{ number_format($order->sub_total, 2) }}</span>
                    </div>
                </td>
            </tr>

            <tr class="table_body" style="border-bottom: none;">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Discount: </span>
                        <span class="subtotal_amount">
                            <svg fill="#000000" width="13px" height="13px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                            {{ number_format($order->discount, 2) }}</span>
                    </div>
                </td>
            </tr>

            <tr class="table_body" style="border-bottom: none;">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Promo Discount: </span>
                        <span class="subtotal_amount">
                            <svg fill="#000000" width="13px" height="13px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                            {{ number_format($order->coupon_discount, 2) }}</span>
                    </div>
                </td>
            </tr>

            <tr class="table_body" style="border-bottom: none; line-height: 1.2;">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Shipping: </span>
                        <span class="subtotal_amount">
                            <svg fill="#000000" width="13px" height="13px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                            {{ number_format($order->shipping_cost, 2) }} (up to 3 business days)</span>
                    </div>
                </td>
            </tr>

            <tr class="table_body" style="border-bottom: none; line-height: 0.5;">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Payment method: </span>
                        <span class="subtotal_amount">{{ str_replace('_', ' ', $order->payment_type) }}</span>
                    </div>
                </td>
            </tr>

            <tr class="table_body" style="border-bottom: 1px dotted black; line-height: 1.2;">
                <td colspan="5">
                    <div class="subtotal_section">
                        <span class="subtotal_title">Total: </span>
                        <span class="subtotal_amount">
                            <svg fill="#000000" width="13px" height="13px" viewBox="0 0 24 24" id="taka" data-name="Flat Line" xmlns="http://www.w3.org/2000/svg" class="icon flat-line"><path id="primary" d="M6,3H6A3,3,0,0,1,9,6V17.34A3.66,3.66,0,0,0,12.66,21h0A3.66,3.66,0,0,0,16,18.83l1.75-3.94A2.87,2.87,0,0,0,16,11h0" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><line id="primary-2" data-name="primary" x1="6" y1="11" x2="12" y2="11" style="fill: none; stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></line></svg>
                            {{ number_format($order->total_payable, 2) }}</span>
                    </div>
                </td>
            </tr>





            <tr class="table_body">
                <td colspan="5" class="table_billing_address" style="font-size: 18px; font-weight: 600" >Shipping Address</td>
            </tr>
            <tr class="table_body">
                <td colspan="5" class="table_billing_addresss_details">
                    Name: {{ $order->shipping_address['name'] ?? 'N/A' }} <br>
                    Phone No: {{ $order->shipping_address['phone_no'] ?? 'N/A' }} <br>
                    Email: {{ $order->shipping_address['email'] ?? 'N/A' }} <br>
                    Division: {{ $order->shipping_address['division'] ?? 'N/A' }} <br>
                    District: {{ $order->shipping_address['district'] ?? 'N/A' }} <br>
                    Thana: {{ $order->shipping_address['thana'] ?? 'N/A' }} <br>
                    Address: {{ $order->shipping_address['address'] ?? 'N/A' }}
                </td>
            </tr>

            <tr>
                <td colspan="5" class="thankyou_container">
                    <div class="fs-1 " style="margin-bottom: 5px">Thanks for using dolbear.com.bd!</div>
                    <div class="fs-1" style="color: rgba(128, 128, 128, 0.25); ">Think Tech, Think Dolbear</div>

                </td>
            </tr>
        </table>
    </div>
</div>

</body>

</html>