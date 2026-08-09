<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Tantana') ?> — Tantana</title>

    <!-- Theme detection script (prevents theme flashing) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'system-ui', 'sans-serif'],
                },
                colors: {
                    border: 'hsl(var(--border))',
                    input: 'hsl(var(--input))',
                    ring: 'hsl(var(--ring))',
                    background: 'hsl(var(--background))',
                    foreground: 'hsl(var(--foreground))',
                    primary: {
                        DEFAULT: 'hsl(var(--primary))',
                        foreground: 'hsl(var(--primary-foreground))',
                    },
                    secondary: {
                        DEFAULT: 'hsl(var(--secondary))',
                        foreground: 'hsl(var(--secondary-foreground))',
                    },
                    destructive: {
                        DEFAULT: 'hsl(var(--destructive))',
                        foreground: 'hsl(var(--destructive-foreground))',
                    },
                    muted: {
                        DEFAULT: 'hsl(var(--muted))',
                        foreground: 'hsl(var(--muted-foreground))',
                    },
                    accent: {
                        DEFAULT: 'hsl(var(--accent))',
                        foreground: 'hsl(var(--accent-foreground))',
                    },
                    popover: {
                        DEFAULT: 'hsl(var(--popover))',
                        foreground: 'hsl(var(--popover-foreground))',
                    },
                    card: {
                        DEFAULT: 'hsl(var(--card))',
                        foreground: 'hsl(var(--card-foreground))',
                    },
                    // Preserve and adapt semantic color mappings
                    jade: {
                        DEFAULT: 'hsl(var(--jade))',
                        foreground: 'hsl(var(--jade-foreground))',
                    },
                    sun: {
                        DEFAULT: 'hsl(var(--sun))',
                        foreground: 'hsl(var(--sun-foreground))',
                    },
                    rose: {
                        DEFAULT: 'hsl(var(--rose))',
                        foreground: 'hsl(var(--rose-foreground))',
                    }
                },
                borderRadius: {
                    lg: 'var(--radius)',
                    md: 'calc(var(--radius) - 2px)',
                    sm: 'calc(var(--radius) - 4px)'
                },
                boxShadow: {
                    card: '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                    lift: '0 4px 12px 0 rgba(0, 0, 0, 0.05), 0 2px 4px 0 rgba(0, 0, 0, 0.03)',
                }
            }
        }
    }
    </script>

    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 240 10% 3.9%;
            --card: 0 0% 100%;
            --card-foreground: 240 10% 3.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 240 10% 3.9%;
            --primary: 240 5.9% 10%;
            --primary-foreground: 0 0% 98%;
            --secondary: 240 4.8% 95.9%;
            --secondary-foreground: 240 5.9% 10%;
            --muted: 240 4.8% 95.9%;
            --muted-foreground: 240 3.8% 46.1%;
            --accent: 240 4.8% 95.9%;
            --accent-foreground: 240 5.9% 10%;
            --destructive: 346.8 77.2% 49.8%;
            --destructive-foreground: 355.7 100% 97.3%;
            --border: 240 5.9% 90%;
            --input: 240 5.9% 90%;
            --ring: 240 5.9% 10%;
            --radius: 0.5rem;

            /* Shadcn-style custom semantic colors */
            --jade: 142.1 76.2% 36.3%;
            --jade-foreground: 355.7 100% 97.3%;
            --sun: 35.2 91.7% 32.9%;
            --sun-foreground: 35.2 100% 98%;
            --rose: 346.8 77.2% 49.8%;
            --rose-foreground: 355.7 100% 97.3%;
        }

        .dark {
            --background: 240 10% 3.9%;
            --foreground: 0 0% 98%;
            --card: 240 10% 3.9%;
            --card-foreground: 0 0% 98%;
            --popover: 240 10% 3.9%;
            --popover-foreground: 0 0% 98%;
            --primary: 0 0% 98%;
            --primary-foreground: 240 5.9% 10%;
            --secondary: 240 3.7% 15.9%;
            --secondary-foreground: 0 0% 98%;
            --muted: 240 3.7% 15.9%;
            --muted-foreground: 240 5% 64.9%;
            --accent: 240 3.7% 15.9%;
            --accent-foreground: 0 0% 98%;
            --destructive: 346.8 77.2% 49.8%;
            --destructive-foreground: 355.7 100% 97.3%;
            --border: 240 3.7% 15.9%;
            --input: 240 3.7% 15.9%;
            --ring: 240 4.9% 83.9%;

            /* Semantic dark colors */
            --jade: 142.1 70.6% 45.3%;
            --jade-foreground: 144.9 80.4% 10%;
            --sun: 37.9 77.2% 41.8%;
            --sun-foreground: 35.2 100% 10%;
            --rose: 346.8 72% 55%;
            --rose-foreground: 355.7 100% 97.3%;
        }

        * { font-family: 'Inter', system-ui, sans-serif; }

        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .page-loader {
            position: fixed; top: 0; left: 0; height: 2px; width: 0;
            background: hsl(var(--primary));
            animation: load .3s ease forwards;
            z-index: 9999;
        }
        @keyframes load { to { width: 100%; } }

        .page-in { animation: fadeUp .25s cubic-bezier(0.16, 1, 0.3, 1) both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

        .hover-lift { transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); }

        .badge {
            display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .75rem;
            border-radius: 9999px; font-size: .75rem; font-weight: 600;
            transition: all .2s; border: 1px solid transparent;
        }
        .badge-jade {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.2);
        }
        .badge-sun {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.2);
        }
        .badge-rose {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }
        .badge-gray {
            background-color: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
            border-color: hsl(var(--border));
        }

        .t-input {
            width: 100%; border: 1px solid hsl(var(--border)); border-radius: var(--radius);
            padding: .5rem .75rem; font-size: .875rem;
            transition: border-color .15s, box-shadow .15s;
            background: hsl(var(--background)); color: hsl(var(--foreground)); outline: none;
        }
        .t-input:focus {
            border-color: hsl(var(--ring));
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            background: hsl(var(--primary)); color: hsl(var(--primary-foreground));
            padding: .5rem 1rem; border-radius: var(--radius);
            font-weight: 500; font-size: .875rem;
            transition: opacity .15s, transform .1s;
            cursor: pointer; border: none; text-decoration: none;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-primary:active { transform: scale(0.98); }

        .btn-jade {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            background: hsl(var(--jade)); color: hsl(var(--jade-foreground));
            padding: .5rem 1rem; border-radius: var(--radius);
            font-weight: 500; font-size: .875rem;
            transition: opacity .15s, transform .1s;
            cursor: pointer; border: none; text-decoration: none;
        }
        .btn-jade:hover { opacity: 0.9; }
        .btn-jade:active { transform: scale(0.98); }

        .btn-ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            background: transparent; color: hsl(var(--foreground));
            padding: .5rem 1rem; border-radius: var(--radius);
            font-weight: 500; font-size: .875rem;
            border: 1px solid hsl(var(--border));
            transition: background-color .15s, color .15s, transform .1s;
            cursor: pointer; text-decoration: none;
        }
        .btn-ghost:hover { background: hsl(var(--accent)); color: hsl(var(--accent-foreground)); }
        .btn-ghost:active { transform: scale(0.98); }

        .btn-danger {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            background: hsl(var(--destructive)); color: hsl(var(--destructive-foreground));
            padding: .5rem 1rem; border-radius: var(--radius);
            font-weight: 500; font-size: .875rem;
            transition: opacity .15s, transform .1s;
            cursor: pointer; text-decoration: none; border: none;
        }
        .btn-danger:hover { opacity: 0.9; }
        .btn-danger:active { transform: scale(0.98); }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: hsl(var(--background));
        }
        ::-webkit-scrollbar-thumb {
            background: hsl(var(--muted-foreground));
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: hsl(var(--foreground));
        }

        /* Legacy government theme class mappings to Shadcn UI tokens */
        .bg-white {
            background-color: hsl(var(--card)) !important;
            color: hsl(var(--card-foreground)) !important;
        }
        .bg-slate-50, .bg-slate-100, .bg-ink-50, .bg-slate-100\/50, .bg-slate-50\/80 {
            background-color: hsl(var(--secondary)) !important;
            color: hsl(var(--secondary-foreground)) !important;
        }
        .text-slate-900, .text-ink, .text-slate-800, .text-slate-700, .text-slate-600 {
            color: hsl(var(--foreground)) !important;
        }
        .text-slate-500, .text-ink-500, .text-slate-400, .text-ink-400 {
            color: hsl(var(--muted-foreground)) !important;
        }
        .border-slate-100, .border-slate-200, .border-slate-200\/80, .border-slate-200\/60, .border-ink-100, .border-slate-300 {
            border-color: hsl(var(--border)) !important;
        }
        .font-display {
            font-family: 'Inter', system-ui, sans-serif !important;
        }
        .shadow-card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            border: 1px solid hsl(var(--border)) !important;
        }
        .dark .shadow-card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2) !important;
        }
        .bg-emerald-50, .bg-jade-light, .bg-emerald-50\/60 {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            border-color: rgba(16, 185, 129, 0.2) !important;
        }
        .bg-sun-light {
            background-color: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
            border-color: rgba(245, 158, 11, 0.2) !important;
        }
        .bg-rose-light {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border-color: rgba(239, 68, 68, 0.2) !important;
        }
        dialog {
            background: hsl(var(--card)) !important;
            color: hsl(var(--card-foreground)) !important;
            border: 1px solid hsl(var(--border)) !important;
            border-radius: var(--radius) !important;
        }
        dialog::backdrop {
            background: rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(4px) !important;
        }
    </style>
</head>
<body class="bg-background min-h-full text-foreground antialiased">
<div class="page-loader"></div>
