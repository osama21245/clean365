<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>clean365 — Development Environment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #90F84D;
            --green-dark: #0C7D40;
            --green-soft: #E6F6EC;
            --blue: #2D6CDF;
            --ink: #0F2117;
            --muted: #63756B;
            --card: #FFFFFF;
            --ring: rgba(23, 167, 92, .18);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--ink);
            background: radial-gradient(1200px 600px at 15% -10%, #EAF8F0 0%, transparent 55%),
                        radial-gradient(1000px 700px at 110% 20%, #EAF1FD 0%, transparent 50%),
                        #F6FBF8;
            overflow: hidden;
        }

        /* floating soft shapes */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(8px);
            opacity: .5;
            z-index: 0;
            animation: float 14s ease-in-out infinite;
        }
        .blob.b1 { width: 220px; height: 220px; top: -60px; left: -40px; background: radial-gradient(circle at 30% 30%, #BEEBD1, transparent 70%); }
        .blob.b2 { width: 300px; height: 300px; bottom: -120px; right: -80px; background: radial-gradient(circle at 30% 30%, #C7DBFB, transparent 70%); animation-delay: -5s; }
        @keyframes float { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-24px) } }

        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            background: var(--card);
            border: 1px solid #EAF0EC;
            border-radius: 28px;
            padding: 48px 44px;
            text-align: center;
            box-shadow: 0 30px 80px -30px rgba(12, 125, 64, .28), 0 8px 24px -12px rgba(15, 33, 23, .12);
            animation: rise .6s cubic-bezier(.2, .8, .2, 1) both;
        }
        @keyframes rise { from { opacity: 0; transform: translateY(16px) } to { opacity: 1; transform: translateY(0) } }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 30px;
            letter-spacing: -.5px;
        }
        .logo .mark {
            width: 40px; height: 40px;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            border-radius: 12px;
            box-shadow: 0 8px 20px -6px var(--ring);
        }
        .logo .mark svg { width: 22px; height: 22px; }
        .logo .word { color: var(--ink); }
        .logo .word b { color: var(--green); font-weight: 800; }

        .badge {
            margin: 26px auto 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 999px;
            background: var(--green-soft);
            color: var(--green-dark);
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .badge .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 4px var(--ring); animation: pulse 1.8s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1 } 50% { opacity: .4 } }

        h1 {
            margin-top: 22px;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        p.lead {
            margin-top: 14px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        p.lead-ar {
            margin-top: 10px;
            color: var(--muted);
            font-size: 15.5px;
            line-height: 1.9;
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
        }

        .divider { height: 1px; background: linear-gradient(90deg, transparent, #E7EEEA, transparent); margin: 30px 0 22px; }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 12px;
            background: #F4F8F6;
            border: 1px solid #EAF0EC;
            font-size: 13px;
            font-weight: 600;
            color: #35473D;
        }
        .chip.blue { color: var(--blue); background: #EEF3FD; border-color: #E1EAFB; }
        .chip svg { width: 15px; height: 15px; }

        footer {
            margin-top: 26px;
            font-size: 12.5px;
            color: #9AA8A0;
        }

        @media (max-width: 480px) {
            .card { padding: 36px 24px; border-radius: 22px; }
            h1 { font-size: 23px; }
            .logo { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="blob b1"></div>
    <div class="blob b2"></div>

    <main class="card">
        <div class="logo">
            <span class="mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6.5c-6.6-.8-11 2.6-12.4 8.9C6.9 13 8.8 11.4 12 11c-2.3 1-3.6 2.7-4.2 5.4-.2.9-.3 1.9-.3 3.1H5.4c0-6.9 3.6-12.4 14.6-13Z" fill="#fff"/>
                </svg>
            </span>
            <span class="word">clean<b>365</b></span>
        </div>

        <div class="badge"><span class="dot"></span> Development &middot; Testing</div>

        <h1>This is a temporary domain</h1>

        <p class="lead">
            This domain belongs to <strong>clean365</strong> and is used only for
            development and testing. It is not the company's public website.
        </p>

        <p class="lead-ar">
            هذا نطاق مؤقت خاص بشركة <strong>clean365</strong> ويُستخدم لأغراض
            التطوير والاختبار فقط، وليس الموقع الرسمي للشركة.
        </p>

        <div class="divider"></div>

        <div class="meta">
            <span class="chip">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2 4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5l-8-3Z" stroke="#17A75C" stroke-width="1.8" stroke-linejoin="round"/></svg>
                Internal environment
            </span>
            <span class="chip blue">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 8 3 12l4 4M17 8l4 4-4 4M14 4l-4 16" stroke="#2D6CDF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                clean365 platform
            </span>
        </div>

        <footer>&copy; <?php echo e(date('Y')); ?> clean365 — for authorized use only</footer>
    </main>
</body>
</html>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/resources/views/temp-domain.blade.php ENDPATH**/ ?>