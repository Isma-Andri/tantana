<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Tantana') ?> — Tantana</title>

    <!-- Google Fonts : Syne (display) + DM Sans (corps) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    display: ['Syne', 'sans-serif'],
                    body: ['DM Sans', 'sans-serif'],
                },
                colors: {
                    ink:  { DEFAULT: '#0D0D0D', 50: '#F5F5F5', 100: '#E8E8E8', 200: '#D4D4D4', 500: '#737373' },
                    jade: { DEFAULT: '#00A67E', light: '#E6F7F2', dark: '#007A5E' },
                    sun:  { DEFAULT: '#F5A623', light: '#FEF6E8' },
                    rose: { DEFAULT: '#E84B3A', light: '#FDF0EE' },
                },
                boxShadow: {
                    card: '0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04)',
                    lift: '0 8px 32px rgba(0,0,0,.10)',
                },
            }
        }
    }
    </script>

    <style>
        * { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }

        /* Barre de progression animée en haut de page */
        .page-loader {
            position: fixed; top: 0; left: 0; height: 3px; width: 0;
            background: linear-gradient(90deg, #00A67E, #F5A623);
            animation: load .4s ease forwards;
            z-index: 9999;
        }
        @keyframes load { to { width: 100%; } }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Transition page */
        .page-in { animation: fadeUp .35s ease both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }

        /* Badge statut */
        .badge { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .7rem; border-radius:99px; font-size:.75rem; font-weight:600; letter-spacing:.02em; }
        .badge-jade { background:#E6F7F2; color:#007A5E; }
        .badge-sun  { background:#FEF6E8; color:#C07D0E; }
        .badge-rose { background:#FDF0EE; color:#C1352B; }
        .badge-gray { background:#F0F0F0; color:#555; }

        /* Input focus ring personnalisé */
        .t-input {
            width: 100%;
            border: 1.5px solid #E8E8E8;
            border-radius: .6rem;
            padding: .65rem 1rem;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
            background: #FAFAFA;
            color: #0D0D0D;
            outline: none;
        }
        .t-input:focus { border-color: #00A67E; box-shadow: 0 0 0 3px rgba(0,166,126,.12); background:#fff; }

        /* Bouton primaire */
        .btn-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #0D0D0D; color: #fff;
            padding: .65rem 1.4rem; border-radius: .6rem;
            font-weight: 600; font-size: .9rem;
            transition: background .2s, transform .15s, box-shadow .2s;
            cursor: pointer; border: none;
        }
        .btn-primary:hover { background: #2a2a2a; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
        .btn-primary:active { transform: none; }

        /* Bouton jade */
        .btn-jade {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #00A67E; color: #fff;
            padding: .65rem 1.4rem; border-radius: .6rem;
            font-weight: 600; font-size: .9rem;
            transition: background .2s, transform .15s;
            cursor: pointer; border: none;
        }
        .btn-jade:hover { background: #007A5E; transform: translateY(-1px); }

        /* Bouton ghost */
        .btn-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            background: transparent; color: #0D0D0D;
            padding: .6rem 1.2rem; border-radius: .6rem;
            font-weight: 500; font-size: .9rem;
            border: 1.5px solid #E8E8E8;
            transition: border-color .2s, background .2s;
            cursor: pointer;
        }
        .btn-ghost:hover { border-color: #aaa; background: #F5F5F5; }

        /* Bouton danger */
        .btn-danger {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #FDF0EE; color: #C1352B;
            padding: .6rem 1.2rem; border-radius: .6rem;
            font-weight: 600; font-size: .875rem;
            border: 1.5px solid #F9C7C2;
            transition: background .2s;
            cursor: pointer;
        }
        .btn-danger:hover { background: #FDDDD9; }
    </style>
</head>
<body class="bg-ink-50 min-h-full text-ink">
<div class="page-loader"></div>
