<!-- includes/head.php -->
<!-- ส่วนรวมโครงสร้าง<head> สำหรับนำไปแปะซ้ำตามหน้าเว็บต่างๆ (Reusable HTML <head> metadata snippet) -->

<!-- รองรับภาษาไทย (Define character set to handle UTF-8 symbols) -->
<meta charset="UTF-8">
<!-- ทำให้เว็บไซต์พอดีกับขนาดหน้าจอมือถือ (Responsive design viewport configuration) -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- ดึง Title หรือชื่อเว็บแท็บที่กำหนดมาจากไฟล์ต้นทาง (Dynamically inject page title if set, else use default) -->
<title><?php echo isset($page_title) ? $page_title : 'NightOwl Pub'; ?></title>

<!-- ดึงสคริปต์ของ TailwindCSS มาช่วยให้สามารถเขียน CSS ด้วย Class ได้เร็ว (Load Tailwind CSS from CDN for utility-first styling) -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
    // ตั้งค่าตกแต่งเพิ่มเติมบน Tailwind (Tailwind configuration script object)
    tailwind.config = {
        theme: {
            // ส่วนที่เสริมเพิ่มเติมเข้าไปนอกจากค่าเริ่มต้นของเทลวินด์ (Extend default theme settings)
            extend: {
                colors: {
                    primary: '#ffd700',   // สีหลัก (Gold/Yellow: Primary theme color)
                    secondary: '#00ff41', // สีรองสำหรับเน้นปุ่ม (Neon Green: Secondary accent color)
                    dark: '#121212',      // โหมดสว่างมืด (Dark: Dark mode shades)
                    darker: '#0a0a0a',    // โทนสีมืดและดำสนิท (Darker: Deeper black variants)
                    surface: '#1e1e1e',   // สีพื้นหลังกล่องโต้ตอบ (Surface: Module background shade)
                    error: '#ff4444'      // สีเตือนเมื่อมีปัญหา (Error: Red alert color)
                },
                fontFamily: {
                    // กำหนดให้ทุกองค์ประกอบใช้ฟ้อนต์ Inter (Assign 'Inter' as the base sans font)
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
