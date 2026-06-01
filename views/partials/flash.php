<?php
// views/partials/flash.php
$flash = getFlash();
if (!$flash) return;

$isSuccess = $flash['type'] === 'success';
$classes   = $isSuccess ? 'bg-jade-light border-jade text-jade-dark' : 'bg-rose-light border-rose text-rose';
$icon      = $isSuccess
    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
    <div class="flex items-start gap-3 border rounded-xl px-4 py-3 <?= $classes ?>"
         role="alert" style="animation: fadeUp .3s ease both">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $icon ?></svg>
        <div class="text-sm font-medium leading-snug"><?= $flash['message'] ?></div>
        <button onclick="this.parentElement.remove()" class="ml-auto opacity-50 hover:opacity-100 transition-opacity flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
