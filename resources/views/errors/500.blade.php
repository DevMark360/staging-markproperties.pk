{{-- Self-contained on purpose: no site layout, components or built assets, so it still renders when the app itself is failing --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Something went wrong | Mark Properties</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#fff;color:#141414}
        main{width:100%;max-width:40rem;margin:0 auto;padding:3rem 1.5rem}
        img{height:44px;width:auto}p.code{margin:3rem 0 0;font-size:.875rem;font-weight:600;color:#6b6b6b}
        h1{margin:.75rem 0 0;font-size:clamp(2rem,6vw,2.75rem);line-height:1.15;letter-spacing:-.02em}
        p.lead{margin:1.25rem 0 0;font-size:1.125rem;line-height:1.6;color:#3d3d3d}
        .actions{margin-top:2.25rem;display:flex;flex-wrap:wrap;gap:.75rem}
        a.btn{display:inline-block;border-radius:.5rem;padding:.875rem 1.5rem;font-weight:600;text-decoration:none;border:1px solid #e5e5e5;color:#141414}
        a.btn.primary{background:#dc1a21;border-color:#dc1a21;color:#fff}
    </style>
</head>
<body>
    <main>
        <a href="/"><img src="/images/logo-dark.png" alt="Mark Properties"></a>
        <p class="code">Error {{ $exception?->getStatusCode() ?? 500 }}</p>
        <h1>Something went wrong on our side</h1>
        <p class="lead">The page could not load just now. Please try again in a moment.</p>
        <div class="actions">
            <a class="btn primary" href="/">Back to the home page</a>
            <a class="btn" href="/contact">Contact us</a>
        </div>
    </main>
</body>
</html>
