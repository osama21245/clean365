<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isSuccess ? $copy['title_success'] : $copy['title_fail'] }}</title>
    <style>
        :root {
            --bg: #f4f7f5;
            --card: #ffffff;
            --ok: #0f8a4b;
            --fail: #c62828;
            --text: #1a1f1c;
            --muted: #5d6b63;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(circle at top {{ $locale === 'ar' ? 'left' : 'right' }}, rgba(15, 138, 75, .12), transparent 40%),
                linear-gradient(180deg, #eef5f0 0%, var(--bg) 100%);
            color: var(--text);
            padding: 24px;
        }
        .card {
            width: min(420px, 100%);
            background: var(--card);
            border-radius: 24px;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 18px 50px rgba(20, 40, 30, .08);
        }
        .icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 42px;
            color: #fff;
            background: {{ $isSuccess ? 'var(--ok)' : 'var(--fail)' }};
        }
        h1 {
            margin: 0 0 8px;
            font-size: 1.45rem;
        }
        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
            font-size: .98rem;
        }
        .meta {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #f3f6f4;
            color: var(--muted);
            font-size: .86rem;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 22px;
            border-radius: 999px;
            text-decoration: none;
            color: #fff;
            background: {{ $isSuccess ? 'var(--ok)' : 'var(--fail)' }};
            font-weight: 600;
        }
        .hint {
            margin-top: 14px;
            font-size: .82rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">{{ $isSuccess ? '✓' : '!' }}</div>
    <h1>{{ $isSuccess ? $copy['title_success'] : $copy['title_fail'] }}</h1>
    <p>{{ $isSuccess ? $copy['body_success'] : $copy['body_fail'] }}</p>

    @if(!empty($bookingReadableId))
        <div class="meta">{{ $copy['booking_label'] }}: {{ $bookingReadableId }}</div>
    @endif

    @if(!empty($redirectUrl))
        <a class="btn" id="continueBtn" href="{{ $redirectUrl }}">
            {{ $isSuccess ? $copy['btn_success'] : $copy['btn_fail'] }}
        </a>
        <div class="hint">{{ $copy['hint_redirect'] }}</div>
    @else
        <a class="btn" href="#" onclick="tryClose(); return false;">{{ $copy['btn_app'] }}</a>
        <div class="hint">{{ $copy['hint_app'] }}</div>
    @endif
</div>

<script>
    (function () {
        var flag = @json($isSuccess ? 'success' : 'fail');
        var payload = {
            flag: flag,
            payment_method: @json($paymentMethod ?? 'edfapay'),
            booking_id: @json($bookingId ?? null),
            readable_id: @json($bookingReadableId ?? null),
            transaction_id: @json($transactionId ?? null),
            lang: @json($locale)
        };

        try {
            if (window.ReactNativeWebView && window.ReactNativeWebView.postMessage) {
                window.ReactNativeWebView.postMessage(JSON.stringify(payload));
            }
        } catch (e) {}

        try {
            if (window.flutter_inappwebview && window.flutter_inappwebview.callHandler) {
                window.flutter_inappwebview.callHandler('paymentResult', payload);
            }
        } catch (e) {}

        try {
            if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.paymentResult) {
                window.webkit.messageHandlers.paymentResult.postMessage(payload);
            }
        } catch (e) {}

        var redirectUrl = @json($redirectUrl);
        if (redirectUrl) {
            setTimeout(function () {
                window.location.replace(redirectUrl);
            }, 1800);
        }
    })();

    function tryClose() {
        try { window.close(); } catch (e) {}
        try {
            if (window.ReactNativeWebView && window.ReactNativeWebView.postMessage) {
                window.ReactNativeWebView.postMessage(JSON.stringify({flag: 'close'}));
            }
        } catch (e) {}
    }
</script>
</body>
</html>
