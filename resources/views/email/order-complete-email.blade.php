<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>

    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600" rel="stylesheet" type="text/css">
    @php $color = settingHelper('primary_color'); @endphp
    <style>
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 24px;
            color: #8094ae;
            font-weight: 400;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif !important;
        }
    </style>

</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f5f6fa;">
    <div
        style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #ddd; border-radius: 5px; font-family: Helvetica, Arial, sans-serif; font-weight: '300'">
        <div style="background-color: #222; color: #fff; padding: 40px 15px; text-align: center; font-size: 20px;">
            <strong>New Order: {{$content->code}}</strong>
        </div>
        <div style="padding: 20px; color: #333030 ">
            <p style="font-size: 16px; margin: 0 0 10px;">
                Hi {{$content->billing_address['name']}}, <br>
                Just to let you know - we've received your order {{$content->code}}, and it is now being processed:
            </p>
            <p style="margin: 0 0 15px;">
                <a href="#" style="color: #000000; text-decoration: underline; font-weight: bold;">[Order
                    {{$content->code}}]</a>
                <strong>({{$content->updated_at->format('F d, Y')}})</strong>
            </p>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 10px; background-color: #f1f1f1; text-align: left;">
                            Product</th>
                        <th
                            style="border: 1px solid #ddd; padding: 10px; background-color: #f1f1f1; text-align: center;">
                            Quantity</th>
                        <th
                            style="border: 1px solid #ddd; padding: 10px; background-color: #f1f1f1; text-align: right;">
                            Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($content->orderDetails as $orderDetail)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">
                                {{ $orderDetail->product->getTranslation('name', \App::getLocale()) }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                                {{$orderDetail->quantity}}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">
                                ৳ {{ number_format($orderDetail->price, 2) }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <tbody>
                    <tr>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">Subtotal:</th>
                        <td style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">৳
                            {{$content->sub_total}}
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">Shipping:</th>
                        <td style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">
                            <!-- {{ isset($content->shipping_method) && !empty($content->shipping_method) ? $content->shipping_method : 'N/A' }} -->
                            <?php
if (isset($content->billing_address['district']) && !empty($content->billing_address['district']) && strcasecmp($content->billing_address['district'], 'Dhaka') == 0) {
    echo "Inside Dhaka City (Delivery - Up to 3 business days)";
} else {
    echo "Outside Dhaka City (Delivery - Up to 3 business days)";
}
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">Payment method:
                        </th>
                        <td style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">
                            {{ ucwords(str_replace('_', ' ', $content->payment_type)) }}
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 50%;">Total:</th>
                        <td
                            style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd; width: 50%;">
                            ৳ {{$content->total_amount}}</td>
                    </tr>
                </tbody>
            </table>


            <table style="width: 100%;  border-collapse: collapse; border: 1px solid #ddd;">
                <h3 style="margin-top: 20px;">Shipping Address</h3>
                <tbody>
                    <tr>
                        <td style="padding: 10px; text-align: left; border: 1px solid #ddd; width: 100%;">
                            <div>
                                <strong>Name:</strong>
                                {{ isset($content->billing_address['name']) && !empty($content->billing_address['name']) ? $content->billing_address['name'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>Phone:</strong>
                                {{ isset($content->billing_address['phone_no']) && !empty($content->billing_address['phone_no']) ? $content->billing_address['phone_no'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>Email:</strong>
                                {{ isset($content->billing_address['email']) && !empty($content->billing_address['email']) ? $content->billing_address['email'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>Division:</strong>
                                {{ isset($content->billing_address['division']) && !empty($content->billing_address['division']) ? $content->billing_address['division'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>District:</strong>
                                {{ isset($content->billing_address['district']) && !empty($content->billing_address['district']) ? $content->billing_address['district'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>Thana:</strong>
                                {{ isset($content->billing_address['thana']) && !empty($content->billing_address['thana']) ? $content->billing_address['thana'] : 'N/A' }}
                            </div>
                            <div>
                                <strong>Address:</strong>
                                {{ isset($content->billing_address['address']) && !empty($content->billing_address['address']) ? $content->billing_address['address'] : 'N/A' }}
                            </div>
                        </td>

                    </tr>
                </tbody>
            </table>

            <h3 style="text-align: center; margin: 20px; opacity: .5;">Thanks for using Dolbear</h3>
        </div>
    </div>
</body>

</html>