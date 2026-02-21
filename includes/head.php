<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title : 'NightOwl Pub'; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#ffd700',   // Gold/Yellow
                    secondary: '#00ff41', // Neon Green
                    dark: '#121212',
                    darker: '#0a0a0a',
                    surface: '#1e1e1e',
                    error: '#ff4444'
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>
<style type="text/tailwindcss">
    <?php include __DIR__ . '/../css/style.css'; ?>
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
