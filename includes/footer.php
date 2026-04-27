<script>
// Simple dropdown toggle for click on mobile
document.querySelectorAll('.dropdown').forEach(d => {
  d.addEventListener('click', e => {
    const menu = d.querySelector('.dropdown-menu');
    if (menu) menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
    e.stopPropagation();
  });
});
document.addEventListener('click', () => {
  document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = '');
});
</script>
</body>
</html>
