<!-- includes/head.php -->
<!-- ส่วนรวมโครงสร้าง<head> สำหรับนำไปแปะซ้ำตามหน้าเว็บต่างๆ (Reusable HTML <head> metadata snippet) -->

<!-- รองรับภาษาไทย (Define character set to handle UTF-8 symbols) -->
<meta charset="UTF-8">
<!-- ทำให้เว็บไซต์พอดีกับขนาดหน้าจอมือถือ (Responsive design viewport configuration) -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- ดึง Title หรือชื่อเว็บแท็บที่กำหนดมาจากไฟล์ต้นทาง (Dynamically inject page title if set, else use default) -->
<title><?php echo (isset($page_title) ? $page_title : 'Welcome') . ' - MaoHub'; ?></title>

<!-- ดึงสคริปต์ของ TailwindCSS มาช่วยให้สามารถเขียน CSS ด้วย Class ได้เร็ว (Load Tailwind CSS from CDN for utility-first styling) -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
    // ตั้งค่าตกแต่งเพิ่มเติมบน Tailwind (Tailwind configuration script object)
    tailwind.config = {
        theme: {
            // ส่วนที่เสริมเพิ่มเติมเข้าไปนอกจากค่าเริ่มต้นของเทลวินด์ (Extend default theme settings)
            extend: {
                colors: {
                    primary: '#f67280',   
                    secondary: '#c06c84', 
                    dark: '#6c5b7b',      
                    darker: '#35477d',    
                    surface: '#6c5b7b',   
                    error: '#ffb3ba',
                    green: {
                        400: '#b5e7a0',
                        500: '#96ceb4',
                        600: '#78a691',
                    },
                    red: {
                        500: '#ffb3ba',
                        600: '#ff9caa',
                        700: '#ff8599',
                    },
                    blue: {
                        500: '#bae1ff',
                        600: '#a1c9f1',
                    },
                    purple: {
                        500: '#e2caf8',
                        600: '#cfa2ef',
                    },
                    yellow: {
                        400: '#ffdfba',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>

<!-- ดึงสไตล์ของ CSS ภายในโปรเจกต์มาใส่ให้ใช้งานครอบคลุมระบบ (Preprocess raw CSS via tailwind script parsing) -->
<style type="text/tailwindcss">
    <?php include __DIR__ . '/../css/style.css'; ?>
</style>

<!-- โหลดฟ้อนต์ Inter จากฐานข้อมูลฟ้อนต์ Google (Import real Inter font weights from Google Fonts) -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
