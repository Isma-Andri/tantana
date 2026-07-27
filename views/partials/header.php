<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Tantana') ?> — Tantana</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,800;1,8..60,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    display: ['Source Serif 4', 'Georgia', 'serif'],
                    body:    ['Inter', 'system-ui', 'sans-serif'],
                },
                colors: {
                    ink:  { DEFAULT: '#0f172a', 50: '#fcfbf9', 100: '#e2e8f0', 200: '#cbd5e1', 500: '#64748b' },
                    jade: { DEFAULT: '#064e3b', light: '#ecfdf5', dark: '#047857' },
                    sun:  { DEFAULT: '#d97706', light: '#fffbeb' },
                    rose: { DEFAULT: '#dc2626', light: '#fef2f2' },
                },
                boxShadow: {
                    card: '0 1px 3px rgba(15,23,42,.04), 0 4px 16px rgba(15,23,42,.03)',
                    lift: '0 8px 32px rgba(15,23,42,.08)',
                },
            }
        }
    }
    </script>

    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Source Serif 4', Georgia, serif; }

        body {
            background-color: #fcfbf9;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(6,78,59,0.04) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .page-loader {
            position: fixed; top: 0; left: 0; height: 3px; width: 0;
            background: #064e3b;
            animation: load .4s ease forwards;
            z-index: 9999;
        }
        @keyframes load { to { width: 100%; } }

        .page-in { animation: fadeUp .35s cubic-bezier(0.16, 1, 0.3, 1) both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }

        .float-slow { animation: floatSlow 6s ease-in-out infinite; }
        @keyframes floatSlow { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }

        .pulse-subtle { animation: pulseSubtle 3s ease-in-out infinite; }
        @keyframes pulseSubtle { 0%, 100% { opacity: 1; } 50% { opacity: 0.75; } }

        .hover-lift { transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08); }

        .badge { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .75rem; border-radius:99px; font-size:.75rem; font-weight:600; letter-spacing:.02em; transition: all .2s; }
        .badge-jade { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .badge-sun  { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
        .badge-rose { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .badge-gray { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

        .t-input {
            width: 100%; border: 1.5px solid #e2e8f0; border-radius: .5rem;
            padding: .65rem 1rem; font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
            background: #ffffff; color: #0f172a; outline: none;
        }
        .t-input:focus { border-color: #064e3b; box-shadow: 0 0 0 3px rgba(6,78,59,.12); background:#fff; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #064e3b; color: #fff;
            padding: .65rem 1.4rem; border-radius: .5rem;
            font-weight: 600; font-size: .9rem;
            transition: background .2s, transform .15s, box-shadow .2s;
            cursor: pointer; border: none; text-decoration: none;
        }
        .btn-primary:hover { background: #047857; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(6,78,59,.2); }
        .btn-primary:active { transform: scale(0.97); }

        .btn-jade {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #064e3b; color: #fff;
            padding: .65rem 1.4rem; border-radius: .5rem;
            font-weight: 600; font-size: .9rem;
            transition: background .2s, transform .15s;
            cursor: pointer; border: none; text-decoration: none;
        }
        .btn-jade:hover { background: #047857; transform: translateY(-1px); }
        .btn-jade:active { transform: scale(0.97); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            background: transparent; color: #334155;
            padding: .6rem 1.2rem; border-radius: .5rem;
            font-weight: 500; font-size: .9rem;
            border: 1.5px solid #cbd5e1;
            transition: border-color .2s, background .2s, transform .15s;
            cursor: pointer; text-decoration: none;
        }
        .btn-ghost:hover { border-color: #94a3b8; background: #f1f5f9; }
        .btn-ghost:active { transform: scale(0.97); }

        .btn-danger {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #fef2f2; color: #b91c1c;
            padding: .6rem 1.2rem; border-radius: .5rem;
            font-weight: 600; font-size: .875rem;
            border: 1.5px solid #fecaca;
            transition: background .2s, transform .15s;
            cursor: pointer; text-decoration: none;
        }
        .btn-danger:hover { background: #fee2e2; }
        .btn-danger:active { transform: scale(0.97); }
    </style>
</head>
<body class="bg-[#fcfbf9] min-h-full text-slate-900">
<div class="page-loader"></div>
