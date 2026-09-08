<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{translate('invoice')}}</title>
    <script src="{{asset('public/assets/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('public/assets/js/jquery.min.js')}}"></script>
    <style>
        body {
            background-color: #F9FCFF;
            font-size: 10px !important;
            line-height: 1.6;
            font-family: "Inter", sans-serif;
        }

        a {
            color: rgb(65, 83, 179) !important;
            text-decoration: none !important;
        }

        @media print {
            a {
                text-decoration: none !important;
                -webkit-print-color-adjust: exact;
            }
        }

        #invoice {
            padding: 30px;
        }

        .invoice {
            position: relative;
            min-height: 972px;
            max-width: 972px;
            margin-left: auto;
            margin-right: auto;

        }

        .white-box-content {
            background-color: #FFF;
            border: 1px solid #e5e5e5;
            padding: 15px
        }

        .invoice header {
            margin-bottom: 16px;
        }

        .invoice .contacts {
            margin-bottom: 16px
        }

        .invoice .company-details,
        .invoice .invoice-details {
            text-align: right
        }

        .invoice .thanks {
            margin-top: 60px;
            margin-bottom: 30px
        }

        .invoice .footer {
            background-color: rgba(4, 97, 165, 0.05);
        }

        @media print {
            .invoice .notices {
                background-color: #F7F7F7 !important;
                -webkit-print-color-adjust: exact;
            }
        }

        .invoice table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .invoice table td, .invoice table th {
            padding: 15px;
        }

        .invoice table th {
            white-space: nowrap;
            font-weight: 500;
            background-color: rgba(4, 97, 165, 0.05);
        }

        @media print {
            .invoice table th {
                background-color: rgba(4, 97, 165, 0.05) !important;
                -webkit-print-color-adjust: exact;
            }
        }

        .invoice table tfoot td {
            background: 0 0;
            border: none;
            white-space: nowrap;
            text-align: right;
            padding: 8px 14px;
        }

        .invoice table tfoot tr:first-child td {
            padding-top: 16px;
        }

        .fw-700 {
            font-weight: 700;
        }

        .fs-9 {
            font-size: 9px !important;
        }

        .fs-8 {
            font-size: 8px !important;
        }

        .lh-1 {
            line-height: 1;
        }

        .rounded-12 {
            border-radius: 12px;
        }

        .fz-12 {
            font-size: 12px;
        }
        .d-flex {
            display: flex;
        }
        .flex-column {
            flex-direction: column;
        }
.border-bottom {
    border-bottom: 1px solid #e5e5e5
}
.text-right {
    text-align: right;
}
.text-left {
    text-align: left;
}
.text-center {
    text-align:center
}
h1, h2,h3,h4, h5, h6 {
    margin: 0
}
.p-0 {
    padding: 0 !important
}
    </style>
</head>
<body>
<div id="invoice">
    <div class="invoice d-flex flex-column">
        <div>
            <table>
                <tbody>
                        @if ($booking->extra_fee > 0)
                            @php($additional_charge_label_name = business_config('additional_charge_label_name', 'booking_setup')->live_values??'Fee')
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="2">{{$additional_charge_label_name}}</td>
                                <td>+ {{with_currency_symbol($booking->extra_fee)}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3"></td>
                            <td class="fw-700 border-top">{{translate('Total')}}</td>
                            <td class="fw-700 border-top">{{with_currency_symbol($booking->total_booking_amount)}}</td>
                        </tr>

                        @if($booking->payment_method != 'cash_after_service' && $booking->additional_charge < 0)
                            <tr>
                                <td colspan="3"></td>
                                <td class="fw-700">{{translate('Refund')}}</td>
                                <td class="fw-700">{{with_currency_symbol(abs($booking->additional_charge))}}</td>
                            </tr>
                        @endif
                        </tfoot>
                    </table>
                </div>

                <div class="mt-5 text-center mb-4">{{translate('Thanks for using our service')}}.</div>
            </div>
        </div>

        <div style="padding:24px 0">
            <div class="fw-700">{{translate('Terms & Conditions')}}</div>
            <div>{{translate('Change of mind is not applicable as a reason for refund')}}</div>
        </div>

        <table class="footer">
            <tr>
                <td>
                    <div class="text-left">
                        {{Request()->getHttpHost()}}
                    </div>
                </td>
                <td>
                    <div class="text-center">
                        {{$business_phone->live_values}}
                    </div>
                </td>
                <td>
                    <div class="text-right">
                        {{$business_email->live_values}}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
    "use strict";

    function printContent(el) {
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    }

    printContent('invoice');
</script>
</body>
</html>
