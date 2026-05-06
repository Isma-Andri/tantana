<script>
// Dropdown toggle
document.querySelectorAll('.dropdown').forEach(d => {
  d.addEventListener('click', e => {
    const menu = d.querySelector('.dropdown-menu');
    if (menu) {
      const isOpen = menu.style.display === 'flex';
      document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = '');
      if (!isOpen) menu.style.display = 'flex';
    }
    e.stopPropagation();
  });
});
document.addEventListener('click', () => {
  document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = '');
});
</script>
</body>
</html>
